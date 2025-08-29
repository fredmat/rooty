<?php

namespace Rooty\Foundation\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Rooty\Foundation\Vite;

class ViteServiceProvider extends ServiceProvider
{
    /**
     * Register application services.
     */
    public function register(): void
    {
        $this->app->singleton(Vite::class, fn() => new Vite());
        $this->app->alias(Vite::class, 'vite');
    }

    /**
     * Bootstrap application services.
     */
    public function boot(): void
    {
        Blade::directive('vite', function (string $expression) {
            return sprintf("<?php echo app('%s')(%s); ?>", Vite::class, $expression);
        });
    }
}
