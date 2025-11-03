<?php
// Lightweight bootstrap: prefer Composer autoload if available, otherwise register a simple PSR-4 autoloader
$composer = __DIR__ . '/../vendor/autoload.php';
if (file_exists($composer)) {
    require_once $composer;
} else {
    // Register a minimal PSR-4 autoloader for the App\ namespace pointing to src/
    spl_autoload_register(function ($class) {
        $prefix = 'App\\';
        $base_dir = __DIR__ . '/';

        // only handle classes in the App\ namespace
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }

        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        if (file_exists($file)) {
            require $file;
        }
    });
}

// optional: set a constant to indicate composer availability
if (!defined('COMPOSER_AUTOLOADED')) {
    define('COMPOSER_AUTOLOADED', file_exists($composer));
}
