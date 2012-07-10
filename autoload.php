<?php

require_once __DIR__.'/vendor/Symfony/Component/ClassLoader/UniversalClassLoader.php';

//use Symfony\Component\HttpFoundation\UniversalClassLoader;
use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();

$loader->registerNamespaces(array(
    'Tools'                          => __DIR__.'/lib/hypoconf/lib',
    'HypoConf'                       => __DIR__.'/lib/hypoconf/lib',
//    'Symfony'                        => __DIR__.'/vendor/symfony/lib',
    'Symfony'                        => __DIR__.'/vendor',
    'PEAR2'                          => __DIR__.'/vendor/pear2/lib'
));

$loader->register();
