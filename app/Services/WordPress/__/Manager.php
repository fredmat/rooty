<?php

namespace App\Services;

use Rooty\Foundation\Application;
use App\Contracts\AbortManagerInterface;

/**
 * Handles general WordPress bootstrapping and configuration for the application.
 *
 * This service is responsible for:
 * - Setting default WordPress-related config values.
 * - Ensuring permalink structure is consistent.
 * - Managing queued cookies via Symfony's response object.
 * - Delegating abort logic to a custom AbortManager.
 */
class Manager
{
    /**
     * Create a new Manager instance.
     *
     * @param  \Rooty\Foundation\Application  $app
     * @param  \App\Contracts\AbortManagerInterface  $abort
     */
    public function __construct(
        protected Application $app,
        protected AbortManagerInterface $abort
    ) {
        $this->setDefaultWpConfig();
    }

    /**
     * Boot the WordPress manager service.
     *
     * Registers necessary actions and filters,
     * and initializes the abort manager.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->abort->boot();

        add_action('after_setup_theme', fn () => $this->ensurePermalinks());
        add_filter('wp_headers', fn (array $headers) => $this->sendQueuedCookies($headers));
    }

    /**
     * Set default WordPress-related config values if not already defined.
     *
     * @return void
     */
    protected function setDefaultWpConfig(): void
    {
        $defaults = [
            'theme_path' => get_theme_file_path(),
            'theme_url'  => get_theme_file_uri(),
        ];

        foreach ($defaults as $key => $value) {
            if (config("wp.$key") === null) {
                config(["wp.$key" => $value]);
            }
        }
    }

    /**
     * Ensure that the permalink structure is set to `/postname/`.
     *
     * This is enforced automatically at theme setup.
     *
     * @return void
     */
    protected function ensurePermalinks(): void
    {
        $expected = '/%postname%/';
        if (get_option('permalink_structure') !== $expected) {
            update_option('permalink_structure', $expected);
            flush_rewrite_rules();
        }
    }

    /**
     * Send queued cookies from the application's response.
     *
     * This integrates Symfony's cookie management with WordPress headers.
     *
     * @param  array<string, string>  $headers
     * @return array<string, string>
     */
    protected function sendQueuedCookies(array $headers): array
    {
        if (
            ! $this->app->bound('cookie') ||
            ! $this->app->bound('response')
        ) {
            return $headers;
        }

        $response  = $this->app['response'];
        $cookieJar = $this->app['cookie'];

        foreach ($cookieJar->getQueuedCookies() as $cookie) {
            $response->headers->setCookie($cookie);
        }

        foreach ($response->headers->getCookies() as $cookie) {
            header('Set-Cookie: ' . $cookie->__toString(), false);
        }

        return $headers;
    }

    /**
     * Abort the request with a given HTTP code, message, and title.
     *
     * @param  int|null  $code    HTTP status code (e.g. 403, 404, 500)
     * @param  string  $message  Optional message to display
     * @param  string  $title    Optional title of the error
     * @param  array<string, mixed>  $args   Additional context or metadata
     * @return never
     */
    public function abort(?int $code = null, string $message = '', string $title = '', array $args = []): never
    {
        $this->abort->abort($code, $message, $title, $args);
    }
}
