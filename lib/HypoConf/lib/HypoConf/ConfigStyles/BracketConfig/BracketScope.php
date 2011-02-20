<?php

namespace ConfigStyles\BracketConfig;

class BracketScope
{
    private $config = array();

    public function addStem($stem)
    {
        if(isset($stem['output']))
        {
            $scope = &$stem['scope'];
            $level = &$stem['level'];
            (isset($stem['append'])) ? $append = &$stem['append'] : $append = null;
            
            if(!isset($this->config[$scope])) $this->config[$scope] = '';
            
            if(is_array($stem['output']))
            {
                foreach($stem['output'] as $output)
                {
                    $this->config[$scope] .= self::AddTabsToLevel($output.$append, $level);
                }
            }
            
            elseif(strlen($stem['output'])>0)
            {
                $output = &$stem['output'];
                //if (!isset($this->config[$scope])) $this->config[$scope] = null; //this shouldn't happen
                
                $this->config[$scope] .= self::AddTabsToLevel($output.$append, $level);
            }
        }
    }
    
    private static function AddTabsToLevel($string, $level)
    {
        $output = '';
        foreach(preg_split("/(\r?\n)/", $string) as $line)
        {
            for ($i = 0; $i < $level; $i++)
            {
                $output .= "\t";
            }
            $output .= $line.PHP_EOL;
        }
        return $output;
    }
    
    public function orderScopes(array $orderedScopes)
    {
        $ordered = array();
        foreach ($orderedScopes as $scopeName)
        {
            if(isset($this->config[$scopeName])) $ordered[$scopeName] = $this->config[$scopeName];
        }
        //merging in case somebody forgot to list all scopes
        $this->config = array_merge($ordered, $this->config);
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
    
    public static function addAllStems(array $appconfs, BracketScope $scope)
    {
        if(is_object( $scope ))
        {
            foreach ($appconfs as $setting)
            {
                //echo "setting: ";
                //var_dump($setting);
                if(is_object($setting))
                        $scope->addStem($setting->returnConfig());
            }
        }
        else throw new \Exception("Scope is not an object");
    }
    
}
