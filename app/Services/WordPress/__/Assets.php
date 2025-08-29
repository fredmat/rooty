<?php

namespace App\Services;

/**
 * Class Assets
 *
 * Lightweight asset registry to collect styles/scripts from custom hooks
 * and print them in the layout (head/footer) with deterministic ordering.
 *
 * Notes:
 * - No dependency resolution (simple last-write-wins on same handle).
 * - Supports inline CSS/JS and basic HTML attributes (boolean or scalar).
 * - Head vs footer scripts are split by the $inFooter flag.
 */
class Assets
{
    /** @var array<string, array{href:string, attrs:array<string, mixed>, inline:string[]}> */
    protected array $styles = [];

    /** @var array<string, array{src:string, attrs:array<string, mixed>, inline_before:string[], inline_after:string[]}> */
    protected array $headScripts = [];

    /** @var array<string, array{src:string, attrs:array<string, mixed>, inline_before:string[], inline_after:string[]}> */
    protected array $footerScripts = [];

    /**
     * Register (or replace) a stylesheet by handle.
     *
     * @param  string               $handle
     * @param  string               $href
     * @param  array<string,mixed>  $attrs
     * @return void
     */
    public function style(string $handle, string $href, array $attrs = []): void
    {
        $this->styles[$handle] = $this->styles[$handle] ?? ['href' => $href, 'attrs' => [], 'inline' => []];
        $this->styles[$handle]['href']  = $href;
        $this->styles[$handle]['attrs'] = $attrs;
    }

    /**
     * Attach inline CSS to a style handle.
     * If the style does not exist yet, it will be created with an empty href.
     *
     * @param  string $handle
     * @param  string $css
     * @return void
     */
    public function inlineStyle(string $handle, string $css): void
    {
        if (! isset($this->styles[$handle])) {
            $this->styles[$handle] = ['href' => '', 'attrs' => [], 'inline' => []];
        }
        $this->styles[$handle]['inline'][] = $css;
    }

    /**
     * Register (or replace) a script by handle.
     *
     * @param  string               $handle
     * @param  string               $src
     * @param  bool                 $inFooter
     * @param  array<string,mixed>  $attrs
     * @return void
     */
    public function script(string $handle, string $src, bool $inFooter = false, array $attrs = []): void
    {
        $bucket = $inFooter ? 'footerScripts' : 'headScripts';

        $this->{$bucket}[$handle] = $this->{$bucket}[$handle]
            ?? ['src' => $src, 'attrs' => [], 'inline_before' => [], 'inline_after' => []];

        $this->{$bucket}[$handle]['src']   = $src;
        $this->{$bucket}[$handle]['attrs'] = $attrs;
    }

    /**
     * Attach inline JS to a script handle.
     *
     * @param  string $handle
     * @param  string $js
     * @param  string $position  'before'|'after'
     * @param  bool   $inFooter
     * @return void
     */
    public function inlineScript(string $handle, string $js, string $position = 'after', bool $inFooter = false): void
    {
        $bucket = $inFooter ? 'footerScripts' : 'headScripts';

        if (! isset($this->{$bucket}[$handle])) {
            $this->{$bucket}[$handle] = ['src' => '', 'attrs' => [], 'inline_before' => [], 'inline_after' => []];
        }

        if ($position === 'before') {
            $this->{$bucket}[$handle]['inline_before'][] = $js;
        } else {
            $this->{$bucket}[$handle]['inline_after'][] = $js;
        }
    }

    /**
     * Print <link> and <style> tags + <script> tags for HEAD.
     *
     * @return void
     */
    public function printHead(): void
    {
        // Styles
        foreach ($this->styles as $handle => $def) {
            $href  = trim($def['href']);
            $attrs = $this->attrs($def['attrs']);

            if ($href !== '') {
                echo "<link rel=\"stylesheet\" href=\"{$this->e($href)}\"{$attrs} />\n";
            }

            if (! empty($def['inline'])) {
                echo "<style>\n" . implode("\n", $def['inline']) . "\n</style>\n";
            }
        }

        // Head scripts (with inline before/after)
        foreach ($this->headScripts as $handle => $def) {
            foreach ($def['inline_before'] as $js) {
                echo "<script>\n{$js}\n</script>\n";
            }

            $src   = trim($def['src']);
            $attrs = $this->attrs($def['attrs']);
            if ($src !== '') {
                echo "<script src=\"{$this->e($src)}\"{$attrs}></script>\n";
            }

            foreach ($def['inline_after'] as $js) {
                echo "<script>\n{$js}\n</script>\n";
            }
        }
    }

    /**
     * Print <script> tags for FOOTER.
     *
     * @return void
     */
    public function printFooter(): void
    {
        foreach ($this->footerScripts as $handle => $def) {
            foreach ($def['inline_before'] as $js) {
                echo "<script>\n{$js}\n</script>\n";
            }

            $src   = trim($def['src']);
            $attrs = $this->attrs($def['attrs']);
            if ($src !== '') {
                echo "<script src=\"{$this->e($src)}\"{$attrs}></script>\n";
            }

            foreach ($def['inline_after'] as $js) {
                echo "<script>\n{$js}\n</script>\n";
            }
        }
    }

    /**
     * Reset all queues (useful between requests in long-running contexts).
     *
     * @return void
     */
    public function reset(): void
    {
        $this->styles       = [];
        $this->headScripts  = [];
        $this->footerScripts= [];
    }

    /**
     * Render HTML attributes. Boolean TRUE renders as valueless attribute.
     *
     * @param  array<string,mixed> $attrs
     * @return string
     */
    protected function attrs(array $attrs): string
    {
        if (empty($attrs)) {
            return '';
        }

        $fragments = [];
        foreach ($attrs as $key => $val) {
            if (is_bool($val)) {
                if ($val === true) {
                    $fragments[] = $this->e($key);
                }
                continue;
            }
            $fragments[] = $this->e($key) . '="' . $this->e((string) $val) . '"';
        }

        return $fragments ? ' ' . implode(' ', $fragments) : '';
    }

    /**
     * Basic HTML escaper.
     *
     * @param  string $value
     * @return string
     */
    protected function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
