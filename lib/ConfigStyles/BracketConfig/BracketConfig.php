<?php

namespace ConfigStyles\BracketConfig;

class BracketConfig
{
    public $params;
    public $scheme;
    public $scope;
    public $isRequired = false;
    public $level = 0;

    public function returnConfig()
    {
        $output = \Tools\StringTools::sprintfn($this->scheme, \Tools\StringTools::makeList($this->params));
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
    
    public static function setAll($data, array $appconfs)
    {
        foreach ($appconfs as $setting)
        {
            //this is dirty, fix me (so many copies of the yaml array!)
            if(is_object($setting))
                $setting->set($data);
        }
    }
    
}