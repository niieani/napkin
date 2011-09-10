<?php
namespace HypoConf\Commands\Set;

use HypoConf\Commands\Helpers;
//use Tools\LogCLI;
use Tools\StringTools;
//use Tools\ArrayTools;
use Tools\Tree;
//use Tools\FileOperation;

//use HypoConf\ConfigParser;
use HypoConf\ConfigScopes;
use HypoConf\ConfigScopes\ApplicationsDB;

class Site
{
    public static function LoadAndSave($arguments, $file)
    {
        $settings = ApplicationsDB::GetSettingsList('nginx', 'server');
        //LogCLI::MessageResult('Listing available settings.', 6, LogCLI::INFO);
        
        $value = Helpers::SetValueHelper($arguments['values']);
        
        $chain = StringTools::delimit($arguments['chain'], '.');
        
        $settingPath = implode('/', $chain);
        
        // are we adding a setting or replacing/merging ? TODO: add check if the setting is iterative at all
        $doNotReplace = Helpers::DoWeReplaceHelper($chain, $settingPath);
        
        if($doNotReplace === true) 
            $settingPath = StringTools::RemoveExclamation($settingPath); //remove the + from the beginning
        
        if($path = Helpers::SearchConfigs(&$settings, $settingPath, 0))
        {
            $nginx = new ConfigScopes\SettingsDB();
            
            // load the original file first
            $nginx->MergeFromYAML($file, false, false, false); //true for compilation
            
            if($doNotReplace === true)
            { //adding without removing
                // 1. cut the last part and store it $lastbit
                $lastbit = StringTools::ReturnLastBit($path);
                $secondlast = StringTools::ReturnLastBit($path, 2);
                //var_dump($secondlast);
                if($secondlast !== false && is_numeric($secondlast))
                {
                    $path = StringTools::DropLastBit($path, 2); //droping the fixed 0 and the last key
                    
                    // 2. arraize $value
                    $setting = array($lastbit => $value);
                }
                else
                {
                    $setting = $value;
                }
                
                // 3. add value
                $nginx->MergeOneIterativeByPath($path, $setting);
            }
            else
            {
                // make the tree
                $setting = Tree::addToTreeSet(explode('/', $path), $value, 1);
                
                // add/replace the setting
                $nginx->MergeFromArray($setting, false, false);
            }
            // save the file with the new setting
            $nginx->ReturnYAML($file);
        }
    }
}