<?php

namespace App\Services\WordPress;

use Rooty\Foundation\Application;
use App\Contracts\WordPress\WordPressService;
use BadMethodCallException;
use RuntimeException;

/**
 * Small hub to access WordPress-dedicated services via container bindings.
 *
 * Responsibilities:
 * - Provide an explicit resolver API: service($name): WordPressService
 * - Expose a namespaced registry view via services() for introspection
 * - Support magic resolution through __get() / __call() for ergonomics
 * - Validate container bindings and surface precise errors
 *
 * Example usage:
 *  app('wp')->service('caps')->boot();
 *  app('wp')->caps()->someMethod(); // via __call()
 *  app('wp')->caps->someMethod();   // via __get()
 *  app('wp')->services()->names();  // registry introspection
 *
 * Container keys must follow: "wp.{name}".
 *
 * @phpstan-type ServiceMap array<string, class-string<WordPressService>>
 */
class Api
{
    /**
     * Registered WordPress services (name => class FQCN implementing WordPressService).
     *
     * @var array<string, class-string<WordPressService>>
     */
    protected array $services;

    /**
     * Create a new API hub instance.
     *
     * @param  \Rooty\Foundation\Application                 $app
     * @param  array<string, class-string<WordPressService>> $services
     */
    public function __construct(
        protected Application $app,
        array $services = [],
    ) {
        $this->services = $services;
    }

    /**
     * Namespaced access to the service registry (introspection utilities).
     */
    public function services(): ServicesView
    {
        return new ServicesView($this->app, $this->services);
    }

    /**
     * Boot all registered services (no-op if a binding does not implement the interface).
     */
    public function boot(): void
    {
        foreach ($this->services as $name => $_class) {
            $instance = $this->app->make("wp.$name");

            if ($instance instanceof WordPressService) {
                $instance->boot();
            }
        }
    }

    /**
     * Resolve a service by name (explicit API).
     *
     * @throws \BadMethodCallException If the service name is unknown.
     * @throws \RuntimeException       If the class is missing or the container binding is invalid.
     */
    public function service(string $name): WordPressService
    {
        return $this->resolve($name);
    }

    /**
     * Magic call to resolve a service without arguments.
     *
     * Syntactic sugar to allow: app('wp')->caps()
     *
     * @throws \BadMethodCallException
     */
    public function __call(string $name, array $arguments): WordPressService
    {
        if (! empty($arguments)) {
            throw new BadMethodCallException(
                "Dynamic call [{$name}] does not accept arguments. First resolve the service, then call its methods."
            );
        }

        return $this->service($name);
    }

    /**
     * Magic getter to resolve a service.
     *
     * Example: app('wp')->caps
     */
    public function __get(string $name): WordPressService
    {
        return $this->service($name);
    }

    /**
     * Allow isset(app('wp')->caps) checks.
     */
    public function __isset(string $name): bool
    {
        return isset($this->services[$name]) && $this->app->bound("wp.$name");
    }

    /**
     * Resolve a service instance by its key and validate its type.
     *
     * @throws \BadMethodCallException|\RuntimeException
     */
    protected function resolve(string $name): WordPressService
    {
        if (! isset($this->services[$name])) {
            $available = implode(', ', $this->services()->names());
            $hint = $available !== '' ? "Available: [{$available}]" : 'No services are registered.';

            throw new BadMethodCallException("Unknown WP service [{$name}]. {$hint}");
        }

        $class = $this->services[$name];

        if (! class_exists($class)) {
            throw new RuntimeException(
                "Configured WP service [{$name}] points to missing class [$class]."
            );
        }

        $key = "wp.$name";

        if (! $this->app->bound($key)) {
            throw new RuntimeException(
                "WP service [{$name}] is not bound in the container under key [$key]."
            );
        }

        $instance = $this->app->make($key);

        if (! $instance instanceof WordPressService) {
            $given = is_object($instance) ? get_debug_type($instance) : gettype($instance);

            throw new RuntimeException(
                "WP service [{$name}] must implement ".WordPressService::class.". Got: {$given}."
            );
        }

        return $instance;
    }
}
