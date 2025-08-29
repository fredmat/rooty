<?php

namespace App\Services\WordPress;

use App\Contracts\WordPress\WordPressService;
use WP_Role;

class Capabilities implements WordPressService
{
    public function boot(): void
    {
    }

    public function currentUserCan(string $capability): bool
    {
        return current_user_can($capability);
    }

    public function currentUserCanAny(array $capabilities): bool
    {
        foreach ($capabilities as $cap) {
            if (current_user_can($cap)) {
                return true;
            }
        }
        return false;
    }

    public function getRoleNames(): array
    {
        return wp_roles()->get_names();
    }

    public function addCap(string $cap, array $roles): void
    {
        foreach ($roles as $roleSlug) {
            $role = get_role($roleSlug);
            if (! $role instanceof WP_Role) {
                continue;
            }
            if (! $role->has_cap($cap)) {
                $role->add_cap($cap);
            }
        }
    }

    public function removeCapFromAllRoles(string $cap): void
    {
        foreach (array_keys($this->getRoleNames()) as $roleSlug) {
            $role = get_role($roleSlug);
            if (! $role instanceof WP_Role) {
                continue;
            }
            if ($role->has_cap($cap)) {
                $role->remove_cap($cap);
            }
        }
    }

    public function hasCap(string $roleSlug, string $cap): bool
    {
        return get_role($roleSlug)?->has_cap($cap) ?? false;
    }

    public function syncCapabilities(array $newMapping, array $oldCaps = []): void
    {
        $newCaps = array_keys($newMapping);

        $obsoleteCaps = array_diff($oldCaps, $newCaps);
        foreach ($obsoleteCaps as $cap) {
            $this->removeCapFromAllRoles($cap);
        }

        foreach ($newCaps as $cap) {
            $this->removeCapFromAllRoles($cap);
        }

        foreach ($newMapping as $cap => $roles) {
            $this->addCap($cap, $roles);
        }
    }
}
