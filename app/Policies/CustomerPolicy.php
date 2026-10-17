<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    public function view(User $user, Customer $customer): bool
    {
        return $user->workshop_id !== null && $user->workshop_id === $customer->workshop_id;
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->isOwner() && $this->view($user, $customer);
    }
}
