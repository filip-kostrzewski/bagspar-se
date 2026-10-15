<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CurrentUserEmailVerificationRequest extends EmailVerificationRequest
{
    protected function failedAuthorization(): void
    {
        if ($this->expectsJson()) {
            parent::failedAuthorization();
        }

        $user = $this->user();
        $issue = $user instanceof User && $user->hasVerifiedEmail()
            ? 'Den här länken kan inte verifiera kontot du är inloggad på. E-postadressen för det kontot är redan bekräftad.'
            : 'Den här länken kan inte verifiera kontot du är inloggad på. En ny verifieringslänk kan skickas till adressen nedan.';

        throw new HttpResponseException(
            redirect()->route('verification.notice')->with(
                'verification_issue',
                $issue,
            ),
        );
    }
}
