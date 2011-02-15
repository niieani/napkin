<?php

namespace ConfigStyles\BracketConfig;
use \Tools\StringTools;
use \Tools\LogCLI;

class BracketConfig
{
    public $params;
    public $scheme;
    public $scope;
    public $isRequired = false;
    public $level = 0;

    public function returnConfig()
    {
        //var_dump($this->params);
        $output = StringTools::sprintfn($this->scheme, StringTools::makeList($this->params));
        if($output)
        return array(
            'output' => rtrim($output).';', 
            'scope' => $this->scope, 
            'level' => $this->level
            );
        else return array('output' => null);
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
            //if(!(is_array($this->params[$setting])))
            $this->params[$setting] = $value;
            LogCLI::MessageResult("Setting $setting", 6, LogCLI::INFO);
            // this is currently setting too many settings than required
            // which is not optimal, but works
        }
    }
    
    public function reset()
    {
        $this->params = array();
    }

    public function __construct($scheme = NULL, $scope = NULL, $level = NULL, array $params = NULL)
    {
        ($scheme === NULL) ? : $this->addScheme($scheme);
        ($scope === NULL) ? $this->setScope('_ROOT') : $this->setScope($scope);
        ($params === NULL) ? : $this->set($params);
        ($level === NULL) ? $this->level = 0 : $this->level = $level;
    }
    
    public static function setAll($data, array $appconfs)
    {
        foreach ($appconfs as $setting)
        {
            //var_dump($setting);
            //this is dirty, fix me (so many copies of the yaml array!)
            if(is_object($setting))
                $setting->set($data);
        }
    }
    
    public static function resetAll(array $appconfs)
    {
        foreach ($appconfs as $setting)
        {
            if(is_object($setting))
                $setting->reset();
        }
    }
    
}