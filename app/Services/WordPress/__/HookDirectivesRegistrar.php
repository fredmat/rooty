<?php

namespace App\Services;

use Illuminate\View\Compilers\BladeCompiler;
use App\Services\HookDirective;

/**
 * Class HookDirectivesRegistrar
 *
 * Registers Blade directives related to Rooty/WordPress hooks.
 * Lives under the WordPress service namespace to keep concerns co-located.
 */
class HookDirectivesRegistrar
{
    /**
     * Register all custom directives on the given Blade compiler.
     *
     * @param  \Illuminate\View\Compilers\BladeCompiler  $blade
     * @return void
     */
    public static function register(BladeCompiler $blade): void
    {
        $fqcn = '\\' . HookDirective::class;

        // @hook('fire', 'ns/hook', ...), @hook('apply', 'ns/filter', $value, ...)
        $blade->directive('hook', function (string $expression) use ($fqcn) {
            return "<?php {$fqcn}::dispatch({$expression}); ?>";
        });

        // @hookIf($cond, 'fire', 'ns/hook', ...)
        $blade->directive('hookIf', function (string $expression) use ($fqcn) {
            return "<?php {$fqcn}::dispatchIf({$expression}); ?>";
        });

        // @hookUnless($cond, 'fire', 'ns/hook', ...)
        $blade->directive('hookUnless', function (string $expression) use ($fqcn) {
            return "<?php {$fqcn}::dispatchUnless({$expression}); ?>";
        });
    }
}
