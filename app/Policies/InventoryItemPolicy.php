<?php

namespace App\Policies;

use App\Models\InventoryItem;
use App\Models\User;

class InventoryItemPolicy
{
    // All authenticated roles can view items
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['owner', 'manager', 'cashier']);
    }

    public function view(User $user, InventoryItem $inventoryItem): bool
    {
        return in_array($user->role, ['owner', 'manager', 'cashier']);
    }

    // Cashiers CANNOT add inventory items. Only Owners and Managers.
    public function create(User $user): bool
    {
        return in_array($user->role, ['owner', 'manager']);
    }

    // All roles can edit/update existing items
    public function update(User $user, InventoryItem $inventoryItem): bool
    {
        return in_array($user->role, ['owner', 'manager', 'cashier']);
    }

    // ONLY Owners can delete inventory items
    public function delete(User $user, InventoryItem $inventoryItem): bool
    {
        return $user->role === 'owner';
    }
}
