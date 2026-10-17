<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Mail\SubscriptionOrderConfirmation;
use App\Mail\SubscriptionOrderNotification;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class BillingController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);
        $workshop = $this->currentWorkshop($request);

        return view('app.billing.edit', [
            'workshop' => $workshop,
            'canManageBilling' => Gate::allows('manageBilling', $workshop),
            'owner' => $workshop->users()->where('role', User::ROLE_OWNER)->orderBy('id')->first(),
            'seatCount' => $workshop->seatCount(),
            'pendingInvitations' => $workshop->invitations()->pending()->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $workshop = $this->currentWorkshop($request);
        Gate::authorize('manageBilling', $workshop);

        $billingEmail = $request->input('billing_email');
        if (is_string($billingEmail)) {
            $request->merge(['billing_email' => Str::lower(trim($billingEmail))]);
        }

        $validated = $request->validate([
            'plan' => ['required', Rule::in([Workshop::PLAN_MONTHLY, Workshop::PLAN_YEARLY])],
            'organization_number' => ['required', 'string', 'regex:/^\d{6}-\d{4}$/'],
            'billing_email' => ['required', 'string', 'email:rfc', 'max:254'],
            'billing_reference' => ['nullable', 'string', 'max:80'],
            'terms' => ['accepted'],
        ], [
            'plan.required' => 'Välj månads- eller årsabonnemang.',
            'plan.in' => 'Välj månads- eller årsabonnemang.',
            'organization_number.required' => 'Ange verkstadens organisationsnummer så att fakturan blir rätt.',
            'organization_number.regex' => 'Ange organisationsnumret med sex siffror, bindestreck och fyra siffror.',
            'billing_email.required' => 'Ange en e-postadress som fakturan ska skickas till.',
            'billing_email.email' => 'Ange en giltig e-postadress för fakturan.',
            'billing_email.max' => 'E-postadressen får vara högst 254 tecken.',
            'billing_reference.max' => 'Referensen får vara högst 80 tecken.',
            'terms.accepted' => 'Godkänn villkoren för att beställa.',
        ]);

        $reference = $validated['billing_reference'] ?? null;
        if (is_string($reference)) {
            $reference = trim($reference);
            $reference = $reference === '' ? null : $reference;
        }

        $isFirstOrder = ! $workshop->hasOrderedSubscription();

        $workshop->fill([
            'subscription_plan' => $validated['plan'],
            'organization_number' => $validated['organization_number'],
            'billing_email' => $validated['billing_email'],
            'billing_reference' => $reference,
        ]);

        if ($isFirstOrder) {
            $workshop->subscription_ordered_at = now();
        }

        $workshop->save();

        $actor = $request->user();
        $this->notify($workshop, $actor instanceof User ? $actor : null, $isFirstOrder);

        return redirect()->route('app.billing.edit')->with('status', $isFirstOrder
            ? 'Tack för beställningen. Fakturan kommer till '.$workshop->billing_email.'.'
            : 'Abonnemanget är uppdaterat.');
    }

    private function notify(Workshop $workshop, ?User $actor, bool $isFirstOrder): void
    {
        $recipient = config('bagspar.lead_notification_email');

        if (is_string($recipient) && trim($recipient) !== '') {
            try {
                Mail::to(trim($recipient))->send(new SubscriptionOrderNotification($workshop, $actor, $isFirstOrder));
            } catch (Throwable $exception) {
                Log::warning('Subscription order notification could not be sent.', [
                    'workshop_id' => $workshop->getKey(),
                    'exception' => $exception::class,
                ]);
            }
        }

        if (! is_string($workshop->billing_email)) {
            return;
        }

        try {
            Mail::to($workshop->billing_email)->send(new SubscriptionOrderConfirmation($workshop, $isFirstOrder));
        } catch (Throwable $exception) {
            Log::warning('Subscription order confirmation could not be sent.', [
                'workshop_id' => $workshop->getKey(),
                'exception' => $exception::class,
            ]);
        }
    }
}
