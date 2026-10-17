<?php

namespace App\Http\Controllers;

use App\Mail\QuoteAcceptedMail;
use App\Models\ProjectEvent;
use App\Models\Quote;
use App\Models\User;
use App\Support\PrivateImageResponse;
use App\Support\WorkshopLogo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class QuotePublicController extends Controller
{
    public function show(string $token): Response
    {
        $quote = $this->quoteForToken($token);
        $project = $quote->project()->with('customer')->firstOrFail();
        $workshop = $quote->workshop()->firstOrFail();

        return response(view('quote.show', compact('quote', 'project', 'workshop', 'token')))
            ->header('X-Robots-Tag', 'noindex, nofollow')
            ->header('Cache-Control', 'private, no-store');
    }

    public function logo(string $token): Response
    {
        $image = WorkshopLogo::visible($this->quoteForToken($token)->workshop()->firstOrFail());
        abort_if($image === null, 404);

        return PrivateImageResponse::fromBytes($image['bytes'], $image['mime']);
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $quote = $this->quoteForToken($token);
        $back = redirect()->route('quote.show', ['token' => $token]);

        if ($quote->isAccepted()) {
            return $back->with('status', 'Offerten är godkänd.');
        }

        $name = $request->input('name');

        if (is_string($name)) {
            $request->merge(['name' => trim($name)]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
        ], [
            'name.required' => 'Ange ditt namn.',
            'name.string' => 'Ange ditt namn som text.',
            'name.max' => 'Namnet får vara högst 150 tecken.',
        ]);

        $saved = Quote::query()
            ->whereKey($quote->getKey())
            ->where('status', Quote::STATUS_SENT)
            ->update([
                'status' => Quote::STATUS_ACCEPTED,
                'accepted_name' => $validated['name'],
                'accepted_at' => now(),
            ]);

        if ($saved === 0) {
            return $back->with('status', 'Offerten är godkänd.');
        }

        $quote->refresh();
        $project = $quote->project()->firstOrFail();
        $project->recordCustomerEvent($validated['name'], ProjectEvent::KIND_QUOTE_ACCEPTED, 'Offerten godkändes av '.$validated['name'].'.');

        $owners = User::query()
            ->where('workshop_id', $quote->workshop_id)
            ->where('role', User::ROLE_OWNER)
            ->get();

        foreach ($owners as $owner) {
            try {
                Mail::to($owner->email)->send(new QuoteAcceptedMail($quote));
            } catch (Throwable $exception) {
                Log::warning('Quote accepted mail could not be sent.', [
                    'quote_id' => $quote->getKey(),
                    'exception' => $exception::class,
                ]);
            }
        }

        return $back->with('status', 'Offerten är godkänd.');
    }

    private function quoteForToken(string $token): Quote
    {
        return Quote::query()
            ->sentOrAccepted()
            ->where('token', $token)
            ->with('lines')
            ->firstOrFail();
    }
}
