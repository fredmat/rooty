<?php

use Illuminate\Container\Container;
use Illuminate\Contracts\Cookie\Factory as CookieFactory;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Rooty\Foundation\Vite;
use App\Services\WordPress\Auth\Auth;
use App\Services\WordPress\Auth\Guard;
use App\Services\WordPress\Auth\CurrentUser;
use App\Services\ThemeEditor\ThemeEditor;
use App\Services\WordPress\Manager;
use App\Services\Ajax\AjaxManager;
use App\Services\WordPress\Hooks;

if (! function_exists('abort')) {
    /**
     * Abort the current request with an HTTP error.
     *
     * @param  int|null  $code     HTTP status code (e.g. 404, 500).
     * @param  string    $message  Optional message to display.
     * @param  string    $title    Optional title for the error page.
     * @param  array     $args     Optional wp_die() arguments.
     * @return never
     */
    function abort(?int $code = null, string $message = '', string $title = '', array $args = []): never {
        app(Manager::class)->abort($code, $message, $title, $args);
    }
}

if (! function_exists('ajax')) {
    /**
     * Resolve the global AJAX manager instance.
     *
     * @return \App\Services\Ajax\AjaxManager
     */
    function ajax(): AjaxManager
    {
        return app(AjaxManager::class);
    }
}

if (! function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @template TClass of object
     *
     * @param  string|class-string<TClass>|null  $abstract
     * @param  array  $parameters
     * @return ($abstract is class-string<TClass> ? TClass : ($abstract is null ? \Rooty\Foundation\Application : mixed))
     */
    function app($abstract = null, array $parameters = [])
    {
        if (is_null($abstract)) {
            return Container::getInstance();
        }

        return Container::getInstance()->make($abstract, $parameters);
    }
}

if (! function_exists('app_name')) {
    /**
     * Get the application name from configuration.
     *
     * @return string
     */
    function app_name()
    {
        return config('app.name');
    }
}

if (! function_exists('app_path')) {
    /**
     * Get the path to the application folder.
     *
     * @param  string  $path
     * @return string
     */
    function app_path($path = '')
    {
        return app()->path($path);
    }
}

if (! function_exists('asset')) {
    /**
     * Generate a full URL to a public asset.
     *
     * @param  string|null $path   Relative path from the public root.
     * @param  bool|null   $secure Force HTTPS if true, HTTP if false, auto if null.
     * @return string
     */
    function asset(?string $path = null, ?bool $secure = null): string
    {
        return home_url($path ?: '/');
    }
}

if (! function_exists('asset_acf_base')) {
    /**
     * Resolve the base subpath for ACF assets (relative to the public directory).
     *
     * Examples:
     *   - target=public → "acf/assets"
     *   - target=build  → "build/acf/assets"
     *   - target=foo    → "foo/acf/assets"
     *
     * @return string
     */
    function asset_acf_base(): string
    {
        $target     = strtolower(env('ACF_ASSETS_TARGET', 'public'));
        $buildDir   = env('BUILD_SUBDIR', 'build');
        $acfSubpath = trim(env('ACF_ASSETS_SUBPATH', 'acf/assets'), '/');

        return match ($target) {
            'build'  => trim($buildDir, '/') . '/' . $acfSubpath,
            'public' => $acfSubpath,
            default  => trim($target, '/') . '/' . $acfSubpath,
        };
    }
}

if (! function_exists('asset_acf')) {
    /**
     * Generate the public URL for an ACF asset.
     *
     * @param  string|null $path   Relative path inside the ACF assets directory.
     * @param  bool|null   $secure Force HTTPS if true, HTTP if false, auto if null.
     * @return string
     */
    function asset_acf(?string $path = null, ?bool $secure = null): string
    {
        $base  = asset_acf_base();
        $final = rtrim($base, '/') . ($path ? '/' . ltrim($path, '/') : '');

        return asset($final, $secure);
    }
}

if (! function_exists('asset_acf_path')) {
    /**
     * Get the filesystem path for an ACF asset (inside the public directory).
     *
     * @param  string|null $path Relative path inside the ACF assets directory.
     * @return string
     */
    function asset_acf_path(?string $path = null): string
    {
        $base  = asset_acf_base();
        $final = rtrim($base, '/') . ($path ? '/' . ltrim($path, '/') : '');

        return public_path($final);
    }
}

if (! function_exists('auth')) {
    /**
     * Get the authentication service instance.
     *
     * @return \App\Services\WordPress\Auth\Auth
     */
    function auth(): Auth
    {
        return app(Auth::class);
    }
}

if (! function_exists('base_path')) {
    /**
     * Get the path to the base of the install.
     *
     * @param  string  $path
     * @return string
     */
    function base_path($path = '')
    {
        return app()->basePath($path);
    }
}

