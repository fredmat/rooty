<?php

namespace App\Services;

/**
 * Class BodyClasses
 *
 * Orchestrates <body> class composition using WordPress filters.
 * - Agnostic: does not depend on domain-specific services.
 * - Stateless: no internal storage; only orchestrates filters and normalization.
 * - Flexible: works with any custom hook name (front, admin, or custom).
 */
class BodyClasses
{
    /**
     * Register a body-classes filter for a given hook.
     *
     * The callback receives the current classes array and must return an array.
     * Normalization/uniqueness is handled by compute(), not here.
     *
     * @param  string   $hook      The filter hook name (e.g. 'rooty/editor/body_classes').
     * @param  callable $callback  function (array $classes): array
     * @param  int      $priority  Filter priority (default: 10).
     * @return void
     */
    public function addFilter(string $hook, callable $callback, int $priority = 10): void
    {
        add_filter($hook, $callback, $priority, 1);
    }

    /**
     * Compute the final list of <body> classes from a given hook.
     *
     * Optionally merges admin_body_class() output for compatibility with WP admin.
     *
     * @param  string  $hook        The filter hook to apply.
     * @param  bool    $mergeAdmin  Merge classes from admin_body_class() result.
     * @return array<int, string>
     */
    public function compute(string $hook, bool $mergeAdmin = false): array
    {
        $classes = apply_filters($hook, []);

        if ($mergeAdmin) {
            $admin = trim((string) apply_filters('admin_body_class', ''));
            if ($admin !== '') {
                $classes = array_merge($classes, preg_split('/\s+/', $admin) ?: []);
            }
        }

        return $this->normalize($classes);
    }

    /**
     * Normalize body classes: cast, sanitize, unique, remove empties.
     *
     * @param  array<int|string, mixed>  $classes
     * @return array<int, string>
     */
    public function normalize(array $classes): array
    {
        // Cast to string
        $classes = array_map('strval', $classes);

        // Sanitize each class using WP helper
        $classes = array_map(static fn (string $c): string => sanitize_html_class($c), $classes);

        // Remove empties and duplicates, reindex numerically
        $classes = array_filter($classes, static fn (string $c): bool => $c !== '');
        $classes = array_values(array_unique($classes));

        return $classes;
    }

    /**
     * Helper: append multiple classes (variadic) to an array by reference.
     *
     * @param  array<int, string> $classes
     * @param  string             ...$items
     * @return void
     */
    public function push(array &$classes, string ...$items): void
    {
        foreach ($items as $item) {
            if ($item !== '') {
                $classes[] = $item;
            }
        }
    }

    /**
     * Helper: append a class if the condition is true.
     *
     * @param  bool               $condition
     * @param  array<int, string> $classes
     * @param  string             $class
     * @return void
     */
    public function pushIf(bool $condition, array &$classes, string $class): void
    {
        if ($condition && $class !== '') {
            $classes[] = $class;
        }
    }

    /**
     * Helper: build a sanitized prefixed class (e.g. "page-{slug}").
     *
     * @param  string  $prefix
     * @param  string  $value
     * @return string
     */
    public function prefixed(string $prefix, string $value): string
    {
        return rtrim($prefix, '-') . '-' . sanitize_key($value);
    }
}
