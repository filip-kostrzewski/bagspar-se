<?php

namespace App\Providers;

use App\Http\Middleware\ForceProductionHttps;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->make(ForceProductionHttps::class)->force();

        VerifyEmail::toMailUsing(fn (User $user, string $verificationUrl): MailMessage => (new MailMessage)
            ->subject('Verifiera din e-postadress för Bågspår')
            ->view(['mail.auth.notification', 'mail.auth.notification-text'], [
                'heading' => 'Verifiera din e-postadress',
                'greeting' => 'Hej '.$user->name.'!',
                'body' => 'Bekräfta e-postadressen för att säkra åtkomsten till verkstadens uppgifter.',
                'actionText' => 'Verifiera e-postadress',
                'url' => $verificationUrl,
                'fallbackText' => 'Om knappen inte fungerar kan du kopiera länken nedan och klistra in den i webbläsaren:',
                'notice' => 'Om du inte skapade kontot kan du bortse från det här meddelandet.',
            ]));

        ResetPassword::createUrlUsing(fn (User $user, string $token): string => route('auth.password.reset', [
            'token' => $token,
            'email' => $user->getEmailForPasswordReset(),
        ]));

        ResetPassword::toMailUsing(fn (User $user, string $token): MailMessage => (new MailMessage)
            ->subject('Återställ lösenordet till Bågspår')
            ->view(['mail.auth.notification', 'mail.auth.notification-text'], [
                'heading' => 'Återställ lösenordet',
                'greeting' => 'Hej '.$user->name.'!',
                'body' => 'Vi fick en begäran om att återställa lösenordet för ditt konto.',
                'actionText' => 'Välj nytt lösenord',
                'url' => route('auth.password.reset', [
                    'token' => $token,
                    'email' => $user->getEmailForPasswordReset(),
                ]),
                'fallbackText' => 'Om knappen inte fungerar kan du kopiera länken nedan och klistra in den i webbläsaren:',
                'notice' => 'Länken gäller i 60 minuter. Om du inte bad om länken behöver du inte göra något.',
            ]));
    }
}
