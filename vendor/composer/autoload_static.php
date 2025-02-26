<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit4a783cf0bcea6adc059324c2989350fa
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Stripe\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Stripe\\' => 
        array (
            0 => __DIR__ . '/..' . '/stripe/stripe-php/lib',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit4a783cf0bcea6adc059324c2989350fa::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit4a783cf0bcea6adc059324c2989350fa::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit4a783cf0bcea6adc059324c2989350fa::$classMap;

        }, null, ClassLoader::class);
    }
}
