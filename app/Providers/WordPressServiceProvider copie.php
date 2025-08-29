<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\AbortManagerInterface;
use App\Services\Manager;
use App\Services\Capabilities;
use App\Services\Auth\Auth;
use App\Services\Auth\Guard;
use App\Services\Auth\CurrentUser;
use App\Services\BodyClasses;
use App\Services\Hooks;
use App\Services\Assets;
use RuntimeException;

/**
 * Class WordPressServiceProvider
 *
 * Binds WordPress-related services into the service container.
 */
class WordPressServiceProvider extends ServiceProvider
{
    /**
     * Register WordPress services into the container.
     *
     * @return void
     */
    public function register(): void
    {
        $this->registerCapabilities();
        $this->registerAuth();
        $this->registerAbortManager();
        $this->registerManager();
        $this->registerBodyClasses();
        $this->registerHooks();
        $this->registerAssets();
    }

    /**
     * Boot WordPress.
     *
     * @return void
     */
    public function boot(): void
    {
        // Optional: guard against CLI if your Manager does WP-specific bootstrapping.
        // if ($this->app->runningInConsole()) {
        //     return;
        // }

        $this->app->make(Manager::class)->boot();
    }

    /**
     * Register the Capabilities service.
     *
     * @return void
     */
    protected function registerCapabilities(): void
    {
        $this->app->singleton(Capabilities::class);
        $this->app->alias(Capabilities::class, 'wp.caps');
    }

    /**
     * Register authentication-related services.
     *
     * @return void
     */
    protected function registerAuth(): void
    {
        // CurrentUser
        $this->app->singleton(CurrentUser::class);
        $this->app->alias(CurrentUser::class, 'wp.user');

        // Auth
        /**
         * Register the Auth service.
         *
         * @param  \Rooty\Contracts\Foundation\Application  $app
         * @return \App\Services\Auth\Auth
         */
        $this->app->singleton(Auth::class, function ($app) {
            return new Auth(
                $app->make(CurrentUser::class),
                $app->make(Capabilities::class)
            );
        });
        $this->app->alias(Auth::class, 'wp.auth');

        // Guard
        /**
         * Register the Guard service.
         *
         * @param  \Rooty\Contracts\Foundation\Application  $app
         * @return \App\Services\Auth\Guard
         */
        $this->app->singleton(Guard::class, function ($app) {
            return new Guard(
                $app->make(Capabilities::class),
                $app->make(Auth::class)
            );
        });
        $this->app->alias(Guard::class, 'wp.guard');
    }

    /**
     * Register the AbortManager service based on the configured implementation.
     *
     * @throws \RuntimeException If the configured class is invalid.
     * @return void
     */
    protected function registerAbortManager(): void
    {
        /**
         * Register the AbortManager service based on the configured class.
         *
         * @return \App\Contracts\AbortManagerInterface
         *
         * @throws \RuntimeException
         */
        $this->app->singleton(AbortManagerInterface::class, function () {
            $class = config('wp.abort.manager');

            $isValid = is_string($class)
                && class_exists($class)
                && is_subclass_of($class, AbortManagerInterface::class);

            if (! $isValid) {
                throw new RuntimeException(
                    "Invalid AbortManager class configured in [config/wp.php] under ['abort.manager']." . PHP_EOL .
                    "Expected a valid class name implementing " . AbortManagerInterface::class . ', got: ' . var_export($class, true)
                );
            }

            return new $class();
        });

        $this->app->alias(AbortManagerInterface::class, 'wp.abort');
    }

    /**
     * Register the main WordPress Manager service.
     *
     * @return void
     */
    protected function registerManager(): void
    {
        /**
         * Register the main WordPress Manager service.
         *
         * @param  \Rooty\Contracts\Foundation\Application  $app
         * @return \App\Services\Manager
         */
        $this->app->singleton(Manager::class, function ($app) {
            return new Manager(
                $app,
                $app->make(AbortManagerInterface::class)
            );
        });

        $this->app->alias(Manager::class, 'wp.manager');
    }

    /**
     * Register the BodyClasses service.
     *
     * @return void
     */
    protected function registerBodyClasses(): void
    {
        $this->app->singleton(BodyClasses::class);
        $this->app->alias(BodyClasses::class, 'wp.body_classes');
    }

    /**
     * Register the Hooks service.
     *
     * @return void
     */
    protected function registerHooks(): void
    {
        $this->app->singleton(Hooks::class);
        $this->app->alias(Hooks::class, 'wp.hooks');
    }

    protected function registerAssets(): void
    {
        $this->app->singleton(Assets::class, fn () => new Assets());
        $this->app->alias(Assets::class, 'wp.assets');
    }
}
