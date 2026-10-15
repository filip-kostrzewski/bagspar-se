<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Closure;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function create(Request $request, string $token): View
    {
        $email = $request->query('email');

        return view('auth.reset-password', [
            'token' => $token,
            'email' => is_string($email) ? $email : '',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $email = $request->input('email');
        if (is_string($email)) {
            $request->merge(['email' => Str::lower(trim($email))]);
        }

        $validated = $request->validate([
            'token' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc', 'max:254'],
            'password' => [
                'bail',
                'required',
                'string',
                'confirmed',
                PasswordRule::min(12),
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (is_string($value) && strlen($value) > 72) {
                        $fail('Lösenordet får vara högst 72 byte.');
                    }
                },
            ],
        ], [
            'email.required' => 'Ange e-postadressen för kontot.',
            'email.email' => 'Ange en giltig e-postadress.',
            'password.confirmed' => 'Lösenorden stämmer inte överens.',
            'password.min' => 'Lösenordet måste innehålla minst 12 tecken.',
        ]);

        $status = Password::reset(
            $validated,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'Återställningslänken är ogiltig eller har gått ut. Be om en ny länk.',
            ]);
        }

        return redirect()->route('login')->with('status', 'Lösenordet har uppdaterats. Du kan logga in nu.');
    }
}
