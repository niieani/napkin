<?php
require_once __DIR__.'/autoload.php';

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Dumper;

require_once('tests/sprintfn.php');

class BracketConfig
{
    public $params;
    public $scheme;
    public $scope;
    public $isRequired = false;
    public $level = 0;

    public function returnConfig()
    {
        $output = sprintfn($this->scheme, makeList($this->params));
	    return array(
            'output' => rtrim($output).';', 
            'scope' => $this->scope, 
            'level' => $this->level
            );
    }

    public function addScheme($scheme)
    {
        $this->scheme = $scheme;
    }

    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    public function set(array $params)
    {
        foreach($params as $setting => $value)
        {
            $this->params[$setting] = $value;
            // this is currently setting too many settings than required
            // which is not optimal, but works
        }
    }

    public function __construct($scheme = NULL, $scope = NULL, $level = NULL, array $params = NULL)
    {
        ($scheme === NULL) ? : $this->addScheme($scheme);
        ($scope === NULL) ? $this->setScope('_ROOT') : $this->setScope($scope);
        ($params === NULL) ? : $this->set($params);
        ($level === NULL) ? $this->level = 0 : $this->level = $level;
    }
}

class BracketScope
{
    private $config = array();

    public function addStem(array $stem)
    {
        $scope = $stem['scope'];
        $output = $stem['output'];
        if (!isset($this->config[$scope])) $this->config[$scope] = NULL; //this shouldn't happen
        foreach(preg_split("/(\r?\n)/", $output) as $line)
        {
            for ($i = 0; $i < $stem['level']; $i++)
            {
                $this->config[$scope] .= "\t";
            }
        	$this->config[$scope] .= $line.PHP_EOL;
        }
    }

    public function returnScopes()
    {
    	$file = NULL;
    	foreach($this->config as $scope => $content)
    	{
    		($scope == '_ROOT') ? ($file .= $content.PHP_EOL) : ($file .= $scope.PHP_EOL.'{'.PHP_EOL.$content.'}'.PHP_EOL);
    	}
    	return $file;
    }
}


class NginxConfig extends BracketConfig
{
}

class NginxScope extends BracketScope
{
}


$confScope = new NginxScope;

$nginx['sites']['listen'] = new NginxConfig('listen %(port)s %(options)s', 'server', 1);
$nginx['sites']['domain'] = new NginxConfig('server_name %(domain)s', 'server', 1);
$nginx['pid'] = new NginxConfig('pid %(pid)s');

$config = YAML::load('defaults.yml');
setAll(&$config['nginx'], &$nginx);
addAllStems(&$nginx, &$confScope);

if(isset($argv[1]))
{
	$config = YAML::load($argv[1]);
	foreach ($config['nginx']['sites'] as $key => $site)
	{
		$siteScope[$key] = new NginxScope;
		setAll($site, $nginx['sites']);
		addAllStems(&$nginx['sites'], $siteScope[$key]);
	}
}

foreach ($siteScope as $scope)
{
	$confScope->addStem(array('scope' => 'http', 'output' => $scope->returnScopes(), 'level' => 1));
}

echo $confScope->returnScopes();



function setAll($data, array $appconfs)
{
	foreach ($appconfs as $setting)
	{
        //this is dirty, fix me (so many copies of the yaml array!)
        if(is_object($setting))
            $setting->set($data);
	}
}

function addAllStems(array $appconfs, $scope)
{
    if(is_object( $scope ))
    {
        foreach ($appconfs as $setting)
        {
        	if(is_object($setting))
                    $scope->addStem($setting->returnConfig());
        }
    }
    else throw new Exception("scope is not an object.");
}

function getCPUs()
{
//  echo "Detecting number of CPU cores: ";
    return system("cat /proc/cpuinfo | grep \"core id\" | sort | uniq | wc -l");
}

function toFile($array, $file="tmp.yml")
{
        $dumper = new Dumper();
        $yaml = $dumper->dump($array, 3);
        file_put_contents(__DIR__.'/'.$file, $yaml);
}

function getAllYaml($path='.')
{
	$Directory = new RecursiveDirectoryIterator($path);
	$Iterator = new RecursiveIteratorIterator($Directory);
	$Regex = new RegexIterator($Iterator, '/^.+\.yml$/i', RecursiveRegexIterator::GET_MATCH);
	$Yamls = array();

	foreach ($Regex as $File)
	{
	        $Yamls[] = $File[0];
	}
	sort($Yamls, SORT_LOCALE_STRING);
	return $Yamls;
}

function makeList($args, $delimiter = ' ')
{
    foreach($args as $k => $list)
    {
        if (is_array($list))
        {
             $args[$k] = implode($delimiter, $list);
        }
    }
    return $args;
}

?>
