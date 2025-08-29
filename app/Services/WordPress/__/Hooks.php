<?php

namespace App\Services;

/**
 * Class Hooks
 *
 * Thin, explicit, and ergonomic orchestrator for WordPress actions & filters.
 * - Core wrappers: action(), filter(), fire(), apply().
 * - Namespacing: namespace('rooty/editor')->action('enqueue_scripts', ...).
 * - Escape namespace: withoutNamespace()->action('admin_enqueue_scripts', ...).
 * - Guards: adminOnly(), frontendOnly(), whenUserCan(), guardScreen().
 * - Dedup/group: once(), onceOn(), group().
 * - Unregister: removeAction(), removeFilter(), replaceFilter().
 */
class Hooks
{
    /**
     * Registry for "once" to prevent duplicates (hook+callback+priority).
     *
     * @var array<string, true>
     */
    protected array $onceRegistry = [];

    /**
     * In-memory map of registered callbacks for optional later removal.
     *
     * @var array<string, array<int, array<int, callable>>> Format: ['action:hook'|'filter:hook'][priority][] = callback
     */
    protected array $registrations = [];

    /**
     * Optional namespace prefix (e.g. "rooty/editor").
     *
     * @var string|null
     */
    protected ?string $ns = null;

    /**
     * Create a namespaced instance that prefixes all hook names.
     *
     * @param  string  $prefix  e.g. 'rooty/editor'
     * @return static
     */
    public function namespace(string $prefix): static
    {
        $clone = clone $this;
        $clone->ns = trim($prefix, "/ \t\n\r\0\x0B");
        return $clone;
    }

    /**
     * Return a clone that does not apply any namespace.
     *
     * @return static
     */
    public function withoutNamespace(): static
    {
        $clone = clone $this;
        $clone->ns = null;
        return $clone;
    }

    /**
     * Resolve (and prefix) a hook name if this instance is namespaced.
     *
     * @param  string  $hook
     * @return string
     */
    protected function resolveHook(string $hook): string
    {
        if (! $this->ns) {
            return $hook;
        }
        $hook = ltrim($hook, "/");
        return "{$this->ns}/{$hook}";
    }

    /**
     * Register a WordPress action.
     *
     * @param  string   $hook
     * @param  callable $callback
     * @param  int      $priority
     * @param  int      $acceptedArgs
     * @return void
     */
    public function action(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        $resolved = $this->resolveHook($hook);
        add_action($resolved, $callback, $priority, $acceptedArgs);
        $this->registrations["action:{$resolved}"][$priority][] = $callback;
    }

    /**
     * Register a WordPress filter.
     *
     * @param  string   $hook
     * @param  callable $callback
     * @param  int      $priority
     * @param  int      $acceptedArgs
     * @return void
     */
    public function filter(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        $resolved = $this->resolveHook($hook);
        add_filter($resolved, $callback, $priority, $acceptedArgs);
        $this->registrations["filter:{$resolved}"][$priority][] = $callback;
    }

    /**
     * Fire a WordPress action (do_action wrapper).
     *
     * @param  string $hook
     * @param  mixed  ...$args
     * @return void
     */
    public function fire(string $hook, mixed ...$args): void
    {
        do_action($this->resolveHook($hook), ...$args);
    }

    /**
     * Apply WordPress filters (apply_filters wrapper).
     *
     * @param  string $hook
     * @param  mixed  $value
     * @param  mixed  ...$args
     * @return mixed
     */
    public function apply(string $hook, mixed $value, mixed ...$args): mixed
    {
        return apply_filters($this->resolveHook($hook), $value, ...$args);
    }

    /**
     * Run a registrar only once for a given composite key.
     *
     * @param  string   $key
     * @param  callable $registrar
     * @return void
     */
    public function once(string $key, callable $registrar): void
    {
        if (isset($this->onceRegistry[$key])) {
            return;
        }
        $this->onceRegistry[$key] = true;
        $registrar();
    }

