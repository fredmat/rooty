<?php

namespace App\Services\WordPress;

use Rooty\Foundation\Application;
use App\Contracts\WordPress\WordPressService;

/**
 * Read-only view over the registered WordPress services.
 * Provides registry introspection helpers without mixing concerns with Api.
 */
final class ServicesView
{
    /**
     * @param  \Rooty\Foundation\Application                 $app
     * @param  array<string, class-string<WordPressService>> $services
     */
    public function __construct(
        private Application $app,
        private array $services,
    ) {}

    /**
     * Whether a service name is registered (optionally also bound in the container).
     */
    public function has(string $name, bool $andBound = false): bool
    {
        $ok = isset($this->services[$name]);
        return $andBound ? ($ok && $this->app->bound($this->key($name))) : $ok;
    }

    /**
     * List registered service names.
     *
     * @return array<int,string>
     */
    public function names(): array
    {
        return array_keys($this->services);
    }

    /**
     * Container key helper ("wp.{name}").
     */
    public function key(string $name): string
    {
        return "wp.$name";
    }
}
