<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    public function view(User $user, Property $property): bool
    {
        return $user->isAdministrator()
            || $property->user_id === $user->id
            || (
                $property->verification_status === 'verified'
                && $property->availability_status === 'available'
            );
    }

    public function create(User $user): bool
    {
        return $user->isActive()
            && $user->canAdvertiseProperties();
    }

    public function update(
        User $user,
        Property $property
    ): bool {
        return $user->isAdministrator()
            || $property->user_id === $user->id;
    }

    public function delete(
        User $user,
        Property $property
    ): bool {
        return $user->isAdministrator()
            || $property->user_id === $user->id;
    }
}