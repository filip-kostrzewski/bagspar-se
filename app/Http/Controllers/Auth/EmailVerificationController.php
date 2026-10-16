<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\CurrentUserEmailVerificationRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class EmailVerificationController extends Controller
{
    public function notice(Request $request): RedirectResponse|View
    {
        $user = $request->user();

        abort_unless($user instanceof User, 401);
        $emailVerified = $user->hasVerifiedEmail();

        if ($emailVerified && ! $request->session()->has('verification_issue')) {
            return redirect()->route('app.dashboard');
        }

        return view('auth.verify-email', [
            'email' => $user->email,
            'emailVerified' => $emailVerified,
        ]);
    }

    public function verify(CurrentUserEmailVerificationRequest $request): RedirectResponse
    {
        $actor = $request->user();
        $target = $request->targetUser();

        if ($actor instanceof User && $target instanceof User && $actor->is($target)) {
            if ($actor->hasVerifiedEmail()) {
                return redirect()->route('app.dashboard');
            }

            $request->fulfill();

            return redirect()->route('app.dashboard')->with('status', 'E-postadressen har verifierats.');
        }

        $request->fulfill();

        // The signed URL is stored as url.intended, the same way auth middleware does,
        // so the next login returns the visitor to this link and then the workshop.
        return redirect()->guest(route('login'))->with(
            'email_verified_notice',
            'Din e-post är verifierad. Logga in med samma e-postadress och lösenord som när kontot skapades.',
        );
    }

    public function send(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user instanceof User, 401);

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('app.dashboard')->with('status', 'E-postadressen är redan verifierad.');
        }

        try {
            $user->sendEmailVerificationNotification();

            return back()->with('status', 'Ett nytt verifieringsmeddelande har skickats.');
        } catch (Throwable $exception) {
            Log::warning('Verification email could not be resent.', [
                'user_id' => $user->getKey(),
                'exception' => $exception::class,
            ]);

            return back()->with('status', 'Verifieringsmeddelandet kunde inte skickas just nu. Försök igen senare.');
        }
    }
}
