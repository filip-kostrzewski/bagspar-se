<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $email = $request->input('email');
        if (is_string($email)) {
            $request->merge(['email' => Str::lower(trim($email))]);
        }

        $request->validate([
            'email' => ['required', 'string', 'email:rfc', 'max:254'],
        ], [
            'email.required' => 'Ange e-postadressen för kontot.',
            'email.string' => 'Ange en giltig e-postadress.',
            'email.email' => 'Ange en giltig e-postadress.',
            'email.max' => 'E-postadressen får vara högst 254 tecken.',
        ]);

        try {
            Password::sendResetLink(['email' => $request->string('email')->toString()]);
        } catch (Throwable $exception) {
            Log::warning('Password reset notification could not be sent.', [
                'exception' => $exception::class,
            ]);
        }

        return redirect()->route('auth.password.request')->with(
            'status',
            'Om adressen hör till ett konto skickar vi en länk för att välja ett nytt lösenord.',
        );
    }
}
