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
    public static function SearchConfigs(&$settings, $settingPath, $application, $basicScope = false)
    {
        // TODO: problem with this function
        //var_dump($settings).PHP_EOL.var_dump($settingPath).PHP_EOL;

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

            //$path = ApplicationsDB::FixPath('nginx', $searchResults['best'], $iterativeSetting);
            $path = $searchResults['best'];

            if($basicScope !== false)
            {
                if(($pos = strpos($path, $basicScope)) !== false && $pos === 0)
                    $path = StringTools::DropLastBit($path, -1);
//                    $path = substr_replace($path, $basicScope.'/', 0, strlen($basePath));
            }

            $parent = ArrayTools::accessArrayElementByPath(&$settings, StringTools::DropLastBit($searchResults['best']));
            if(isset($parent['iterative']))
                $path = StringTools::DropLastBit($path, -1);

            LogCLI::MessageResult('Fixed path: '.LogCLI::YELLOW.$searchResults['best'].' => '.LogCLI::BLUE.$path.LogCLI::RESET, 6, LogCLI::INFO);
            return $path;
        }
    }
    /*
    public static function SearchConfigs(&$settings, $settingPath, $iterativeSetting = 0)
    {
        // TODO: problem with this function
        var_dump($settings).PHP_EOL.var_dump($settingPath).PHP_EOL;

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
    */
    public static function DoWeReplaceHelper(array $chain) //, $path
    {
        $testtype = end(StringTools::TypeList(reset($chain), '+', false));
        
        if($testtype['exclamation'] !== false)
        {
            return true;
        }
        else return false;
    }
}