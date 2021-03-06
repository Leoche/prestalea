<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit79756245ccdd7cfc8d5053bb6493c7fe
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInit79756245ccdd7cfc8d5053bb6493c7fe', 'loadClassLoader'), true, false);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInit79756245ccdd7cfc8d5053bb6493c7fe', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInit79756245ccdd7cfc8d5053bb6493c7fe::getInitializer($loader));

        $loader->setClassMapAuthoritative(true);
        $loader->register(false);

        $includeFiles = \Composer\Autoload\ComposerStaticInit79756245ccdd7cfc8d5053bb6493c7fe::$files;
        foreach ($includeFiles as $fileIdentifier => $file) {
            composerRequire79756245ccdd7cfc8d5053bb6493c7fe($fileIdentifier, $file);
        }

        return $loader;
    }
}

/**
 * @param string $fileIdentifier
 * @param string $file
 * @return void
 */
function composerRequire79756245ccdd7cfc8d5053bb6493c7fe($fileIdentifier, $file)
{
    if (empty($GLOBALS['__composer_autoload_files'][$fileIdentifier])) {
        $GLOBALS['__composer_autoload_files'][$fileIdentifier] = true;

        require $file;
    }
}
