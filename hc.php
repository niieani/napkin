<?php
require_once __DIR__.'/autoload.php';

use Tools\LogCLI;
use Tools\StringTools;
use Tools\ArrayTools;
use Tools\Tree;
use Tools\FileOperation;

//only for cores
use Tools\System;

//use ConfigParser\ConfigParser;
//use ConfigScopes\ConfigScopes;
//use ConfigScopes\SettingsDB;

use HypoConf\Commands;
use HypoConf\ConfigParser;
use HypoConf\ConfigScopes;
use HypoConf\Paths;
//use HypoConf;
//use HypoConf\ConfigScopes\ApplicationsDB;
//use ConfigScopes\TemplatesDB;
use PEAR2\Console\CommandLine;

Paths::$root = __DIR__;
Paths::$db = __DIR__.'/database';

//use Applications\Nginx;
//use ConfigStyles\BracketConfig\NginxConfig;
//use ConfigStyles\BracketConfig\NginxScope;

//define('HC_DIR', __DIR__);

// create the parser
$parser = new CommandLine(array(
    'name'        => 'HypoConf',
    'description' => 'A configuration manager for nginx, PHP with PHP-FPM and MySQL with a command line interface',
    'version'     => '0.4 alpha',
    'add_help_option' => FALSE,
    'add_version_option' => TRUE
));

// add a global option to make the program verbose
$parser->addOption('verbose', array(
    'short_name'  => '-v',
    'long_name'   => '--verbose',
    'action'      => 'StoreInt',
    'default'     => 1,
    'description' => 'set verbose level output (-1 quiet, 5 debug level)'
));

$parser->addOption('stdout', array(
    'short_name'  => '-s',
    'long_name'   => '--stdout',
    'action'      => 'StoreTrue',
    'default'     => false,
    'description' => 'turn on output to console instead of writing files'
));

$parser->addOption('debug', array(
    'short_name'  => '-d',
    'long_name'   => '--debug',
    'action'      => 'StoreTrue',
    'default'     => false,
    'description' => 'turn on debugging'
));

$parser->addOption('force', array(
    'short_name'  => '-f',
    'long_name'   => '--force',
    'action'      => 'StoreTrue',
    'default'     => false,
    'description' => 'force a specific action without asking for confirmation'
));


$cmd = array();

// add the foo subcommand
$cmd['enable'] = $parser->addCommand('enable', array(
    'description' => 'output the given string with a foo prefix',
    'aliases' => array('dis', 'd')
));
$cmd['enable']->addArgument('enable', array(
    'description' => 'the text to output'
));

// add the bar subcommand
$cmd['disable'] = $parser->addCommand('disable', array(
    'description' => 'output the given string with a bar prefix',
    'aliases' => array('en', 'e')
));
$cmd['disable']->addArgument('disable', array(
    'description' => 'the text to output'
));
$cmd['disable']->addOption('placeholder', array(
    'short_name'  => '-p',
    'long_name'   => '--placeholder',
    'help_name'   => '/home/overquota.html'
));


// add the set subcommand
$cmd['set'] = $parser->addCommand('set', array(
    'description' => 'Sets the parameter of a website, user or template to specified value(s).',
    'aliases'     => array('s', 'setting')
));
$cmd['set']->addArgument('name', array(
    'description' => 'site, user (when prefixed with @) or template (when prefixed with +)',
));
$cmd['set']->addArgument('chain', array(
    'description' => 'configuration chain (eg. nginx.php)',
));
$cmd['set']->addArgument('values', array(
    'description' => 'value(s) to set',
    'multiple'    => true
));

$cmd['help'] = $parser->addCommand('help', array(
    'description' => 'shows general help or if help [argument] specified displays more about a certain function',
    'aliases'     => array('h')
));
$cmd['help']->addArgument('setting', array(
    'description' => 'displays detailed help for the specified setting',
    'optional'    => true
));

// add the unset subcommand
$cmd['unset'] = $parser->addCommand('unset', array(
    'description' => 'unsets a given setting',
    'aliases'     => array('us')
));
$cmd['unset']->addArgument('name', array(
    'description' => 'user or site',
));
$cmd['unset']->addArgument('chain', array(
    'description' => 'chains',
));
$cmd['unset']->addArgument('value', array(
    'description' => 'optional value to unset (if not specified will unset the chain)',
    'multiple'    => true,
    'optional'    => true
));

//add
$cmd['add'] = $parser->addCommand('add', array(
    'description' => 'output the given string with a bar prefix',
    'aliases' => array('a')
));
$cmd['add']->addArgument('name', array(
    'description' => 'the text to output'
));
$cmd['add']->addArgument('name2', array(
    'description' => 'the text to output',
    'optional' => TRUE
));
$cmd['add']->addArgument('template', array(
    'description' => 'the text to output',
    'optional' => TRUE
));

//gen
$cmd['generate'] = $parser->addCommand('generate', array(
    'description' => 'generate a config file from the provided .yml file(s) (order of files is important)',
    'aliases' => array('gen', 'g')
));
$cmd['generate']->addArgument('file', array(
    'description' => 'path(s) to file(s) in parsing order',
    'multiple' => TRUE
));

//reload
$cmd['reload'] = $parser->addCommand('reload', array(
    'description' => 'output the given string with a bar prefix',
    'aliases' => array('r', 'load', 'activate')
));

