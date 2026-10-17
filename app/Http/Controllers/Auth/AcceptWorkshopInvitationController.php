<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WorkshopInvitation;
use Closure;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AcceptWorkshopInvitationController extends Controller
{
    public function create(string $token): View
    {
        $invitation = WorkshopInvitation::findPendingByToken($token);
        abort_unless($invitation instanceof WorkshopInvitation, 404);
        $invitation->load('workshop');

        return view('auth.accept-invitation', [
            'invitation' => $invitation,
            'token' => $token,
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $invitation = WorkshopInvitation::findPendingByToken($token);
        abort_unless($invitation instanceof WorkshopInvitation, 404);

        if ($request->user() instanceof User) {
            return back()->withErrors([
                'invitation' => 'Logga ut innan du accepterar inbjudan.',
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'password' => [
                'bail',
                'required',
                'string',
                'confirmed',
                Password::min(12),
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (is_string($value) && strlen($value) > 72) {
                        $fail('Lösenordet får vara högst 72 byte.');
                    }
                },
            ],
            'terms' => ['accepted'],
        ], [
            'name.required' => 'Ange ditt namn.',
            'name.string' => 'Ange ditt namn som text.',
            'name.max' => 'Namnet får vara högst 150 tecken.',
            'password.required' => 'Ange ett lösenord.',
            'password.string' => 'Ange ett giltigt lösenord.',
            'password.confirmed' => 'Lösenorden stämmer inte överens.',
            'password.min' => 'Lösenordet måste innehålla minst 12 tecken.',
            'terms.accepted' => 'Godkänn villkoren för att acceptera inbjudan.',
        ]);

        if (User::query()->where('email', $invitation->email)->exists()) {
            return back()->withErrors([
                'invitation' => 'E-postadressen är redan registrerad.',
            ]);
        }

        try {
            $user = $this->acceptInvitation($invitation, $validated);
        } catch (UniqueConstraintViolationException) {
            return back()->withErrors([
                'invitation' => 'E-postadressen är redan registrerad.',
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('app.dashboard')->with('status', 'Du är nu medarbetare i verkstaden.');
    }

    /**
     * @param  array{name: string, password: string}  $validated
     */
    private function acceptInvitation(WorkshopInvitation $invitation, array $validated): User
    {
        return DB::transaction(function () use ($invitation, $validated): User {
            $locked = WorkshopInvitation::query()->lockForUpdate()->findOrFail($invitation->getKey());
            abort_unless($locked->accepted_at === null && $locked->expires_at?->isFuture(), 404);

            $user = $locked->workshop()->firstOrFail()->users()->create([
                'name' => $validated['name'],
                'email' => $locked->email,
                'password' => $validated['password'],
                'role' => User::ROLE_MEMBER,
                'terms_accepted_at' => now(),
            ]);
            $user->markEmailAsVerified();
            $locked->update(['accepted_at' => now()]);

            return $user;
        });
    }
}
