<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    // All roles can view customers
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['owner', 'manager', 'cashier']);
    }

    public function view(User $user, Customer $customer): bool
    {
        return in_array($user->role, ['owner', 'manager', 'cashier']);
    }

    // All roles (including Cashiers) can add customers
    public function create(User $user): bool
    {
        return in_array($user->role, ['owner', 'manager', 'cashier']);
    }

    // All roles can update customers
    public function update(User $user, Customer $customer): bool
    {
        return in_array($user->role, ['owner', 'manager', 'cashier']);
    }

    // ONLY Owners can delete customers
    public function delete(User $user, Customer $customer): bool
    {
        return $user->role === 'owner';
    }
}
