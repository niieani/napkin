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
    protected $DB = array();
    protected static $AppsDir = 'stems/';
    protected $SettingsDB;
    
    public static $applications = array(
        'nginx'              => array('HypoConf\\ConfigScopes\\Parser\\Nginx', true)
    );
    
    public function LoadAll()
    {
        foreach(self::$applications as $application)
        {
            $this->LoadApplication($application);
        }
    }
    
    public function RegisterApplication($application, $classname)
    {
        self::$applications[$application] = array($classname, false);
    }
    
    public function LoadApplication($application)
    {
        // load templates:
        $templateFiles = FileOperation::getAllFilesByExtension(self::$AppsDir.$application, 'tpl');
        //var_dump($templateFiles);
        $this->DB[$application]['templatesInstance'] = new TemplatesDB($templateFiles);
        $this->DB[$application]['templates'] = $this->DB[$application]['templatesInstance']->DB;
        
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
        
        $this->DB[$application]['parserInstance'] = new $className($this->DB[$application]['templates']);
        $this->DB[$application]['parsers'] = $this->DB[$application]['parserInstance']->GetSubParsers();
        
        $this->DB[$application]['scopesInstance'] = new HypoConf\ConfigScopes(&$this->DB[$application]['parsers'], &$this->DB[$application]['templates']);
        $this->DB[$application]['scopesInstance']->rootscope = $application;
        
        return $this->DB[$application]['scopesInstance'];
    }
    
    public function FixPath($application, $path, $iterativeSetting = 0)
    {
        return $this->DB[$application]['parserInstance']->FixPath($path, $iterativeSetting);
    }
    
    public function LoadConfig(&$config)
    {
        foreach($this->DB as $application)
        {
            $application['scopesInstance']->config = &$config;
        }
    }
    /*
    public function LoadConfigFromFiles(&$files, $compilation = false)
    {
        $this->SettingsDB = new ConfigScopes\SettingsDB();
        $this->SettingsDB->MergeFromYAML($file, $compilation); //true for compilation
    }
    */
    public function GetTemplates($application)
    {
        return $this->DB[$application]['templates'];
    }
    
    public function GetParsers($application)
    {
        return $this->DB[$application]['parsers'];
    }
    
    public function GetAllSettings($application)
    {
        return $this->DB[$application]['scopesInstance']->returnSettingsList();
        //$this->DB[$application]['settingsList'] =
    }
    
    public function GetSettingsList($application, $scope)
    {
        return $this->DB[$application]['scopesInstance']->returnSettingsList($scope);
    }
}