    /**
     * Register a callback exactly once on a hook.
     * Deduplication key: (type + resolvedHook + priority + normalizedCallbackId).
     *
     * @param  string   $hook
     * @param  callable $callback
     * @param  int      $priority
     * @param  int      $acceptedArgs
     * @param  bool     $isFilter
     * @return void
     */
    public function onceOn(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1, bool $isFilter = false): void
    {
        $resolvedHook = $this->resolveHook($hook); // only for key stability
        $id = $this->normalizeCallbackId($callback);
        $key = ($isFilter ? 'filter' : 'action') . ":{$resolvedHook}:{$priority}:{$id}";

        if (isset($this->onceRegistry[$key])) {
            return;
        }
        $this->onceRegistry[$key] = true;

        if ($isFilter) {
            $this->filter($hook, $callback, $priority, $acceptedArgs);
        } else {
            $this->action($hook, $callback, $priority, $acceptedArgs);
        }
    }

    /**
     * Wrap a callback so it only runs in wp-admin.
     *
     * @param  callable $callback
     * @return callable
     */
    public function adminOnly(callable $callback): callable
    {
        return function (...$args) use ($callback) {
            if (is_admin()) {
                return $callback(...$args);
            }
            return $args[0] ?? null;
        };
    }

    /**
     * Wrap a callback so it only runs on the frontend.
     *
     * @param  callable $callback
     * @return callable
     */
    public function frontendOnly(callable $callback): callable
    {
        return function (...$args) use ($callback) {
            if (! is_admin()) {
                return $callback(...$args);
            }
            return $args[0] ?? null;
        };
    }

    /**
     * Wrap a callback so it only runs when the current user has the capability.
     *
     * @param  string   $capability
     * @param  callable $callback
     * @return callable
     */
    public function whenUserCan(string $capability, callable $callback): callable
    {
        return function (...$args) use ($capability, $callback) {
            if (current_user_can($capability)) {
                return $callback(...$args);
            }
            return $args[0] ?? null;
        };
    }

    /**
     * DEPRECATED: Prefer guardScreen() which accepts a callback.
     * This returns a pass-through guard for filters (no-op for actions).
     *
     * @param  string        $screenId
     * @param  null|string   $fallbackGet
     * @param  null|string   $expected
     * @return callable
     */
    public function onScreen(string $screenId, ?string $fallbackGet = null, ?string $expected = null): callable
    {
        return function (...$args) use ($screenId, $fallbackGet, $expected) {
            $ok = false;

            if (function_exists('get_current_screen')) {
                $screen = get_current_screen();
                $ok = ($screen && isset($screen->id) && is_string($screen->id) && $screen->id === $screenId);
            }

            if (! $ok && $fallbackGet && $expected) {
                $source = $_GET ?? [];
                $val = isset($source[$fallbackGet]) && is_string($source[$fallbackGet])
                    ? sanitize_text_field($source[$fallbackGet])
                    : null;
                $ok = ($val !== null && strcasecmp($val, $expected) === 0);
            }

            return $args[0] ?? null;
        };
    }

    /**
     * Guard wrapper: run $callback only when on given screen (or GET fallback).
     *
     * @param  string      $screenId   e.g. 'toplevel_page_rooty-editor'
     * @param  callable    $callback
     * @param  null|string $fallbackGet
     * @param  null|string $expected
     * @return callable
     */
    public function guardScreen(string $screenId, callable $callback, ?string $fallbackGet = null, ?string $expected = null): callable
    {
        return function (...$args) use ($screenId, $callback, $fallbackGet, $expected) {
            $ok = false;

            if (function_exists('get_current_screen')) {
                $screen = get_current_screen();
                $ok = ($screen && isset($screen->id) && is_string($screen->id) && $screen->id === $screenId);
            }

            if (! $ok && $fallbackGet && $expected) {
                $source = $_GET ?? [];
                $val = isset($source[$fallbackGet]) && is_string($source[$fallbackGet])
                    ? sanitize_text_field($source[$fallbackGet])
                    : null;
                $ok = ($val !== null && strcasecmp($val, $expected) === 0);
            }

            if ($ok) {
                return $callback(...$args);
            }

            // For filters, return the first arg (value); for actions, no-op.
            return $args[0] ?? null;
        };
    }

