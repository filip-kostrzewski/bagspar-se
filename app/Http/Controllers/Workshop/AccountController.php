<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Models\User;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Throwable;

class AccountController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $this->currentUser($request);

        return view('app.account.edit', [
            'user' => $user,
            'workshop' => $user->workshop,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $this->currentUser($request);

        $email = $request->input('email');
        if (is_string($email)) {
            $request->merge(['email' => Str::lower(trim($email))]);
        }

        $validated = $request->validateWithBag('profile', [
            'name' => ['required', 'string', 'max:150'],
            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:254',
                Rule::unique('users', 'email')->ignore($user->getKey()),
            ],
            'current_password' => ['bail', 'required', 'string', $this->matchesCurrentPassword($user)],
        ], [
            'name.required' => 'Ange ditt namn.',
            'name.string' => 'Ange ditt namn som text.',
            'name.max' => 'Namnet får vara högst 150 tecken.',
            'email.required' => 'Ange en e-postadress.',
            'email.string' => 'Ange en giltig e-postadress.',
            'email.email' => 'Ange en giltig e-postadress.',
            'email.max' => 'E-postadressen får vara högst 254 tecken.',
            'email.unique' => 'E-postadressen är redan registrerad.',
            'current_password.required' => 'Ange ditt nuvarande lösenord.',
            'current_password.string' => 'Ange ditt nuvarande lösenord.',
        ]);

        $emailChanged = Str::lower($user->email) !== $validated['email'];

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        if ($emailChanged) {
            $user->email_verified_at = null;
        }
        $user->save();

        if (! $emailChanged) {
            return redirect()->route('app.account.edit')->with('status', 'Uppgifterna är sparade.');
        }

        $this->sendVerificationEmail($user);

        return redirect()->route('app.account.edit')->with(
            'status',
            'Uppgifterna är sparade. Vi har skickat ett mejl till den nya adressen, bekräfta den för att behålla åtkomsten.'
        );
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $this->currentUser($request);

        $validated = $request->validateWithBag('password', [
            'current_password' => ['bail', 'required', 'string', $this->matchesCurrentPassword($user)],
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
        ], [
            'current_password.required' => 'Ange ditt nuvarande lösenord.',
            'current_password.string' => 'Ange ditt nuvarande lösenord.',
            'password.required' => 'Ange ett nytt lösenord.',
            'password.string' => 'Ange ett giltigt lösenord.',
            'password.confirmed' => 'Lösenorden stämmer inte överens.',
            'password.min' => 'Lösenordet måste innehålla minst 12 tecken.',
        ]);

        $user->password = $validated['password'];
        $user->save();

        Auth::logoutOtherDevices($validated['password']);
        $request->session()->regenerate();

        return redirect()->route('app.account.edit')->with('status', 'Lösenordet är bytt.');
    }

    private function currentUser(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        return $user;
    }

    private function matchesCurrentPassword(User $user): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($user): void {
            if (! is_string($value) || ! Hash::check($value, $user->password)) {
                $fail('Fel lösenord.');
            }
        };
    }

    private function sendVerificationEmail(User $user): void
    {
        try {
            $user->sendEmailVerificationNotification();
        } catch (Throwable $exception) {
            Log::warning('Verification email could not be sent.', [
                'user_id' => $user->getKey(),
                'exception' => $exception::class,
            ]);
        }
    }
}
