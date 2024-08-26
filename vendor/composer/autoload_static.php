<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit5eeba4b7d38d1217ff0fd39ec20da240
{
    public static $prefixLengthsPsr4 = array (
        'V' => 
        array (
            'Venom\\SystemSettings\\' => 21,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Venom\\SystemSettings\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit5eeba4b7d38d1217ff0fd39ec20da240::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit5eeba4b7d38d1217ff0fd39ec20da240::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit5eeba4b7d38d1217ff0fd39ec20da240::$classMap;

        }, null, ClassLoader::class);
    }
}