if (! function_exists('cache')) {
    /**
     * Get / set the specified cache value.
     *
     * If an array is passed, we'll assume you want to put to the cache.
     *
     * @param  string|array<string, mixed>|null  $key  key|data
     * @param  mixed  $default  default|expiration|null
     * @return ($key is null ? \Illuminate\Cache\CacheManager : ($key is string ? mixed : bool))
     *
     * @throws \InvalidArgumentException
     */
    function cache($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('cache');
        }

        if (is_string($key)) {
            return app('cache')->get($key, $default);
        }

        if (! is_array($key)) {
            throw new InvalidArgumentException(
                'When setting a value in the cache, you must pass an array of key / value pairs.'
            );
        }

        return app('cache')->put(key($key), reset($key), ttl: $default);
    }
}

if (! function_exists('config')) {
    /**
     * Get / set the specified configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array<string, mixed>|string|null  $key
     * @param  mixed  $default
     * @return ($key is null ? \Illuminate\Config\Repository : ($key is string ? mixed : null))
     */
    function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('config');
        }

        if (is_array($key)) {
            return app('config')->set($key);
        }

        return app('config')->get($key, $default);
    }
}

if (! function_exists('config_path')) {
    /**
     * Get the configuration path.
     *
     * @param  string  $path
     * @return string
     */
    function config_path($path = '')
    {
        return app()->configPath($path);
    }
}