//move
$cmd['move'] = $parser->addCommand('move', array(
    'description' => 'output the given string with a bar prefix',
    'aliases' => array('mv')
));
$cmd['move']->addArgument('site', array(
    'description' => 'the text to output'
));
$cmd['move']->addArgument('username', array(
    'description' => 'the text to output',
    'optional' => TRUE
));

//drop
$cmd['drop'] = $parser->addCommand('drop', array(
    'description' => 'output the given string with a bar prefix',
    'aliases' => array('dropdb')
));
$cmd['drop']->addArgument('database', array(
    'description' => 'database to be dropped'
));

//rename
$cmd['rename'] = $parser->addCommand('rename', array(
    'description' => 'renames a website or a user',
    'aliases' => array('ren')
));
$cmd['rename']->addArgument('oldname', array(
    'description' => 'current name of website or username'
));
$cmd['rename']->addArgument('newname', array(
    'description' => 'the desired name'
));

//remove
$cmd['remove'] = $parser->addCommand('remove', array(
    'description' => 'output the given string with a bar prefix',
    'aliases' => array('rm')
));
$cmd['remove']->addArgument('name', array(
    'description' => 'the text to output'
));

//remove
$cmd['debug'] = $parser->addCommand('debug', array(
    'description' => 'output the given string with a bar prefix'
));


// run the parser
try {
    $result = $parser->parse();
    if ($result->options)
    {
        //if ($result->options['verbose'] === true) LogCLI::SetVerboseLevel(2);
        LogCLI::SetVerboseLevel($result->options['verbose']);
        //else LogCLI::SetVerboseLevel(1);
    }
    LogCLI::Message(LogCLI::GREEN.'Running the HypoConf engine...'.LogCLI::RESET, 1);
    if ($result->command_name)
    {
        switch($result->command_name)
        {
            case 'debug':
                
                break;
                
            case 'help':
                displayHelp($result->command->args['setting']);
                break;
                
            case 'set':
                if($result->command->args)
                {
                    Commands::LoadAndSave($result->command->args);
                }
                break;
            
            case 'generate':
                if($result->command->args)
                {
                    Commands::GenerateParsed($result->command->args);
                }
                
                break;
            
            case 'add':
                if($result->command->args)
                {
                    Commands::Add($result->command->args);
                }
                break;
            
            default:
                displayHelp('_NotImplemented');
        }
    }
    else displayHelp();
    
    LogCLI::Result(LogCLI::INFO);
    
} catch (Exception $exc) {
    $parser->displayError($exc->getMessage());
}

function displayHelp($setting = false)
{
    echo 'HypoConf Manual'.PHP_EOL;
    switch($setting)
    {
        case '_NotImplemented':
            echo 'Sorry, that function has not been implemented yet!'.PHP_EOL;
            break;
        case '_NoArgs':
            echo 'You haven\'t provided enough arguments!'.PHP_EOL;
            break;
        case '_Unknown':
            echo 'Unknown function!'.PHP_EOL;
            break;
        case 'set':
            echo <<< 'EOT'

set - sets the parameter of a website, user or template to specified value(s).
If a sub-setting is unique then you can use it as a setting, for example:
- setting port is same as nginx.port
- setting php is same as php.support
- setting a database will only ADD a database to the list, 
  to drop a database use 'hc dropdb DBNAME'

Examples:
  hc set default template mytemplate
  hc set default port 80
  hc set @myuser +port 81 (will add another port without removing previous)
  hc set default nginx.port 80 81
  hc set site.com php yes
  hc set default chroot yes
  hc set default nginx.favicon-fix no
  hc set default,site.com chroot,ssl yes
  hc set default,site.com +hostname site2.com
  hc set +mytemplate php.display_errors yes
  hc set site.com database somedb
  hc set site.com access /phpmyadmin deny=all allow=localhost,192.168.0.1
  hc set site.com,@myuser,+mytemplate +listing /files
  hc set site.com +access '^/files$' deny

EOT;
            break;
            
        default:
            echo <<< 'EOT'

A configuration manager for nginx, PHP with PHP-FPM and MySQL 
with a Command Line Interface

Usage:
  hc [options]
  hc [options] <command> [options] [args]

Options:
  -v, --verbose  turn on verbose output
  -s, --stdout   turn on output to console instead of writing files
  -!, --debug    turn on debugging
  -f, --force    force a specific action without asking for confirmation
  -h, --help     show this help message and exit
  --version      show the program version and exit

Commands:
  enable    output the given string with a foo prefix (aliases: dis, d)
  disable   output the given string with a bar prefix (aliases: en, e)
  set       Sets the parameter of a website, user or template to specified
            value(s). (aliases: s, setting)
  help      shows general help or if help [argument] specified displays
            more about a certain function (alias: h)
  unset     unsets a given setting (alias: us)
  add       output the given string with a bar prefix (alias: a)
  generate  output the given string with a bar prefix (aliases: gen, g)
  reload    output the given string with a bar prefix (aliases: r, load,
            activate)
  move      output the given string with a bar prefix (alias: mv)
  drop      output the given string with a bar prefix (alias: dropdb)
  rename    renames a website or a user (alias: ren)
  remove    output the given string with a bar prefix (alias: rm)

EOT;
    }
}

?>
