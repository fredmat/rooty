<?php

namespace App\Services\Http;

use Illuminate\Support\Facades\View;
use App\Contracts\AbortManagerInterface;

/**
 * Handles custom error responses and WordPress aborts.
 *
 * This manager overrides the default `wp_die` behavior and renders
 * custom error views with optional configuration-based messages and status codes.
 */
class AbortManager implements AbortManagerInterface
{
    /**
     * Register custom WordPress error hooks.
     *
     * @return void
     */
    public function boot(): void
    {
        add_filter('wp_die_handler', fn () => $this->customHandler());
        // add_filter('wp_php_error_message', fn (string $message, array $args) => $this->customPHPErrorMessage($message, $args), 10, 2);
    }

    /**
     * Format the PHP fatal error message for display in `wp_php_error_message`.
     *
     * @param  string  $message  The original error message.
     * @param  array<string, mixed>  $args  The error arguments provided by WordPress.
     * @return string
     */
    protected function customPHPErrorMessage(string $message, array $args): string
    {
        return '<p>...</p>';
    }

    /**
     * Abort execution and render a custom error response.
     *
     * Falls back to configured defaults if no message or title is provided.
     *
     * @param  int|null  $code
     * @param  string  $message
     * @param  string  $title
     * @param  array<string, mixed>  $args
     * @return never
     */
    public function abort(?int $code = null, string $message = '', string $title = '', array $args = []): never
    {
        $code ??= config('wp.abort.default_response_code') ?? 500;
        $defaults = $this->resolveDefaults($code, $message, $title);

        wp_die(
            $defaults['message'],
            $defaults['title'],
            array_merge([
                'response'   => $code,
                'show_title' => false,
            ], $args)
        );
    }

    /**
     * Resolve default message and title based on HTTP status code.
     *
     * @param  int  $code
     * @param  string  $message
     * @param  string  $title
     * @return array{message: string, title: string}
     */
    protected function resolveDefaults(int $code, string $message, string $title): array
    {
        $config = config('wp.abort.responses', []);
        $defaults = $config[$code] ?? [];

        return [
            'message' => $message ?: ($defaults['message'] ?? sprintf(__('An error occurred (HTTP %d).', 'rooty'), $code)),
            'title'   => $title ?: ($defaults['title'] ?? sprintf(__('Error %d', 'rooty'), $code)),
        ];
    }

    /**
     * Return a custom `wp_die_handler` callable that renders the error view.
     *
     * @return callable
     */
    protected function customHandler(): callable
    {
        return function ($message, $title = '', $args = []) {
            $args = $this->normalizeArgs($args);

            if (! headers_sent()) {
                header('Content-Type: text/html; charset=' . $args['charset']);
                status_header($args['response']);
                nocache_headers();
            }

            echo View::make('http-error', [
                'message' => $this->formatMessage($message, $args),
                'title'   => $title,
                'args'    => $args,
            ])->render();

            exit;
        };
    }

    /**
     * Normalize `wp_die` arguments and apply defaults.
     *
     * @param  array<string, mixed>  $args
     * @return array<string, mixed>
     */
    protected function normalizeArgs(array $args): array
    {
        return wp_parse_args($args, [
            'response'          => 500,
            'charset'           => get_option('blog_charset') ?: 'utf-8',
            'text_direction'    => is_rtl() ? 'rtl' : 'ltr',
            'additional_errors' => [],
        ]);
    }

    /**
     * Format the error message(s) into an HTML unordered list.
     *
     * If multiple messages or additional errors exist, they will be merged and rendered together.
     *
     * @param  string|array<int, string>  $message
     * @param  array<string, mixed>  $args
     * @return string
     */
    protected function formatMessage(string|array $message, array $args): string
    {
        $messages = is_array($message) ? $message : [$message];

        if (! empty($args['additional_errors'])) {
            $messages = array_merge(
                $messages,
                wp_list_pluck($args['additional_errors'], 'message')
            );
        }

        return "<ul>\n\t\t<li>" . implode("</li>\n\t\t<li>", array_filter($messages)) . "</li>\n\t</ul>";
    }
}
