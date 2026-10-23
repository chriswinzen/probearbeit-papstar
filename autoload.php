<?php
declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $file = __DIR__ . '/ETL/' . $class . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});
