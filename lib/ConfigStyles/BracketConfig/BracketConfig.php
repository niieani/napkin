<?php

namespace ConfigStyles\BracketConfig;
use \Tools\StringTools;
use \Tools\LogCLI;
use \Tools\System;

class BracketConfig
{
    private $params;
    private $scheme;
    private $scope = '_ROOT';
    private $isRequired = false;
    private $level = 0;
    private $allSettings = array();
    private $isIterative = false;

    public function returnConfig()
    {
        $config = array(
            'output' => null, 
            'scope' => $this->scope, 
            'level' => $this->level,
            'append' => ';'
        );
        
        $output = '';
        if ($this->isIterative === true)
        {
            foreach ($this->params as $element)
            {
                if(is_array($element))
                    foreach ($element as $param)
                    {
                        if(is_array($param))
                        //var_dump($param);
                        $config['output'][] = rtrim(StringTools::sprintfn($this->scheme, StringTools::makeList($param)));
                    }
            }
        }
        else
        {
            $config['output'] = rtrim(StringTools::sprintfn($this->scheme, StringTools::makeList($this->params)));
        }
        return $config;
        
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
        if(!empty($this->allSettings))
            $this->allSettings = System::MergeArrays($this->allSettings, $params);
        else $this->allSettings = $params;
        //var_dump($params);
        foreach($params as $setting => $value)
        {
            if(is_array($value))
            {
                if(count($value) > 1)
                {
                    LogCLI::MessageResult('Setting: '.LogCLI::GREEN.$setting.LogCLI::RESET.' = '.LogCLI::YELLOW.System::Dump($value, '  Subsetting: ').LogCLI::RESET, 5, LogCLI::INFO);
                    /*
                    foreach($value as $subvalue)
                    {
                        
                        //LogCLI::MessageResult('Setting (multi): '.LogCLI::GREEN.$setting.LogCLI::RESET.' = '.LogCLI::YELLOW.$subvalue.LogCLI::RESET, 5, LogCLI::INFO);
                    }
                    */
                    //if(!(is_array($this->params[$setting])))
                    
                    $this->params[$setting] = $value;
                    // this is currently setting too many settings than required
                    // which is not optimal, but works
                }
                else
                {
                    LogCLI::MessageResult('Setting (single from array): '.LogCLI::GREEN.$setting.LogCLI::RESET.' = '.end($value).LogCLI::RESET, 5, LogCLI::INFO);
                    $this->params[$setting] = end($value);
                }
            }
            else
            {
                LogCLI::MessageResult('Setting (single): '.LogCLI::GREEN.$setting.LogCLI::RESET.' = '.LogCLI::YELLOW.$value.LogCLI::RESET, 5, LogCLI::INFO);
                $this->params[$setting] = $value;
            }
        }
    }
    
    public function returnAll()
    {
        return $this->allSettings;
    }
    
    public function reset()
    {
        $this->params = array();
    }

    public function __construct($scheme = NULL, $scope = NULL, $level = NULL, $isIterative = false, array $params = NULL)
    {
        ($scheme === NULL) ? : $this->addScheme($scheme);
        ($scope === NULL) ? $this->setScope('_ROOT') : $this->setScope($scope);
        ($level === NULL) ? $this->level = 0 : $this->level = $level;
        $this->isIterative = $isIterative;
        ($params === NULL) ? : $this->set($params);
    }
    
    public static function setAll($data, array $appconfs)
    {
        foreach ($appconfs as $setting)
        {
            //var_dump($setting);
            //this is dirty, fix me (so many copies of the yaml array!)
            
            if(is_array($setting) && isset($setting['_definition']) && is_object($setting['_definition']))
            {
                LogCLI::MessageResult('Setting according to _definition!', 6, LogCLI::INFO);
                $setting['_definition']->set($data);
            }
            elseif(is_object($setting))
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