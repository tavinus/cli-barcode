<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitf17787d2ecff231b84857fe0d96cbca5
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Picqer\\Barcode\\' => 15,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Picqer\\Barcode\\' => 
        array (
            0 => __DIR__ . '/..' . '/picqer/php-barcode-generator/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'U' => 
        array (
            'Ulrichsg\\' => 
            array (
                0 => __DIR__ . '/..' . '/ulrichsg/getopt-php/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitf17787d2ecff231b84857fe0d96cbca5::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitf17787d2ecff231b84857fe0d96cbca5::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInitf17787d2ecff231b84857fe0d96cbca5::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
