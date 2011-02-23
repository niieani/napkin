<?php
namespace HypoConf\Commands;

use Tools\LogCLI;
use Tools\StringTools;
use Tools\ArrayTools;
//use Tools\Tree;
//use Tools\FileOperation;

//use HypoConf\ConfigParser;
//use HypoConf\ConfigScopes;
use HypoConf\ConfigScopes\ApplicationsDB;

class Helpers
{
    
    public static function SetValueHelper($values)
    {
        return (count($values) === 1) ? $values[0] : $values;
    }
    
    public static function SearchConfigs(&$settings, $settingPath, $iterativeSetting = 0)
    {
        $searchResults = ArrayTools::TraverseTreeWithPath(&$settings, $settingPath);
        if(empty($searchResults))
        {
            LogCLI::MessageResult(LogCLI::YELLOW.'Sorry, no settings found for: '.LogCLI::BLUE.$settingPath.LogCLI::RESET, 0, LogCLI::INFO);
            return false;
        }
        else
        {
            if(count($searchResults['all'])>1) LogCLI::MessageResult(LogCLI::YELLOW.'Multiple settings found for: '.LogCLI::BLUE.$settingPath.LogCLI::RESET, 4, LogCLI::INFO);
            LogCLI::MessageResult(LogCLI::GREEN.'Best match: '.LogCLI::BLUE.$searchResults['best'].LogCLI::RESET, 0, LogCLI::INFO);
            
            $path = ApplicationsDB::FixPath('nginx', $searchResults['best'], $iterativeSetting);
            LogCLI::MessageResult('Fixed path: '.LogCLI::YELLOW.$searchResults['best'].' => '.LogCLI::BLUE.$path.LogCLI::RESET, 6, LogCLI::INFO);
            return $path;
        }
    }
    
    public static function DoWeReplaceHelper(array $chain, $path)
    {
        $testtype = end(StringTools::typeList(reset($chain), '+', false));
        
        if($testtype['exclamation'] !== false)
        {
            return true;
        }
        else return false;
    }
}