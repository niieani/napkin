<?php

//require_once __DIR__.'/vendor/symfony/lib/Symfony/Component/HttpFoundation/UniversalClassLoader.php';
require_once __DIR__.'/vendor/symfony/lib/Symfony/Component/ClassLoader/UniversalClassLoader.php';

//use Symfony\Component\HttpFoundation\UniversalClassLoader;
use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();

$loader->registerNamespaces(array(
    'Tools'                          => __DIR__.'/lib/hypoconf/lib',
//    'ConfigStyles'                   => __DIR__.'/lib/hypoconf/lib/HypoConf',
//    'ConfigParser'                   => __DIR__.'/lib/hypoconf/lib/HypoConf',
//    'ConfigScopes'                   => __DIR__.'/lib/hypoconf/lib/HypoConf',
//    'Applications'                   => __DIR__.'/lib/hypoconf/lib/HypoConf',
    'HypoConf'                       => __DIR__.'/lib/hypoconf/lib',
    'Symfony'                        => __DIR__.'/vendor/symfony/lib',
    'PEAR2'                          => __DIR__.'/vendor/pear2/lib'
));

/*
$loader->registerPrefixes(array(
    'Swift_' => __DIR__.'/vendor/swiftmailer/lib/classes',
    'Twig_'  => __DIR__.'/vendor/twig/lib',
));
*/

$loader->register();
