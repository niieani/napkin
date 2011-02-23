<?php

namespace HypoConf;

use Tools\LogCLI;
use Tools\StringTools;

use Tools\ArrayTools;
use Tools\Tree;
use Tools\FileOperation;

//only for cores
use Tools\System;

use HypoConf\ConfigParser;
use HypoConf\ConfigScopes;
use HypoConf\ConfigScopes\ApplicationsDB;

class Commands
{
//    public static $ApplicationsDB;
    
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
                if($argument['text'] == 'config')
                {
                    $file = 'config.yml';
                    Commands\Set\NginxConfig::LoadAndSave($arguments, $file);
                }
                else // opening a site or default site
                {
                    $file = $argument['text'].'.yml';
                    Commands\Set\Site::LoadAndSave($arguments, $file);
                }
                
                //var_dump($setting);
                
                /*
                    VALIDATION GOES HERE, TRAVERSING THE TREE ALSO
                */
                
                /*
                $last = StringTools::ReturnLastBit($path);
                
                
                if ($path)
                    $nginxParse->SetConfig($path, array($last => $result->command->args['values']));
                else LogCLI::Fail('No setting by name: '.end(array_keys($setting)));
                
                $nginxParse->ReturnYAML();
                */
                
                //System::getCPUs();
                //System::getCPUcores();
                /*
                   add notification if modified the file or added a new option (important)
                */
            }
        }
    }
    
    function GenerateParsed($arguments)
    {
        //$ApplicationsDB = new ConfigScopes\ApplicationsDB();
        $configScopesNginx = ApplicationsDB::LoadApplication('nginx');
        //$configScopes = new ConfigScopes($ApplicationsDB->GetParsers('nginx'), $ApplicationsDB->GetTemplates('nginx'), &$config);
        
        foreach($arguments['file'] as $file)
        {
            $files[] = $file; //.'.yml';
        }
        
        $nginx = new ConfigScopes\SettingsDB();
        $nginx->MergeFromYAML('config.yml', false, true, true); //true for compilation
        $nginx->MergeFromYAMLs($files, 'server', true, true); //true for compilation
        //$nginx->MergeFromYAMLs($files, 'server', true, true); //true for compilation
        
        
        ApplicationsDB::LoadConfig(&$nginx->DB);
        
        $parsedFile = $configScopesNginx->parseTemplateRecursively('nginx');
        echo PHP_EOL.$parsedFile;
    }
}