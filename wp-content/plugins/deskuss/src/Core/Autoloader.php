<?php

namespace Deskuss\Core;

class Autoloader
{
    private static $prefix = 'Deskuss\\';
    private static $baseDir;

    public static function register()
    {
        self::$baseDir = DESKUSS_DIR . '/src/';
        spl_autoload_register([__CLASS__, 'load']);
    }

    public static function load($class)
    {
        $prefix = self::$prefix;
        $len = strlen($prefix);

        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }

        $relativeClass = substr($class, $len);
        $file = self::$baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    }
}