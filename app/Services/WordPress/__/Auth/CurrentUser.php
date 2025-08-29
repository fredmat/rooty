<?php

namespace App\Services\Auth;

use WP_User;

class CurrentUser
{
    protected ?WP_User $user;

    public function __construct()
    {
        $this->user = wp_get_current_user();
    }

    public function exists(): bool
    {
        return $this->user instanceof WP_User && $this->user->exists();
    }

    public function id(): ?int
    {
        return $this->exists() ? (int) $this->user->ID : null;
    }

    public function email(): ?string
    {
        return $this->exists() ? $this->user->user_email : null;
    }

    public function name(): ?string
    {
        return $this->exists() ? $this->user->display_name : null;
    }

    public function username(): ?string
    {
        return $this->exists() ? $this->user->user_login : null;
    }

    public function roles(): array
    {
        return $this->exists() ? (array) $this->user->roles : [];
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles(), true);
    }

    public function getRaw(): ?WP_User
    {
        return $this->exists() ? $this->user : null;
    }
}
