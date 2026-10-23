<?php
declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $prefix = 'Papstar\\PimMiddleware\\ETL\\';
    $baseDir = __DIR__ . '/ETL/';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));

    $file = $baseDir
        . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass)
        . '.php';

    if (is_file($file)) {
        require $file;
    }
});