    /**
     * Remove a previously registered action (best effort).
     *
     * @param  string   $hook
     * @param  callable $callback
     * @param  int      $priority
     * @return void
     */
    public function removeAction(string $hook, callable $callback, int $priority = 10): void
    {
        $resolved = $this->resolveHook($hook);
        remove_action($resolved, $callback, $priority);
    }

    /**
     * Remove a previously registered filter (best effort).
     *
     * @param  string   $hook
     * @param  callable $callback
     * @param  int      $priority
     * @return void
     */
    public function removeFilter(string $hook, callable $callback, int $priority = 10): void
    {
        $resolved = $this->resolveHook($hook);
        remove_filter($resolved, $callback, $priority);
    }

    /**
     * Replace a filter by removing the old one and adding the new one.
     *
     * @param  string   $hook
     * @param  callable $old
     * @param  callable $new
     * @param  int      $priority
     * @param  int      $acceptedArgs
     * @return void
     */
    public function replaceFilter(string $hook, callable $old, callable $new, int $priority = 10, int $acceptedArgs = 1): void
    {
        $this->removeFilter($hook, $old, $priority);
        $this->filter($hook, $new, $priority, $acceptedArgs);
    }

    /**
     * Register a batch of hooks and return an undo closure.
     *
     * @param  callable $registrar  The function that calls $this->action()/filter() etc.
     * @return callable             Call to attempt unregistering previously added callbacks.
     */
    public function group(callable $registrar): callable
    {
        $snapshotBefore = $this->registrations;

        $registrar($this);

        $snapshotAfter = $this->registrations;
        $delta = $this->diffRegistrations($snapshotBefore, $snapshotAfter);

        return function () use ($delta): void {
            foreach ($delta as $key => $byPriority) {
                [$type, $hook] = explode(':', $key, 2);
                foreach ($byPriority as $priority => $callbacks) {
                    foreach ($callbacks as $cb) {
                        if ($type === 'action') {
                            remove_action($hook, $cb, (int) $priority);
                        } else {
                            remove_filter($hook, $cb, (int) $priority);
                        }
                    }
                }
            }
        };
    }

    /**
     * Normalize a callback into a stable id for deduplication.
     *
     * @param  callable $callback
     * @return string
     */
    protected function normalizeCallbackId(callable $callback): string
    {
        if ($callback instanceof \Closure) {
            return 'closure:' . spl_object_hash($callback);
        }

        if (is_array($callback)) {
            [$objOrClass, $method] = $callback;
            if (is_object($objOrClass)) {
                return 'obj:' . spl_object_hash($objOrClass) . '::' . (string) $method;
            }
            return 'cls:' . (string) $objOrClass . '::' . (string) $method;
        }

        if (is_string($callback)) {
            return 'func:' . $callback;
        }

        if (is_object($callback) && method_exists($callback, '__invoke')) {
            return 'invokable:' . spl_object_hash($callback);
        }

        return 'unknown:' . md5(serialize($callback));
    }

    /**
     * Compute new registrations added between two snapshots.
     *
     * @param  array $before
     * @param  array $after
     * @return array
     */
    protected function diffRegistrations(array $before, array $after): array
    {
        $delta = [];

        foreach ($after as $key => $byPriority) {
            $beforeByPriority = $before[$key] ?? [];
            foreach ($byPriority as $priority => $callbacks) {
                $prev = $beforeByPriority[$priority] ?? [];

                $toId = fn ($cb) => $this->normalizeCallbackId($cb);
                $prevIds = array_map($toId, $prev);

                foreach ($callbacks as $cb) {
                    $id = $toId($cb);
                    if (! in_array($id, $prevIds, true)) {
                        $delta[$key][(int) $priority][] = $cb;
                    }
                }
            }
        }

        return $delta;
    }
}
