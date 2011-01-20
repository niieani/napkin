<?php

require_once __DIR__.'/vendor/symfony/lib/Symfony/Component/HttpFoundation/UniversalClassLoader.php';

use Symfony\Component\HttpFoundation\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony'                        => __DIR__.'/vendor/symfony/lib',
    'PwFisher'                       => __DIR__.'/vendor/pwfisher/lib',
));

/*
$loader->registerPrefixes(array(
    'Swift_' => __DIR__.'/vendor/swiftmailer/lib/classes',
    'Twig_'  => __DIR__.'/vendor/twig/lib',
));
*/

$loader->register();
