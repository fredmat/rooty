#!/usr/bin/env php
<?php
// Copy ACF assets from ./src/acf/assets to ./public/acf/assets
// Simple, portable, always-copy approach (no symlinks).

declare(strict_types=1);

use Dotenv\Dotenv;

function fail(string $msg, int $code = 1): void {
    fwrite(STDERR, "[ACF] {$msg}\n");
    exit($code);
}

function rrmdir(string $path): void {
    if (!file_exists($path)) return;
    if (is_file($path) || is_link($path)) { @unlink($path); return; }
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($it as $item) {
        $p = $item->getPathname();
        $item->isDir() ? @rmdir($p) : @unlink($p);
    }
    @rmdir($path);
}

function rcopy(string $src, string $dst): void {
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($it as $item) {
        $target = $dst . DIRECTORY_SEPARATOR . $it->getSubPathName();
        if ($item->isDir()) {
            if (!is_dir($target) && !mkdir($target, 0755, true) && !is_dir($target)) {
                fail("Failed to create dir: {$target}");
            }
        } else {
            $parent = dirname($target);
            if (!is_dir($parent) && !mkdir($parent, 0755, true) && !is_dir($parent)) {
                fail("Failed to create dir: {$parent}");
            }
            if (!copy($item->getPathname(), $target)) {
                fail("Failed to copy file: {$target}");
            }
        }
    }
}

// --- New: load Composer autoload + .env via vlucas/phpdotenv ---
$root = getcwd(); // keep original behavior (composer runs from project root)
$autoload = $root . '/vendor/autoload.php';
if (!is_file($autoload)) {
    fail("Composer autoload not found at {$autoload}. Run `composer install`.");
}
require_once $autoload;

// Load .env (supports quotes and ${VAR} expansion)
Dotenv::createImmutable($root)->safeLoad();

// Read env from $_ENV first (Dotenv default), then getenv() as fallback
$PUBLIC_DIR      = $_ENV['PUBLIC_DIR']         ?? getenv('PUBLIC_DIR')         ?: 'public';
$BUILD_SUBDIR    = $_ENV['BUILD_SUBDIR']       ?? getenv('BUILD_SUBDIR')       ?: 'build';
$ACF_ASSETS_SRC  = $_ENV['ACF_ASSETS_SRC']     ?? getenv('ACF_ASSETS_SRC')     ?: 'src/acf/assets';
$ACF_SUBPATH     = $_ENV['ACF_ASSETS_SUBPATH'] ?? getenv('ACF_ASSETS_SUBPATH') ?: 'acf/assets';
$TARGET          = strtolower(($_ENV['ACF_ASSETS_TARGET'] ?? getenv('ACF_ASSETS_TARGET') ?: 'public')); // 'public' | 'build'

$src = $root . '/' . ltrim($ACF_ASSETS_SRC, '/');
switch ($TARGET) {
    case 'build':
        $dst = $root . '/' . trim($PUBLIC_DIR, '/') . '/' . trim($BUILD_SUBDIR, '/') . '/' . trim($ACF_SUBPATH, '/');
        break;

    case 'public':
        $dst = $root . '/' . trim($PUBLIC_DIR, '/') . '/' . trim($ACF_SUBPATH, '/');
        break;

    default:
        // Custom subdir under PUBLIC_DIR
        $dst = $root . '/' . trim($PUBLIC_DIR, '/') . '/' . trim($TARGET, '/') . '/' . trim($ACF_SUBPATH, '/');
        break;
}

if (!is_dir($src)) {
    fail("Source assets not found: {$src}");
}

// Ensure base exists (e.g. /public/acf or /public/build/acf)
$dstBase = dirname($dst);
if (!is_dir($dstBase) && !mkdir($dstBase, 0755, true) && !is_dir($dstBase)) {
    fail("Unable to create public base dir: {$dstBase}");
}

// Clean target then copy fresh
rrmdir($dst);
if (!mkdir($dst, 0755, true) && !is_dir($dst)) {
    fail("Unable to create target dir: {$dst}");
}

rcopy($src, $dst);
echo "[ACF] Assets copied to {$dst}\n";
exit(0);