if (! function_exists('cookie')) {
    /**
     * Create a new cookie instance.
     *
     * @param  string|null  $name
     * @param  string|null  $value
     * @param  int  $minutes
     * @param  string|null  $path
     * @param  string|null  $domain
     * @param  bool|null  $secure
     * @param  bool  $httpOnly
     * @param  bool  $raw
     * @param  string|null  $sameSite
     * @return ($name is null ? \Illuminate\Cookie\CookieJar : \Symfony\Component\HttpFoundation\Cookie)
     */
    function cookie($name = null, $value = null, $minutes = 0, $path = null, $domain = null, $secure = null, $httpOnly = true, $raw = false, $sameSite = null)
    {
        $cookie = app(CookieFactory::class);

        if (is_null($name)) {
            return $cookie;
        }

        return $cookie->make($name, $value, $minutes, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }
}

if (! function_exists('decrypt')) {
    /**
     * Decrypt the given value.
     *
     * @param  string  $value
     * @param  bool  $unserialize
     * @return mixed
     */
    function decrypt($value, $unserialize = true)
    {
        return app('encrypter')->decrypt($value, $unserialize);
    }
}

if (! function_exists('encrypt')) {
    /**
     * Encrypt the given value.
     *
     * @param  mixed  $value
     * @param  bool  $serialize
     * @return string
     */
    function encrypt($value, $serialize = true)
    {
        return app('encrypter')->encrypt($value, $serialize);
    }
}

if (! function_exists('event')) {
    /**
     * Dispatch an event and call the listeners.
     *
     * @param  string|object  $event
     * @param  mixed  $payload
     * @param  bool  $halt
     * @return array|null
     */
    function event(...$args)
    {
        return app('events')->dispatch(...$args);
    }
}

if (! function_exists('guard')) {
    /**
     * Get the guard service instance.
     *
     * @return \App\Services\WordPress\Auth\Guard
     */
    function guard(): Guard
    {
        return app(Guard::class);
    }
}

if (! function_exists('hooks')) {
    /**
     * Resolve the WordPress Hooks service.
     *
     * @return Hooks
     */
    function hooks(): Hooks
    {
        /** @var Hooks $resolved */
        $resolved = app(Hooks::class);
        return $resolved;
    }
}

if (! function_exists('http_scheme')) {
    /**
     * Get the current request scheme.
     *
     * @return string
     */
    function http_scheme(): string
    {
        return is_secure() ? 'https' : 'http';
    }
}

if (! function_exists('is_backend')) {
    /**
     * Determine if the current request is for the WP admin.
     *
     * @return bool
     */
    function is_backend(): bool
    {
        return is_admin();
    }
}

if (! function_exists('is_frontend')) {
    /**
     * Determine if the current request is on the frontend (not in admin).
     *
     * @return bool
     */
    function is_frontend(): bool
    {
        return ! is_admin();
    }
}

if (! function_exists('is_secure')) {
    /**
     * Determine whether the current request is HTTPS (TLS).
     * 
     * @return bool
     */
    function is_secure(): bool
    {
        return is_ssl();
    }
}

if (! function_exists('join_url')) {
    /**
     * Join base URL + path with a single slash.
     *
     * @param  string       $baseUrl
     * @param  string|null  $path
     * @return string
     */
    function join_url(string $baseUrl, ?string $path = null): string
    {
        return app()->joinUrl($baseUrl, $path);
    }
}

if (! function_exists('logger')) {
    /**
     * Log a debug message to the logs.
     *
     * @param  string|null  $message
     * @param  array  $context
     * @return ($message is null ? \Illuminate\Log\LogManager : null)
     */
    function logger($message = null, array $context = [])
    {
        if (is_null($message)) {
            return app('log');
        }

        return app('log')->debug($message, $context);
    }
}

if (! function_exists('public_path')) {
    /**
     * Get the path to the public folder.
     *
     * @param  string  $path
     * @return string
     */
    function public_path($path = '')
    {
        return app()->publicPath($path);
    }
}

if (! function_exists('public_url')) {
    /**
     * Get the public base URL of the application (no trailing slash).
     *
     * @param  bool|null  $secure
     * @return string
     */
    function public_url(?bool $secure = null): string
    {
        $base = '';

        if (function_exists('config')) {
            $base = (string) (config('app.url') ?? '');
        }

        if ($base === '' && function_exists('home_url')) {
            $base = (string) home_url('/');
        }

        if ($base === '') {
            $host   = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
            $isTls  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (($_SERVER['SERVER_PORT'] ?? '') === '443');
            $scheme = $isTls ? 'https' : 'http';
            $base   = $scheme . '://' . $host . '/';
        }

        $base = rtrim($base, '/');

        if ($secure !== null) {
            $base = preg_replace('~^https?://~i', $secure
                ? 'https://'
                : 'http://', $base) ?: $base;
        }

        return $base;
    }
}

if (! function_exists('resolve')) {
    /**
     * Resolve a service from the container.
     *
     * @template TClass of object
     *
     * @param  string|class-string<TClass>  $name
     * @param  array  $parameters
     * @return ($name is class-string<TClass> ? TClass : mixed)
     */
    function resolve($name, array $parameters = [])
    {
        return app($name, $parameters);
    }
}

if (! function_exists('resource_path')) {
    /**
     * Get the path to the resources folder.
     *
     * @param  string  $path
     * @return string
     */
    function resource_path($path = '')
    {
        return app()->resourcePath($path);
    }
}

if (! function_exists('storage_path')) {
    /**
     * Get the path to the storage folder.
     *
     * @param  string  $path
     * @return string
     */
    function storage_path($path = '')
    {
        return app()->storagePath($path);
    }
}

if (! function_exists('theme_editor')) {
    /**
     * Get the Editor service instance.
     *
     * @return \App\Services\ThemeEditor\Editor
     */
    function theme_editor()
    {
        return app(ThemeEditor::class);
    }
}

if (! function_exists('user')) {
    /**
     * Get the currently authenticated user.
     *
     * @return \App\Services\WordPress\Auth\CurrentUser|null
     */
    function user(): ?CurrentUser
    {
        return auth()->user();
    }
}

if (! function_exists('view')) {
    /**
     * Get the evaluated view contents for the given view.
     *
     * @param  string|null  $view
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $data
     * @param  array  $mergeData
     * @return ($view is null ? \Illuminate\Contracts\View\Factory : \Illuminate\Contracts\View\View)
     */
    function view($view = null, $data = [], $mergeData = [])
    {
        $factory = app(ViewFactory::class);

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($view, $data, $mergeData);
    }
}

if (! function_exists('vite')) {
    /**
     * Generate Vite asset tags.
     *
     * @param  string|array  $entry
     * @return string
     */
    function vite(string|array $entry): string
    {
        return app(Vite::class)($entry);
    }
}

if (! function_exists('vite_asset')) {
    /**
     * Résout le chemin final (hashé) d'un asset géré par Vite.
     *
     * @param  string       $path  ex: 'resources/images/logo.png'
     * @param  string|null  $build ex: 'build' (défaut)
     * @return string
     */
    function vite_asset(string $path, ?string $build = null): string
    {
        return app(Vite::class)->asset($path, $build);
    }
}

if (! function_exists('wp_path')) {
    /**
     * Get the absolute path to the WordPress installation.
     *
     * @param  string  $path  Optional relative path (e.g. 'wp-content/uploads')
     * @return string
     */
    function wp_path(string $path = ''): string
    {
        $base = rtrim(ABSPATH, DIRECTORY_SEPARATOR);

        if ($path !== '') {
            $path = ltrim($path, DIRECTORY_SEPARATOR);
            return $base . DIRECTORY_SEPARATOR . $path;
        }

        return $base . DIRECTORY_SEPARATOR;
    }
}

if (! function_exists('wp_admin_path')) {
    /**
     * Get the absolute path inside wp-admin.
     *
     * @param  string  $path  Optional relative path inside wp-admin
     * @return string
     */
    function wp_admin_path(string $path = ''): string
    {
        return wp_path('wp-admin' . ($path ? '/' . ltrim($path, '/') : ''));
    }
}
