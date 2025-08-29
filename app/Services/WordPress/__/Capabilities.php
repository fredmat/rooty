<?php

namespace App\Services;

use WP_Role;

/**
 * Handles WordPress user role capabilities.
 *
 * This service provides utility methods to check, assign, remove, and sync capabilities across roles.
 */
class Capabilities
{
    /**
     * Determine if the current user has a given capability.
     *
     * @param  string  $capability
     * @return bool
     */
    public function currentUserCan(string $capability): bool
    {
        return current_user_can($capability);
    }

    /**
     * Determine if the current user has at least one of the given capabilities.
     *
     * @param  array<int, string>  $capabilities
     * @return bool
     */
    public function currentUserCanAny(array $capabilities): bool
    {
        foreach ($capabilities as $cap) {
            if (current_user_can($cap)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all registered role names.
     *
     * @return array<string, string>
     */
    public function getRoleNames(): array
    {
        return wp_roles()->get_names();
    }

    /**
     * Add a capability to the given roles, if they don’t already have it.
     *
     * @param  string  $cap
     * @param  array<int, string>  $roles
     * @return void
     */
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

    /**
     * Remove a capability from all registered roles.
     *
     * @param  string  $cap
     * @return void
     */
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

    /**
     * Determine if the given role has the specified capability.
     *
     * @param  string  $roleSlug
     * @param  string  $cap
     * @return bool
     */
    public function hasCap(string $roleSlug, string $cap): bool
    {
        return get_role($roleSlug)?->has_cap($cap) ?? false;
    }

    /**
     * Synchronize capabilities with a new mapping.
     *
     * This will:
     * - Remove obsolete capabilities that are no longer defined.
     * - Remove all capabilities in case they’ve changed roles.
     * - Reassign all capabilities based on the new mapping.
     *
     * @param  array<string, array<int, string>>  $newMapping  Capability => [roles...]
     * @param  array<int, string>  $oldCaps
     * @return void
     */
    public function syncCapabilities(array $newMapping, array $oldCaps = []): void
    {
        $newCaps = array_keys($newMapping);

        // Remove obsolete capabilities no longer used
        $obsoleteCaps = array_diff($oldCaps, $newCaps);

        foreach ($obsoleteCaps as $cap) {
            $this->removeCapFromAllRoles($cap);
        }

        // Remove capabilities that may have changed roles
        foreach ($newCaps as $cap) {
            $this->removeCapFromAllRoles($cap);
        }

        // Reapply the new capabilities
        foreach ($newMapping as $cap => $roles) {
            $this->addCap($cap, $roles);
        }
    }
}
