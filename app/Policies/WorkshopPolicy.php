<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Workshop;

class WorkshopPolicy
{
    public function update(User $user, Workshop $workshop): bool
    {
        return $user->isOwner() && $user->workshop_id === $workshop->getKey();
    }

    public function invite(User $user, Workshop $workshop): bool
    {
        return $this->update($user, $workshop);
    }
}
