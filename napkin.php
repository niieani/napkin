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

use Symfony\Component\Console as Console;

set_error_handler('\Tools\Errors::Handle');

Paths::$root = __DIR__;
Paths::$db = __DIR__.'/database';

LogCLI::SetVerboseLevel(6);

$application = new Console\Application('NAPCIN', '0.9.0');
$application->add(new HypoConf\ConsoleCommands\ListSettings('list'));
$application->add(new HypoConf\ConsoleCommands\LoadSetAndSave('set'));
$application->add(new HypoConf\ConsoleCommands\LoadSetAndSaveTest('settest'));
$application->add(new HypoConf\ConsoleCommands\Generate('gen1'));
$application->run();

//$shell = new Console\Shell($application);
//$shell->run();