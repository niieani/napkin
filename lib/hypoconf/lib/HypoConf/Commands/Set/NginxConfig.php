<?php
namespace HypoConf\Commands\Set;

use HypoConf\Commands\Helpers;
use Tools\LogCLI;
use Tools\StringTools;
use Tools\ArrayTools;
use Tools\Tree;
//use Tools\FileOperation;

//use HypoConf\ConfigParser;
use HypoConf\ConfigScopes;
use HypoConf\ConfigScopes\ApplicationsDB;

class NginxConfig
{
    public static function LoadAndSave($arguments, $file)
    {
        $settingsNginx = ApplicationsDB::GetSettingsList('nginx');
        
        $value = ArrayTools::dearraizeIfNotRequired($arguments['values']);
        
        //$setting = (Tree::addToTreeSet(StringTools::Delimit($result->command->args['chain'],'.'), $value, 1));
        $chain = StringTools::Delimit($arguments['chain'], '.');
        
        $settingPath = implode('/', $chain);
        
        // are we adding a setting or replacing/merging ? TODO: add check if the setting is iterative at all
        $doNotReplace = Helpers::DoWeReplaceHelper($chain, $settingPath);
        
        if($doNotReplace === true) 
            $settingPath = StringTools::RemoveExclamation($settingPath); //remove the + from the beginning
        
        if($path = Helpers::SearchConfigs(&$settingsNginx, $settingPath, 'defaults'))
        {
            $setting = Tree::addToTreeSet(explode('/', $path), $value, 1);
            
            $nginx = new ConfigScopes\SettingsDB();
            
            // load the original file first
            $nginx->MergeFromYAML($file, false, false, false); //true for compilation
            
            // change the setting
            $nginx->MergeFromArray($setting, false, false);
            
            // save the file with the new setting
            $nginx->ReturnYAML($file);
        }
    }
}