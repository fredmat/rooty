<?php

namespace App\Services;

use InvalidArgumentException;

/**
 * Class HookDirective
 *
 * Blade directive helper to trigger WordPress hooks through the Hooks service.
 * Modes are explicit and validated: 'fire' | 'apply'.
 *
 * Supported Blade forms:
 *  - @hook('fire', 'ns/hook', $arg1, ...)
 *  - @hook('fire:ns/hook', $arg1, ...)
 *  - @hook('apply', 'ns/filter', $value, $arg1, ...)
 *  - @hook('apply:ns/filter', $value, $arg1, ...)
 *
 * Conditional:
 *  - @hookIf($cond, 'fire', 'ns/hook', ...)
 *  - @hookIf($cond, 'apply', 'ns/filter', $value, ...)
 *  - @hookIf($cond, 'fire:ns/hook', ...)
 *  - @hookIf($cond, 'apply:ns/filter', $value, ...)
 */
class HookDirective
{
    /**
     * Dispatch the hook call (mode required).
     *
     * @param  mixed ...$args
     * @return void
     */
    public static function dispatch(mixed ...$args): void
    {
        self::doDispatch(...$args);
    }

    /**
     * Dispatch the hook call only when $condition is truthy.
     *
     * @param  mixed $condition
     * @param  mixed ...$args
     * @return void
     */
    public static function dispatchIf(mixed $condition, mixed ...$args): void
    {
        if (! $condition) {
            return;
        }
        self::doDispatch(...$args);
    }

    /**
     * Dispatch the hook call only when $condition is falsy.
     *
     * @param  mixed $condition
     * @param  mixed ...$args
     * @return void
     */
    public static function dispatchUnless(mixed $condition, mixed ...$args): void
    {
        if ($condition) {
            return;
        }
        self::doDispatch(...$args);
    }

    /**
     * Internal dispatcher with validation.
     *
     * @param  mixed ...$args
     * @return void
     *
     * @throws \InvalidArgumentException When invalid usage is detected in debug mode.
     */
    protected static function doDispatch(mixed ...$args): void
    {
        /** @var \App\Services\Hooks $hooks */
        $hooks = app(Hooks::class);

        if (empty($args)) {
            self::fail("Missing arguments. Expected @hook('mode', 'hook', ...) or @hook('mode:hook', ...).");
            return;
        }

        $mode = null;
        $hook = null;
        $rest = [];

        // If the first arg looks like "mode:hook", parse it regardless of payload presence
        if (is_string($args[0]) && str_contains($args[0], ':')) {
            [$mode, $hook] = explode(':', (string) $args[0], 2);
            $mode = strtolower(trim($mode));
            $hook = trim($hook);
            $rest = array_slice($args, 1);
        } else {
            // Long form: mode, hook, ...
            $mode = strtolower(trim((string) ($args[0] ?? '')));
            $hook = isset($args[1]) ? trim((string) $args[1]) : '';
            $rest = array_slice($args, 2);
        }

        if (! in_array($mode, ['fire', 'apply'], true)) {
            self::fail("Invalid mode '{$mode}'. Allowed: 'fire' or 'apply'.");
            return;
        }

        if ($hook === '') {
            self::fail('Missing hook name.');
            return;
        }

        if ($mode === 'apply') {
            $value = $rest[0] ?? null;
            $extra = array_slice($rest, 1);

            // Printing the filtered value so the directive outputs content.
            echo $hooks->apply($hook, $value, ...$extra);
            return;
        }

        // fire
        $hooks->fire($hook, ...$rest);
    }

    /**
     * Fail helper: throw in debug, no-op in production.
     *
     * @param  string  $message
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected static function fail(string $message): void
    {
        $debug =
            function_exists('config') ? (bool) config('app.debug', false) : false;

        // Optional fallback to custom Application method if you really need it:
        // if (! $debug && function_exists('app') && method_exists(app(), 'debugMode')) {
        //     $debug = (bool) app()->debugMode();
        // }

        if ($debug) {
            throw new InvalidArgumentException('[@hook] ' . $message);
        }
        // Silent no-op in production.
    }
}
