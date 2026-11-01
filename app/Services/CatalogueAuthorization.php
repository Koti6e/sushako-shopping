<?php

namespace App\Services;

use App\Models\SellerCataloguePermission;
use App\Models\User;
use App\Models\Vendor;

class CatalogueAuthorization
{
    public function allows(?User $user, Vendor $vendor, string $permission): bool
    {
        if (! $user) return false;
        if ($user->hasRole(User::ROLE_SUPER_ADMIN)) return true;
        if ($vendor->user_id === $user->id) return true;

        return SellerCataloguePermission::query()
            ->where('vendor_id', $vendor->id)
            ->where('user_id', $user->id)
            ->where('permission', $permission)
            ->exists();
    }

    public function enforce(?User $user, Vendor $vendor, string $permission): void
    {
        abort_unless($this->allows($user, $vendor, $permission), 403);
    }
}
