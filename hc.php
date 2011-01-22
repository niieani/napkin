#!/usr/bin/env php
<?php
// Include the Console_CommandLine package.
require_once 'Console/CommandLine.php';
require_once 'Console/CommandLine/Action.php';

class ActionList extends Console_CommandLine_Action
{
    public function execute($value=false, $params=array())
    {
        $list = explode(',', str_replace(' ', '', $value));
        /*if (count($range) != 2) {
            throw new Exception(sprintf(
                'Option "%s" must be 2 integers separated by a comma',
                $this->option->name
             ));
        }*/
        $this->setResult($list);
    }
}

function delimit($value, $delimited = ',')
{
	return explode($delimited, str_replace(' ', '', $value));
}

function addToTree($arrayin)
{
	$arrayin = array_reverse($arrayin);
	$tree = array();
	for ($i = 0; $i < count($arrayin); $i++)
	{
                $last = $arrayin[$i];
                $tree = array($arrayin[$i] => $tree);
	}
	return $tree;
}

function addToTreeSet($arrayin, $values)
{
        $arrayin = array_reverse($arrayin);
        $tree = array();
	$all = count($arrayin);
        for ($i = 0; $i < $all; $i++)
        {
                $last = $arrayin[$i];
		($i == 0) ?
                $tree = array($arrayin[$i] => $values) :
		$tree = array($arrayin[$i] => $tree);
        }
        return $tree;
}


//class ActionTypeList extends Console_CommandLine_Action
//{
    function typeList($value=false, $sign = '@', $delimit = ',')
    {
	$list = delimit($value, $delimit);
	$info = array();
	foreach ($list as $k => $v)
	{
//	if(strstr($v, $sign))
//	{
		$pos = strpos($v, $sign);
		if ($pos === 0)
		{
			$info[$k]['exclamation'] = true;
			$info[$k]['text'] = substr($v, 1);
			//$info[$k]['text'] = strstr_after($v, '!');
		}
//	}
		else
		{
			$info[$k]['exclamation'] = false;
			$info[$k]['text'] = $v;
		}
	}
        return ($info);
    }
//}

        function strstr_after($haystack, $needle) {
            $pos = strpos($haystack, $needle);
            if (is_int($pos)) {
                return substr($haystack, $pos + strlen($needle));
            }
            // Most likely false or null
            return $pos;
        }


// then we can register our action
Console_CommandLine::registerAction('List', 'ActionList');
//Console_CommandLine::registerAction('TypeList', 'ActionTypeList');

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
    'description' => 'turn on verbose output'
));

$parser->addOption('stdout', array(
    'short_name'  => '-s',
    'long_name'   => '--stdout',
    'action'      => 'StoreTrue',
    'description' => 'turn on output to console instead of writing files'
));

$parser->addOption('debug', array(
    'short_name'  => '-!',
    'long_name'   => '--debug',
    'action'      => 'StoreTrue',
    'description' => 'turn on debugging'
));

$parser->addOption('force', array(
    'short_name'  => '-f',
    'long_name'   => '--force',
    'action'      => 'StoreTrue',
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
    'description' => 'Sets the parameter of a website, user or template to specified value(s).
If a sub-setting is unique then you can use it as a setting, for example:
- setting port is same as nginx.port
- setting php is same as php.support
- setting a database will only ADD a database to the list, 
  to drop a database use \'hc dropdb DBNAME\'

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
  hc set site.com +access \'^/files$\' deny',
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
    'description' => 'output the given string with a bar prefix',
    'aliases' => array('gen', 'g')
));
$cmd['generate']->addArgument('file', array(
    'description' => 'the text to output',
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
    if ($result->command_name) {
/*        $st = $result->command->options['reverse'] 
            ? strrev($result->command->args['text'])
            : $result->command->args['text'];
        if ($result->command_name == 'enable') { 
            echo "Foo says: $st\n";
        } else if ($result->command_name == 'disable') {
            echo "Bar says: $st\n";
        }*/
//	$t = $result->command->args['text2'];
	var_dump(typeList($result->command->args['name']));
	var_dump(addToTreeSet(delimit($result->command->args['chain'],'.'),$result->command->args['values']));
//	var_dump($result->command->args['value']);
/*	foreach (delimit($result->command->args['chain'],'.') as $chain)
	{
		var_dump($chain);
	}*/
//	var_dump(delimit($result->command->args['chain']),'.');
    }
} catch (Exception $exc) {
    $parser->displayError($exc->getMessage());
}

?>
