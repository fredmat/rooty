<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Rooty\Foundation\Application;
use App\Services\ACF\ACF;
use App\Services\ACF\Helper;
use RuntimeException;

/**
 * Service provider for integrating Advanced Custom Fields (ACF) into Rooty.
 *
 * Responsibilities:
 * - Normalize and expose ACF settings to the container.
 * - Validate and include the bundled ACF core.
 * - Register WordPress filters that configure ACF at runtime.
 * - Guard against double-loading ACF from other sources.
 * - Enforce presence and writability of required directories and assets.
 */
class ACFServiceProvider extends ServiceProvider
{
    /**
     * Normalized ACF settings (memoized).
     *
     * @var array<string, mixed>
     */
    protected array $settings = [];

    /**
     * Register container bindings for ACF.
     */
    public function register(): void
    {
        $this->registerSettings();
        // $this->registerHelper();
        // $this->registerAcf();
    }

    /**
     * Bootstrap the ACF integration.
     *
     * Order of operations:
     * 1) Guard against double loading.
     * 2) Ensure public assets folder (prod hard guard).
     * 3) Ensure save_json directory (prod hard guard, auto-create in non-prod).
     * 4) Include ACF core file and assert class presence.
     * 5) If in a WP runtime, register filters & option pages.
     * 6) Resolve the ACF service to finalize wiring.
     */
    public function boot(): void
    {
        $this->guardDoubleLoad();
        $this->ensurePublicAssets();
        $this->ensureSaveJsonDirectory();
        $this->includeAcfCore();

        if ($this->hasWpRuntime()) {
            $this->registerWordPressFilters();
            $this->registerOptionPages();
        }

        // Ensure service is resolved (builds Helper dependency as well)
        $this->app->make(ACF::class);
    }

    /**
     * Prevent loading if ACF already exists in the global runtime.
     *
     * @throws RuntimeException If the global ACF class is already loaded.
     */
    protected function guardDoubleLoad(): void
    {
        if (class_exists('ACF', false)) {
            throw new RuntimeException('ACF already loaded by another source. Rooty must be the only loader.');
        }
    }

    /**
     * Normalize ACF settings and expose them in the container.
     *
     * The snapshot is captured at registration time for predictability.
     */
    protected function registerSettings(): void
    {
        $this->settings = $this->normalizeSettings((array) config('acf.settings', []));

        $settings = $this->settings;
        $this->app->singleton('acf.settings', static fn () => $settings);
    }

    /**
     * Normalize and validate settings (pure function).
     *
     * @param  array<string, mixed>  $cfg
     * @return array<string, mixed>
     */
    protected function normalizeSettings(array $cfg): array
    {
        $defaults = [
            'path'             => rtrim(base_path(env('ACF_PATH', 'src/acf')), '/'),
            'url'              => rtrim(dirname(asset_acf('f')), '/') . '/', // match config
            'json'             => true,
            'save_json'        => storage_path(env('ACF_JSON_SAVE', 'app/private/acf/json')),
            'load_json'        => array_filter(
                array_map('trim', explode(',', env('ACF_JSON_LOAD', ''))),
                static fn ($p) => $p !== ''
            ),
            'capability'       => 'manage_options',
            'show_admin'       => (bool) env('APP_DEBUG', false),
            'autoload'         => false,
            'show_updates'     => false,
            'row_index_offset' => 0,
            'local'            => true,
        ];

        $s = array_replace($defaults, $cfg);

        // Normalize scalar types and shape
        $s['path']             = rtrim((string) $s['path'], '/');
        $s['url']              = rtrim((string) $s['url'], '/');
        $s['capability']       = (string) $s['capability'];
        $s['show_admin']       = (bool) $s['show_admin'];
        $s['autoload']         = (bool) $s['autoload'];
        $s['show_updates']     = (bool) $s['show_updates'];
        $s['row_index_offset'] = (int)  $s['row_index_offset'];
        $s['local']            = (bool) $s['local'];
        $s['json']             = (bool) $s['json'];
        $s['save_json']        = (string) ($s['save_json'] ?: $defaults['save_json']);

        // Ensure load_json at least includes save_json and is unique
        $loads = (array) ($s['load_json'] ?: [$s['save_json']]);
        $s['load_json'] = array_values(
            array_unique(
                array_filter($loads, static fn ($p) => is_string($p) && $p !== '')
            )
        );

        return $s;
    }

    /**
     * Ensure public ACF assets exist at the configured target path.
     *
     * In production, throws if the expected directory is missing.
     *
     * @return string Validated ACF assets directory path.
     *
     * @throws RuntimeException If assets are missing in production.
     */
    protected function ensurePublicAssets(): string
    {
        $dir = asset_acf_path();

        if (! is_dir($dir)) {
            if ($this->isProd()) {
                throw new RuntimeException(sprintf(
                    'ACF assets missing at %s. Run: `composer run copy-acf-assets` to copy %s → %s.',
                    $dir,
                    env('ACF_ASSETS_SRC', 'src/acf/assets'),
                    $dir
                ));
            }

            logger()->warning("[ACF] Public assets directory missing (non-prod): {$dir}");
        }

        return $dir;
    }

