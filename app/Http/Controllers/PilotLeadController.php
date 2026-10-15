<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePilotLeadRequest;
use App\Mail\PilotLeadNotification;
use App\Models\PilotLead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class PilotLeadController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(StorePilotLeadRequest $request): RedirectResponse
    {
        $lead = PilotLead::create(
            $request->safe()->only(['company', 'email']),
        );

        $recipient = config('bagspar.lead_notification_email');

        if (is_string($recipient) && $recipient !== '') {
            try {
                Mail::to($recipient)->send(new PilotLeadNotification($lead));
            } catch (Throwable $exception) {
                Log::warning('Bågspår lead notification could not be sent.', [
                    'exception' => $exception::class,
                ]);
            }
        }

        return redirect()->to(route('home').'#form-result')->with('pilot_lead_received', true);
    }
}
