<?php

namespace ConfigStyles\GlobalConfig;
use \Tools\StringTools;
use \Tools\LogCLI;

class GlobalConfig
{
    public $params;

    public function returnConfig()
    {
        return $this->params;
        //return array('output' => implode($this->params, ' '));
        //else return array('output' => null);
    }

    public function set(array $params)
    {
        //var_dump($params);
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

    public function __construct(array $params = NULL)
    {
        ($params === NULL) ? : $this->set($params);
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