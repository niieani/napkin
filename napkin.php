<?php
/**
 * User: NIXin
 * Date: 23.09.2011
 * Time: 00:30
 */
 
require_once __DIR__.'/autoload.php';

use Tools\LogCLI;
//use Tools\Errors;

//use HypoConf\Commands;
use HypoConf\Paths;
use Tools\XFormatterHelper;

use Symfony\Component\Console as Console;

set_error_handler('\Tools\Errors::Handle');

Paths::$root = __DIR__;
Paths::$db = __DIR__.'/database';

LogCLI::SetVerboseLevel(6);

$application = new Console\Application('NAPKIN', '0.9.1');

$application->setHelperSet(new Console\Helper\HelperSet(
                    array(
                        new Console\Helper\FormatterHelper(),
                        new Console\Helper\DialogHelper(),
                        new XFormatterHelper()
                    )));

$application->add(new HypoConf\ConsoleCommands\ListSettings());
$application->add(new HypoConf\ConsoleCommands\LoadSetAndSave());
$application->add(new HypoConf\ConsoleCommands\Generate());
$application->add(new HypoConf\ConsoleCommands\AddSite());
$application->add(new HypoConf\ConsoleCommands\AddUser());
$application->run();

//$shell = new Console\Shell($application);
//$shell->run();