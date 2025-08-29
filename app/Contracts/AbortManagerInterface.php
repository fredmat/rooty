<?php

namespace App\Contracts;

interface AbortManagerInterface
{
    public function boot(): void;

    public function abort(?int $code = null, string $message = '', string $title = '', array $args = []): never;
}
