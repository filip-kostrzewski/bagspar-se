<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Mail\ContactMessageConfirmation;
use App\Mail\ContactMessageNotification;
use App\Mail\OutboundMail;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class ContactMessageController extends Controller
{
    public function create(): View
    {
        return view('contact');
    }

    public function store(StoreContactMessageRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $contactMessage = ContactMessage::query()->create([
            'name' => $validated['contact_name'],
            'company' => $validated['contact_company'] ?? null,
            'email' => $validated['contact_email'],
            'phone' => $validated['contact_phone'] ?? null,
            'message' => $validated['contact_message'],
        ]);

        $recipient = config('bagspar.contact_email');

        if (is_string($recipient) && $recipient !== '') {
            $this->sendMail(
                $recipient,
                new ContactMessageNotification($contactMessage),
                'Bågspår contact message could not be sent.',
            );
        }

        if (OutboundMail::isConfigured()) {
            $this->sendMail(
                $contactMessage->email,
                new ContactMessageConfirmation($contactMessage),
                'Bågspår contact confirmation could not be sent.',
            );
        }

        $target = $request->input('source') === 'home'
            ? route('home').'#kontakt'
            : route('contact').'#kontakt';

        return redirect()
            ->to($target)
            ->with('contact_message_received', true);
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
