<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\WordPress\WordPressService;
use App\Services\WordPress\Api;
use RuntimeException;

class WordPressServiceProvider extends ServiceProvider
{
    /**
     * Map of WordPress services to be registered and exposed.
     *
     * @var array<string, class-string<WordPressService>>
     */
    protected array $services = [
        'conflicts' => \App\Services\WordPress\Conflicts::class,
        'caps'      => \App\Services\WordPress\Capabilities::class,
        'hooks'     => \App\Services\WordPress\Hooks::class,
    ];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->registerApi();
        $this->registerWpServices();
    }

    /**
     * Bootstrap the services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->app->make(Api::class)->boot();
    }

    /**
     * Register the central WordPress API hub.
     *
     * @return void
     */
    protected function registerApi(): void
    {
        $this->app->singleton(Api::class, fn ($app) => new Api($app, $this->services));
        $this->app->alias(Api::class, 'wp');
    }

    /**
     * Register all WordPress services defined in $services.
     *
     * @return void
     */
    protected function registerWpServices(): void
    {
        foreach ($this->services as $name => $class) {
            if (! class_exists($class)) {
                throw new RuntimeException("WordPress service [$name] points to missing class [$class].");
            }

            if (! is_subclass_of($class, WordPressService::class)) {
                throw new RuntimeException("WordPress service [$name] must implement WordPressService contract.");
            }

            $this->app->singleton($class);
            $this->app->alias($class, "wp.$name");
        }
    }
}
