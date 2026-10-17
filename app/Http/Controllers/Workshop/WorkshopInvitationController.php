<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Mail\OutboundMail;
use App\Mail\WorkshopInvitationMail;
use App\Models\User;
use App\Models\WorkshopInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class WorkshopInvitationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $workshop = $this->currentWorkshop($request);
        Gate::authorize('invite', $workshop);

        $email = $request->input('email');
        if (is_string($email)) {
            $request->merge(['email' => Str::lower(trim($email))]);
        }

        $validated = $request->validate([
            'email' => ['required', 'string', 'email:rfc', 'max:254'],
        ], [
            'email.required' => 'Ange en e-postadress.',
            'email.string' => 'Ange en giltig e-postadress.',
            'email.email' => 'Ange en giltig e-postadress.',
            'email.max' => 'E-postadressen får vara högst 254 tecken.',
        ]);

        $email = $validated['email'];
        $actor = $request->user();

        if (User::query()->where('email', $email)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'E-postadressen är redan registrerad.']);
        }

        $token = WorkshopInvitation::freshToken();
        $invitation = DB::transaction(function () use ($workshop, $actor, $email, $token): WorkshopInvitation {
            $invitation = WorkshopInvitation::query()
                ->where('workshop_id', $workshop->getKey())
                ->where('email', $email)
                ->whereNull('accepted_at')
                ->lockForUpdate()
                ->first();

            $attributes = [
                'invited_by_user_id' => $actor instanceof User ? $actor->getKey() : null,
                'token_hash' => WorkshopInvitation::hashToken($token),
                'expires_at' => now()->addDays(7),
            ];

            if ($invitation instanceof WorkshopInvitation) {
                $invitation->update($attributes);

                return $invitation;
            }

            return $workshop->invitations()->create($attributes + ['email' => $email]);
        });

        $invitation->setRelation('workshop', $workshop);
        $sent = $this->sendInvitation($invitation, $token, $actor instanceof User ? $actor->name : $workshop->name);

        return back()->with('status', $sent
            ? 'Inbjudan har skickats.'
            : 'Inbjudan sparades, men mejlet kunde inte skickas.');
    }

    public function destroy(Request $request, WorkshopInvitation $invitation): RedirectResponse
    {
        $workshop = $this->currentWorkshop($request);
        Gate::authorize('invite', $workshop);
        abort_unless($invitation->workshop_id === $workshop->getKey() && $invitation->accepted_at === null, 404);

        $invitation->delete();

        return back()->with('status', 'Inbjudan har tagits bort.');
    }

    public function removeMember(Request $request, User $member): RedirectResponse
    {
        $workshop = $this->currentWorkshop($request);
        Gate::authorize('invite', $workshop);
        abort_unless($member->workshop_id === $workshop->getKey(), 404);
        abort_unless($member->isMember(), 403);

        DB::table('sessions')->where('user_id', $member->getKey())->delete();
        $member->delete();

        return back()->with('status', 'Medarbetarens åtkomst har tagits bort.');
    }

    private function sendInvitation(WorkshopInvitation $invitation, string $token, string $inviterName): bool
    {
        if (! OutboundMail::isConfigured()) {
            return false;
        }

        try {
            Mail::to($invitation->email)->send(new WorkshopInvitationMail(
                $invitation,
                route('invitations.accept', ['token' => $token]),
                $inviterName,
            ));

            return true;
        } catch (Throwable $exception) {
            Log::warning('Workshop invitation could not be sent.', [
                'invitation_id' => $invitation->getKey(),
                'exception' => $exception::class,
            ]);

            return false;
        }
    }
}
