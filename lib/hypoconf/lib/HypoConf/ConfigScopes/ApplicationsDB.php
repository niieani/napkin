<?php
namespace HypoConf\ConfigScopes;

use HypoConf;

//use \ConfigScopes\nginx;
//use \ConfigScopes;

use \Tools\FileOperation;
use \Tools\ArrayTools;
use \Tools\LogCLI;

class ApplicationsDB
{
    //protected static $DB = array();
    protected static $DB = array();
    protected static $AppsDir = 'stems/';
    protected static $SettingsDB;
    
    public static $applications = array(
        'nginx'              => array('HypoConf\\ConfigScopes\\Parser\\Nginx', true)
    );
    
    public static function LoadAll()
    {
        foreach(self::$applications as $application)
        {
            self::LoadApplication($application);
        }
    }
    
    public static function RegisterApplication($application, $classname)
    {
        self::$applications[$application] = array($classname, false);
    }
    
    public static function LoadApplication($application)
    {
        // load templates:
        $templateFiles = FileOperation::getAllFilesByExtension(self::$AppsDir.$application, 'tpl');
        //var_dump($templateFiles);
        self::$DB[$application]['templatesInstance'] = new TemplatesDB($templateFiles);
        self::$DB[$application]['templates'] = self::$DB[$application]['templatesInstance']->DB;
        
        // register possible settings:
        
        if(isset(self::$applications[$application]))
        {
            $classInfo = self::$applications[$application];
            $className = $classInfo[0];
        }
        else
        {
            // throw error
        }
        
        self::$DB[$application]['parserInstance'] = new $className(self::$DB[$application]['templates']);
        self::$DB[$application]['parsers'] = self::$DB[$application]['parserInstance']->GetSubParsers();
        
        self::$DB[$application]['scopesInstance'] = new HypoConf\ConfigScopes(&self::$DB[$application]['parsers'], &self::$DB[$application]['templates']);
        self::$DB[$application]['scopesInstance']->rootscope = $application;
//        echo PHP_EOL.$application.PHP_EOL;
        
        return self::$DB[$application]['scopesInstance'];
    }
    
    public static function FixPath($application, $path, $iterativeSetting = 0)
    {
        return self::$DB[$application]['parserInstance']->FixPath($path, $iterativeSetting);
    }
    
    public static function LoadConfig(&$config)
    {
        foreach(self::$DB as $application)
        {
            $application['scopesInstance']->config = &$config;
        }
    }
    /*
    public function LoadConfigFromFiles(&$files, $compilation = false)
    {
        self::$SettingsDB = new ConfigScopes\SettingsDB();
        self::$SettingsDB->MergeFromYAML($file, $compilation); //true for compilation
    }
    */
    public static function GetTemplates($application)
    {
        return self::$DB[$application]['templates'];
    }
    
    public static function GetParsers($application)
    {
        return self::$DB[$application]['parsers'];
    }
    
    public static function GetAllSettings($application)
    {
//        echo PHP_EOL.var_dump(self::$DB[$application]['scopesInstance']->rootscope).PHP_EOL;
        return self::$DB[$application]['scopesInstance']->returnSettingsList(); //in brackets self::$DB[$application]['scopesInstance']->rootscope or maybe simpler would be just ($application), since it's the same ?
        //self::$DB[$application]['settingsList'] =
    }
    
    public static function GetSettingsList($application, $scope)
    {
        return self::$DB[$application]['scopesInstance']->returnSettingsList($scope);
    }
}