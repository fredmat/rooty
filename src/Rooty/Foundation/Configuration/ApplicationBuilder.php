<?php

namespace Rooty\Foundation\Configuration;

use Rooty\Foundation\Application;
use Rooty\Foundation\Bootstrap\RegisterProviders;
// use Illuminate\Events\EventServiceProvider;
use Illuminate\Support\Facades\Facade;
use Symfony\Component\HttpFoundation\Response;

class ApplicationBuilder
{
    /**
     * Create a new application builder instance.
     */
    public function __construct(protected Application $app)
    {
    }

    /**
     * Register the standard kernel classes for the application.
     *
     * @return $this
     */
    public function withKernels()
    {
        $this->app->singleton(\Rooty\Contracts\Http\Kernel::class, \Rooty\Http\Kernel::class);

        $this->app->singleton('response', fn() => new Response());

        return $this;
    }

    /**
     * Register additional service providers.
     *
     * @param  array  $providers
     * @param  bool  $withBootstrapProviders
     * @return $this
     */
    public function withProviders(array $providers = [], bool $withBootstrapProviders = true)
    {
        RegisterProviders::merge(
            $providers,
            $withBootstrapProviders
                ? $this->app->getBootstrapProvidersPath()
                : null
        );

        return $this;
    }

    // /**
    //  * Register the core event service provider for the application.
    //  *
    //  * @param  array|bool  $discover
    //  * @return $this
    //  */
    // public function withEvents(array|bool $discover = [])
    // {
    //     $this->app->singleton('events', function () {
    //         $this->app->register(EventServiceProvider::class);

    //         $this->app->make('events');
    //     });

    //     return $this;
    // }

    /**
     * Register the facades for the application.
     *
     * @param  bool  $aliases
     * @param  array  $userAliases
     * @return $this
     */
    public function withFacades(bool $aliases = true, array $userAliases = [])
    {
        Facade::setFacadeApplication($this->app);

        if ($aliases) {
            if (! Application::facadeAliasesRegistered()) {
                Application::markFacadeAliasesRegistered();

                $merged = array_merge(Application::getDefaultFacadeAliases(), $userAliases);

                foreach ($merged as $original => $alias) {
                    if (! class_exists($alias)) {
                        class_alias($original, $alias);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Register a callback to be invoked when the application's service providers are registered.
     *
     * @param  callable  $callback
     * @return $this
     */
    public function registered(callable $callback)
    {
        $this->app->registered($callback);

        return $this;
    }

    /**
     * Register a callback to be invoked when the application is "booting".
     *
     * @param  callable  $callback
     * @return $this
     */
    public function booting(callable $callback)
    {
        $this->app->booting($callback);

        return $this;
    }

    /**
     * Register a callback to be invoked when the application is "booted".
     *
     * @param  callable  $callback
     * @return $this
     */
    public function booted(callable $callback)
    {
        $this->app->booted($callback);

        return $this;
    }

    /**
     * Get the application instance.
     *
     * @return \Rooty\Foundation\Application
     */
    public function create()
    {
        return $this->app;
    }
}