    /**
     * Ensure the save_json directory exists and is writable.
     *
     * Non-production: attempts to create and chmod the directory, logging warnings on failure.
     * Production: throws on missing or non-writable directory.
     *
     * @return string Validated save_json directory path.
     *
     * @throws RuntimeException If the directory is missing or not writable in production.
     */
    protected function ensureSaveJsonDirectory(): string
    {
        $dir = rtrim((string) $this->settings['save_json'], "/\\");

        // Ensure directory exists
        if (! is_dir($dir)) {
            if ($this->isProd()) {
                throw new RuntimeException("ACF save_json directory missing: {$dir}");
            }

            if (! @mkdir($dir, 0775, true) && ! is_dir($dir)) {
                logger()->warning("[ACF] Failed to create save_json directory: {$dir}");
            }
        }

        // Ensure directory is writable
        if (is_dir($dir) && ! is_writable($dir)) {
            if (! $this->isProd()) {
                @chmod($dir, 0775);
            }

            clearstatcache(true, $dir);

            if (! is_writable($dir)) {
                $msg = "ACF save_json directory is not writable: {$dir}";

                if ($this->isProd()) {
                    throw new RuntimeException($msg);
                }

                logger()->warning("[ACF] {$msg}");
            }
        }

        return $dir;
    }

    /**
     * Include the ACF core file and assert the ACF class is available.
     *
     * @throws RuntimeException If the file is invalid or the class fails to load.
     */
    protected function includeAcfCore(): void
    {
        $filePath = $this->settings['path'] . '/acf.php';

        $this->validateAcfCore($filePath);

        require_once $filePath;

        if (! class_exists('ACF', false)) {
            throw new RuntimeException("ACF failed to load from {$filePath}");
        }
    }

    /**
     * Strictly validate the ACF entry file.
     *
     * @param  string  $path Absolute path to the expected acf.php file.
     *
     * @throws RuntimeException If the file is missing, unreadable, or misnamed.
     */
    protected function validateAcfCore(string $path): void
    {
        $real = realpath($path);

        if (! $real) {
            throw new RuntimeException("ACF entry file does not exist: {$path}");
        }
        if (! is_file($real)) {
            throw new RuntimeException("ACF entry path is not a file: {$real}");
        }
        if (! is_readable($real)) {
            throw new RuntimeException("ACF entry file is not readable: {$real}");
        }
        if (strtolower(basename($real)) !== 'acf.php') {
            throw new RuntimeException("ACF entry file must be 'acf.php'. Got: " . basename($real));
        }
    }

    /**
     * Register WordPress filters for ACF settings.
     *
     * Safeguards non-WP runtimes (artisan/CLI) by early return.
     */
    protected function registerWordPressFilters(): void
    {
        if (! function_exists('add_filter')) {
            return;
        }

        $s = $this->settings;

        foreach ($this->getFilterMap($s) as $hook => $callback) {
            add_filter($hook, $callback);
        }

        // Use only existing folders for load_json to avoid useless scans
        $existing = $this->existingLoadJsonPaths();

        add_filter('acf/settings/load_json', static function ($paths) use ($existing) {
            $merged = array_merge((array) $paths, $existing);
            return array_values(array_unique(array_filter($merged, static fn($p) => is_dir($p))));
        });
    }

    /**
     * Build the mapping of ACF-related WP filter hooks to their callbacks.
     *
     * @param  array<string, mixed>  $s
     * @return array<string, callable>
     */
    protected function getFilterMap(array $s): array
    {
        return [
            'acf/settings/path'         => static fn () => trailingslashit($s['path']),
            'acf/settings/url'          => static fn () => trailingslashit($s['url']),
            'acf/settings/capability'   => static fn () => $s['capability'],
            'acf/settings/show_admin'   => static fn () => $s['show_admin'],
            'acf/settings/show_updates' => static fn () => $s['show_updates'],
            'row_index_offset'          => static fn () => $s['row_index_offset'],
            'acf/settings/autoload'     => static fn () => $s['autoload'],
            'acf/settings/local'        => static fn () => $s['local'],
            'acf/settings/json'         => static fn () => $s['json'],
            'acf/settings/save_json'    => static fn () => $s['save_json'],
        ];
    }

    /**
     * Register ACF options pages defined in configuration.
     */
    protected function registerOptionPages(): void
    {
        if (! function_exists('acf_add_options_page')) {
            return;
        }

        foreach ((array) config('acf.option_pages', []) as $page) {
            acf_add_options_page($page);
        }
    }

    /**
     * Return only existing, unique load_json directories.
     *
     * Used to keep WP filters fast and avoid scanning non-existent directories.
     *
     * @return array<int, string>
     */
    protected function existingLoadJsonPaths(): array
    {
        $paths = array_values(array_unique((array) ($this->settings['load_json'] ?? [])));

        return array_values(array_filter($paths, static fn($p) => is_string($p) && is_dir($p)));
    }

    /**
     * Detect whether we are currently running inside a WordPress runtime.
     */
    protected function hasWpRuntime(): bool
    {
        return function_exists('add_filter') && function_exists('apply_filters');
    }

    /**
     * Determine if the application is running in production.
     */
    protected function isProd(): bool
    {
        return app()->isProduction();
    }

    /**
     * Register the Helper singleton and its container alias.
     */
    protected function registerHelper(): void
    {
        $this->app->singleton(Helper::class);
        $this->app->alias(Helper::class, 'acf.helper');
    }

    /**
     * Register the ACF service and its alias.
     */
    protected function registerAcf(): void
    {
        $this->app->singleton(ACF::class, static function (Application $app) {
            return new ACF($app, $app->make(Helper::class));
        });

        $this->app->alias(ACF::class, 'acf');
    }
}
