<?php

namespace HypoConf;

use Tools\LogCLI;
use Tools\StringTools;

use Tools\ArrayTools;
use Tools\Tree;
use Tools\FileOperation;

//only for cores
//use Tools\System;

use HypoConf\Paths;
use HypoConf\ConfigParser;
use HypoConf\ConfigScopes;
use HypoConf\ConfigScopes\ApplicationsDB;

class Commands
{
    public static function ListSettings($arguments)
    {
        LogCLI::Message('Listing available settings: ', 0);
        $configScopesNginx = ApplicationsDB::LoadApplication('nginx');
        //$settings = ApplicationsDB::GetSettingsList('nginx', 'server');
        $settingsNginx = ApplicationsDB::GetAllSettings('nginx');
        $settings = ArrayTools::GetMultiDimentionalElementsWithChildren(&$settingsNginx);
        foreach($settings as $setting)
        {
            //var_dump($setting);
            LogCLI::MessageResult(LogCLI::BLUE.$setting, 0, LogCLI::INFO);
        }
        LogCLI::Result(LogCLI::OK);
    }

    public static function LoadAndSave($arguments)
    {
        //self::$ApplicationsDB = new ConfigScopes\ApplicationsDB();
        $configScopesNginx = ApplicationsDB::LoadApplication('nginx');
        
        /*
         * There needs to be a check wheter the 'set' operation is a reference
         * to a custom action, or just a normal setting.
         */
        
        foreach(StringTools::typeList($arguments['name']) as $argument)
        {
            if ($argument['exclamation'] !== false) 
                {
                    echo 'Exclamation: '.$argument['exclamation'];
                }
            else 
            {
                /**
                 * If it's a main config edit than allow all the options (Commands\Set\NginxConfig)
                 * TODO: Can we merge these two classes?
                 */
                if($argument['text'] == 'config')
                {
                    $file = Paths::$db.Paths::$separator.Paths::$hypoconf.Paths::$separator.Paths::$defaultUser.Paths::$separator.'config.yml';
                    Commands\Set\NginxConfig::LoadAndSave($arguments, $file);
                }
                /*
                 * If it's only a site edit, allow only what's in the 'server' scope (Commands\Set\Site)
                 */
                else // opening a site or default site
                {
                    //$siteYML = array_search(strtolower($argument['text']),array_map('strtolower',$files));
                    //var_dump($files);
                    
                    $siteYML = self::GetFullPath($argument['text']);
                    
                    if($siteYML !== false)
                        Commands\Set\Site::LoadAndSave($arguments, $siteYML);
                    else
                    {
                        LogCLI::Fail('Sorry, no site by name: '.$argument['text']);
                    }
                }
                
                //var_dump($setting);
                
                /*
                    VALIDATION GOES HERE, TRAVERSING THE TREE ALSO
                */
                //System::getCPUs();
                //System::getCPUcores();
                /*
                   add notification if modified the file or added a new option (important)
                */
            }
        }
    }
    
    public static function GenerateParsed($arguments)
    {
        $configScopesNginx = ApplicationsDB::LoadApplication('nginx');
        
        foreach($arguments['file'] as $dir)
        {
            $fileslist = FileOperation::getAllFilesByExtension(Paths::$db.Paths::$separator.Paths::$defaultGroup.Paths::$separator.$dir, 'yml');
            //$pathinfo = array();
            //$siteYML = false;
            foreach($fileslist as $file)
            {
                $files[] = $file;
            }
        }
        //var_dump($files);
        $nginx = new ConfigScopes\SettingsDB();
        $nginx->MergeFromYAML(Paths::$db.Paths::$separator.Paths::$hypoconf.Paths::$separator.Paths::$defaultUser.Paths::$separator.'config.yml', false, true, true); //true for compilation
        $nginx->MergeFromYAMLs($files, 'server', true, true); //true for compilation
        //$nginx->MergeFromYAMLs($files, 'server', true, true); //true for compilation
        
        
        ApplicationsDB::LoadConfig(&$nginx->DB);
        
        $parsedFile = $configScopesNginx->parseTemplateRecursively('nginx');
        echo PHP_EOL.$parsedFile;
    }
    
    public static function Add($arguments)
    {
        foreach(StringTools::typeList($arguments['name'], '@') as $argument)
        {
            $name = $argument['text'];
            
            if ($argument['exclamation'] !== false) 
            {
                LogCLI::MessageResult('Exclamation: '.$argument['exclamation'], 2, LogCLI::INFO);
                
                $username = $name;
                LogCLI::Message('Adding user: '.$username, 0);
                $group = (isset($arguments['name2'])) ? $arguments['name2'] : Paths::$defaultGroup;
                $structure = Paths::$db.Paths::$separator.$group.Paths::$separator.$username;
                
                LogCLI::MessageResult('Creating directory: '.$structure, 2, LogCLI::INFO);
                
                if(@mkdir($structure, 0755, true))
                {
                    LogCLI::Result(LogCLI::OK);
                }
                else
                {
                    LogCLI::Result(LogCLI::FAIL);
                    LogCLI::Fail('User '.$username.' already exists!');
                    //LogCLI::Fail($e->getMessage());
                }
            }
            else
            {
                // adding website
                $website = $name;
                LogCLI::Message('Adding website: '.$website, 0);
                $username = (isset($arguments['name2'])) ? $arguments['name2'] : Paths::$defaultUser;
                $group = Paths::$defaultGroup;
                LogCLI::MessageResult('Username and group: '.$username.'/'.$group, 2, LogCLI::INFO);
                $path = Paths::$db.Paths::$separator.$group.Paths::$separator.$username.Paths::$separator;
                if(file_exists($path))
                {
                    if(!file_exists($path.$website.'.yml') && self::GetFullPath($website) === false)
                    {
                        FileOperation::CreateEmptyFile($path.$website.'.yml');
                        LogCLI::Result(LogCLI::OK);
                    }
                    else
                    {
                        LogCLI::Result(LogCLI::FAIL);
                        LogCLI::Fail('Website '.$website.', under '.$group.'/'.$username.' already exists!');
                    }
                }
                else
                {
                    LogCLI::Result(LogCLI::FAIL);
                    LogCLI::Fail('Group and/or user '.$group.'/'.$username.' does not exist!');
                }
            }
        }
    }
    
    public static function GetFullPath($site)
    {
        $files = FileOperation::getAllFilesByExtension(Paths::$db, 'yml');
        $pathinfo = array();
        //$siteYML = false;
        foreach($files as $id => $file)
        {
            $pathinfo[$id] = FileOperation::pathinfo_utf($file);
            if($pathinfo[$id]['filename'] == $site)
            {
                return $files[$id];
            }
        }
        return false;
    }
}