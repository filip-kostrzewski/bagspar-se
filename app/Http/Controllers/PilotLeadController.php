<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePilotLeadRequest;
use App\Mail\PilotLeadConfirmation;
use App\Mail\PilotLeadNotification;
use App\Models\PilotLead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class PilotLeadController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(StorePilotLeadRequest $request): RedirectResponse
    {
        $company = trim($request->string('company')->toString());
        $email = Str::lower($request->string('email')->trim()->toString());

        $lead = DB::transaction(function () use ($company, $email): ?PilotLead {
            $existing = PilotLead::query()
                ->whereRaw('lower(email) = ?', [$email])
                ->lockForUpdate()
                ->first();

            if ($existing instanceof PilotLead) {
                return null;
            }

            return PilotLead::create([
                'company' => $company,
                'email' => $email,
                // Existing databases require this column. It is the time the form was sent.
                'consented_at' => now(),
            ]);
        });

        if (! $lead instanceof PilotLead) {
            return redirect()
                ->to(route('home').'#form-result')
                ->with('pilot_lead_duplicate', true);
        }

        $recipient = config('bagspar.lead_notification_email');

        if (is_string($recipient) && $recipient !== '') {
            $this->sendMail($recipient, new PilotLeadNotification($lead), 'Bågspår lead notification could not be sent.');
        }

        $this->sendMail($lead->email, new PilotLeadConfirmation($lead), 'Bågspår signup confirmation could not be sent.');

        return redirect()->to(route('home').'#form-result')->with('pilot_lead_received', true);
    }

    private function sendMail(string $recipient, Mailable $mail, string $logMessage): void
    {
        try {
            Mail::to($recipient)->send($mail);
        } catch (Throwable $exception) {
            Log::warning($logMessage, [
                'exception' => $exception::class,
            ]);
        }
    }
}
