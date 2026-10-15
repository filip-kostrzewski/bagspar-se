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
            ->greeting('Hej '.$user->name.'!')
            ->line('Bekräfta e-postadressen för att säkra åtkomsten till verkstadens uppgifter.')
            ->action('Verifiera e-postadress', $verificationUrl)
            ->line('Om du inte skapade kontot kan du bortse från det här meddelandet.'));

        ResetPassword::createUrlUsing(fn (User $user, string $token): string => route('auth.password.reset', [
            'token' => $token,
            'email' => $user->getEmailForPasswordReset(),
        ]));

        ResetPassword::toMailUsing(fn (User $user, string $token): MailMessage => (new MailMessage)
            ->subject('Återställ lösenordet till Bågspår')
            ->greeting('Hej '.$user->name.'!')
            ->line('Vi fick en begäran om att återställa lösenordet för ditt konto.')
            ->action('Välj nytt lösenord', route('auth.password.reset', [
                'token' => $token,
                'email' => $user->getEmailForPasswordReset(),
            ]))
            ->line('Länken gäller i 60 minuter. Om du inte bad om länken behöver du inte göra något.'));
    }
}
