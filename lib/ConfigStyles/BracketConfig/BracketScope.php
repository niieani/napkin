<?php

namespace ConfigStyles\BracketConfig;

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
    
    public static function addAllStems(array $appconfs, $scope)
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
    
}
