<?php
/**
 * User: Larry
 * Date: 09.07.12
 * Time: 18:23
 */

//require_once __DIR__.'/autoload.php';
require_once __DIR__.'/vendor/Symfony/Component/ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Tools'                          => __DIR__.'/lib/hypoconf/lib',
    'HypoConf'                       => __DIR__.'/lib/hypoconf/lib',
    'NginxParser'                    => __DIR__.'/lib/hypoconf/lib',
    'Symfony'                        => __DIR__.'/vendor',
    'PEAR2'                          => __DIR__.'/vendor/pear2/lib'
));

$loader->register();

use Tools\LogCLI;

use Symfony\Component\Console as Console;
$application = new Console\Application('NginxParser', '0.0.1');

$application->add(new NginxParser\ConsoleCommands\ParseNginx());
$application->run();