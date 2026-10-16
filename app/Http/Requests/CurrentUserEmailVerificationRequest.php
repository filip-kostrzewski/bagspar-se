<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CurrentUserEmailVerificationRequest extends EmailVerificationRequest
{
    private ?User $resolvedTarget = null;

    private bool $targetResolved = false;

    public function authorize(): bool
    {
        $target = $this->targetUser();
        $hash = $this->route('hash');

        if (! $target instanceof User || ! is_string($hash)) {
            return false;
        }

        if (! hash_equals(sha1($target->getEmailForVerification()), $hash)) {
            return false;
        }

        $actor = $this->user();

        return ! $actor instanceof User || $actor->is($target);
    }

    public function targetUser(): ?User
    {
        if ($this->targetResolved) {
            return $this->resolvedTarget;
        }

        $this->targetResolved = true;
        $id = $this->route('id');

        if (! is_scalar($id) || (string) $id === '') {
            return null;
        }

        $user = User::query()->find($id);
        $this->resolvedTarget = $user instanceof User ? $user : null;

        return $this->resolvedTarget;
    }

    public function fulfill(): void
    {
        $user = $this->targetUser();

        if (! $user instanceof User || $user->hasVerifiedEmail()) {
            return;
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }
    }

    protected function failedAuthorization(): void
    {
        if ($this->expectsJson()) {
            parent::failedAuthorization();
        }

        $actor = $this->user();

        if ($actor instanceof User && ! $actor->is($this->targetUser())) {
            $issue = $actor->hasVerifiedEmail()
                ? 'Den här länken kan inte verifiera kontot du är inloggad på. E-postadressen för det kontot är redan bekräftad.'
                : 'Den här länken kan inte verifiera kontot du är inloggad på. En ny verifieringslänk kan skickas till adressen nedan.';

            throw new HttpResponseException(
                redirect()->route('verification.notice')->with(
                    'verification_issue',
                    $issue,
                ),
            );
        }

        throw new HttpResponseException(
            response()->view('auth.verification-invalid', status: 403),
        );
    }
}
