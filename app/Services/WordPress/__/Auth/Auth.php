<?php

namespace App\Services\Auth;

use App\Services\Capabilities;

class Auth
{
    public function __construct(
        protected CurrentUser $user,
        protected Capabilities $caps
    ) {}

    public function check(): bool
    {
        return $this->user->exists();
    }

    public function guest(): bool
    {
        return ! $this->check();
    }

    public function user(): ?CurrentUser
    {
        return $this->check() ? $this->user : null;
    }

    public function id(): ?int
    {
        return $this->user->id();
    }

    public function roles(): array
    {
        return $this->user->roles();
    }

    public function hasRole(string $role): bool
    {
        return $this->user->hasRole($role);
    }

    public function can(string $cap): bool
    {
        return $this->caps->currentUserCan($cap);
    }

    public function canAny(array $caps): bool
    {
        return $this->caps->currentUserCanAny($caps);
    }
}
