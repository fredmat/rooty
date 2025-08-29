#!/usr/bin/env php
<?php
declare(strict_types=1);

// Rooty setup runner: execute all project setup scripts sequentially

$root = dirname(__DIR__);

// List of scripts to run (relative to /scripts)
$scripts = [
    'publish-acf-assets.php',
    // ajouter d'autres scripts ici si besoin
];

foreach ($scripts as $script) {
    $path = "{$root}/scripts/{$script}";
    if (!is_file($path)) {
        fwrite(STDERR, "[WARN] Script not found: {$path}\n");
        continue;
    }

    echo "[INFO] Running {$script}...\n";
    $exitCode = 0;
    passthru(PHP_BINARY . ' ' . escapeshellarg($path), $exitCode);
    if ($exitCode !== 0) {
        fwrite(STDERR, "[ERROR] {$script} failed (exit code {$exitCode})\n");
        exit($exitCode);
    }
}

echo "[OK] Rooty setup completed successfully\n";
exit(0);
