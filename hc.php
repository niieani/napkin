#!/usr/bin/env php
<?php
// Include the Console_CommandLine package.
require_once 'Console/CommandLine.php';
require_once 'Console/CommandLine/Action.php';
require_once __DIR__.'/autoload.php';

use Symfony\Component\Yaml\Yaml;
//use Symfony\Component\Yaml\Dumper;
use Tools\LogCLI;
use Tools\StringTools;
use Tools\Tree;
use Tools\FileOperation;
use ConfigStyles\BracketConfig\NginxConfig;
use ConfigStyles\BracketConfig\NginxScope;

/*
class ActionList extends Console_CommandLine_Action
{
    public function execute($value=false, $params=array())
    {
        $list = explode(',', str_replace(' ', '', $value));
        //if (count($range) != 2) {
        //    throw new Exception(sprintf(
        //        'Option "%s" must be 2 integers separated by a comma',
        //        $this->option->name
        //     ));
        //}
        $this->setResult($list);
    }
}

// then we can register our action
Console_CommandLine::registerAction('List', 'ActionList');
//Console_CommandLine::registerAction('TypeList', 'ActionTypeList');
*/

// create the parser
$parser = new Console_CommandLine(array(
    'name'        => 'hc',
    'description' => 'A configuration manager for nginx, PHP with PHP-FPM and MySQL with a command line interface',
    'version'     => '0.0.5',
    'add_help_option' => TRUE,
    'add_version_option' => TRUE
));

// add a global option to make the program verbose
$parser->addOption('verbose', array(
    'short_name'  => '-v',
    'long_name'   => '--verbose',
    'action'      => 'StoreTrue',
    'default'     => false,
    'description' => 'turn on verbose output'
));

$parser->addOption('stdout', array(
    'short_name'  => '-s',
    'long_name'   => '--stdout',
    'action'      => 'StoreTrue',
    'default'     => false,
    'description' => 'turn on output to console instead of writing files'
));

$parser->addOption('debug', array(
    'short_name'  => '-!',
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


// run the parser
try {
    $result = $parser->parse();
    if ($result->options)
    {
        if ($result->options['verbose'] === true) define('VERBOSE', true);
    }
    if ($result->command_name) 
    {
        switch($result->command_name)
        {
            case 'help':
                displayHelp($result->command->args['setting']);
                break;
                
            case 'set':
                if($result->command->args)
                {
                    var_dump(StringTools::typeList($result->command->args['name']));
                    var_dump(Tree::addToTreeSet(StringTools::delimit($result->command->args['chain'],'.'),$result->command->args['values']));
                }
                else displayHelp('_NoArgs');
                break;
            
            case 'generate':
                $confScope = new NginxScope;
                
                $nginx['sites']['listen'] = new NginxConfig('listen %(port)s %(options)s', 'server', 1);
                $nginx['sites']['domain'] = new NginxConfig('server_name %(domain)s', 'server', 1);
                $nginx['pid'] = new NginxConfig('pid %(pid)s');
                
                $config = YAML::load('defaults.yml');
                NginxConfig::setAll(&$config['nginx'], &$nginx);
                NginxScope::addAllStems(&$nginx, &$confScope);
                
                if($result->command->args)
                {
                    foreach($result->command->args['file'] as $file)
                    {
                        LogCLI::Message('Loading file: '.$file);
                        if (file_exists($file))
                        {
                            $config = YAML::load($file);
                            LogCLI::Result('OK');
                        } else {
                            LogCLI::Result('FAILED');
                            LogCLI::Message("No such file: $file", 'FATAL');
                        }
                        // FIX ME
                	}
                    
                    if (isset($config['nginx']['sites']))
                    {
                        foreach ($config['nginx']['sites'] as $key => $site)
                        {
                        	$siteScope[$key] = new NginxScope;
                        	NginxConfig::setAll($site, $nginx['sites']);
                        	NginxScope::addAllStems(&$nginx['sites'], $siteScope[$key]);
                        }
                    }
                }
                
                if (isset($siteScope))
                {
                    foreach ($siteScope as $scope)
                    {
                    	$confScope->addStem(array('scope' => 'http', 'output' => $scope->returnScopes(), 'level' => 1));
                    }
                }
                
                echo $confScope->returnScopes();
                
                break;
            
            default:
                displayHelp('_NotImplemented');
        }
    }
    else displayHelp();

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

/*        $st = $result->command->options['reverse'] 
            ? strrev($result->command->args['text'])
            : $result->command->args['text'];
        if ($result->command_name == 'enable') { 
            echo "Foo says: $st\n";
        } else if ($result->command_name == 'disable') {
            echo "Bar says: $st\n";
        }*/
//	$t = $result->command->args['text2'];
//	var_dump($result->command->args['value']);
/*	foreach (delimit($result->command->args['chain'],'.') as $chain)
	{
		var_dump($chain);
	}*/
//	var_dump(delimit($result->command->args['chain']),'.');


?>
