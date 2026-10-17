<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Workshop;
use App\Support\PilotPeriod;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Throwable;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $email = $request->input('email');
        if (is_string($email)) {
            $request->merge(['email' => Str::lower(trim($email))]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'company' => ['required', 'string', 'max:160'],
            'email' => ['required', 'string', 'email:rfc', 'max:254', 'unique:users,email'],
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
            'company.required' => 'Ange företagets eller verkstadens namn.',
            'company.string' => 'Ange ett giltigt företagsnamn.',
            'company.max' => 'Företagsnamnet får vara högst 160 tecken.',
            'email.required' => 'Ange en e-postadress.',
            'email.string' => 'Ange en giltig e-postadress.',
            'email.email' => 'Ange en giltig e-postadress.',
            'email.max' => 'E-postadressen får vara högst 254 tecken.',
            'email.unique' => 'E-postadressen är redan registrerad.',
            'password.required' => 'Ange ett lösenord.',
            'password.string' => 'Ange ett giltigt lösenord.',
            'password.confirmed' => 'Lösenorden stämmer inte överens.',
            'password.min' => 'Lösenordet måste innehålla minst 12 tecken.',
            'terms.accepted' => 'Godkänn villkoren för att skapa kontot.',
        ]);

        $user = DB::transaction(function () use ($validated): User {
            $workshop = Workshop::query()->create([
                'name' => $validated['company'],
                'trial_ends_at' => PilotPeriod::forAccountCreatedAt(now()),
            ]);

            return $workshop->users()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => User::ROLE_OWNER,
                'terms_accepted_at' => now(),
            ]);
        });

        Auth::login($user);
        $request->session()->regenerate();

        $mailWasSent = $this->sendVerificationEmail($user);
        $destination = app()->isProduction() ? route('verification.notice') : route('app.dashboard');
        $status = $mailWasSent
            ? 'Kontot har skapats.'
            : 'Kontot har skapats, men verifieringsmeddelandet kunde inte skickas. Försök skicka det igen från verifieringssidan.';

        return redirect()->to($destination)->with('status', $status);
    }

    private function sendVerificationEmail(User $user): bool
    {
        try {
            $user->sendEmailVerificationNotification();

            return true;
        } catch (Throwable $exception) {
            Log::warning('Verification email could not be sent.', [
                'user_id' => $user->getKey(),
                'exception' => $exception::class,
            ]);

            return false;
        }
    }
}
