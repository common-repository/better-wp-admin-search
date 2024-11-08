<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitae55caf3a4e4c526caed7e89cdfd9bb5
{
    public static $classMap = array (
        'BWPAS\\BWPAS_Api' => __DIR__ . '/../..' . '/includes/class-bwpas-api.php',
        'BWPAS\\BWPAS_Main' => __DIR__ . '/../..' . '/includes/class-bwpas-main.php',
        'BWPAS\\BWPAS_Result' => __DIR__ . '/../..' . '/includes/class-bwpas-result.php',
        'BWPAS\\BWPAS_Search' => __DIR__ . '/../..' . '/includes/class-bwpas-search.php',
        'BWPAS\\Utils\\BWPAS_Helper' => __DIR__ . '/../..' . '/includes/utils/class-bwpas-helper.php',
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInitae55caf3a4e4c526caed7e89cdfd9bb5::$classMap;

        }, null, ClassLoader::class);
    }
}
