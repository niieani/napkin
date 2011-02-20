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
    //protected static $AppsDir = 'stems/';
    protected $DB = array();
    protected static $AppsDir = 'stems/';
    
    public static $parsers = array(
//        'nginx'              => array('Parser\\nginx', true)
        'nginx'              => array('HypoConf\\ConfigScopes\\Parser\\Nginx', true)
    );
    
    /*
    public function __construct()
    {
        
    }
    */
    
    public function RegisterApplication($application)
    {
        // load templates:
        $templateFiles = FileOperation::getAllFilesByExtension(self::$AppsDir.$application, 'tpl');
        //var_dump($templateFiles);
        $this->DB[$application]['templatesInstance'] = new TemplatesDB($templateFiles);
        $this->DB[$application]['templates'] = $this->DB[$application]['templatesInstance']->DB;
        
        // register possible settings:
        
        if(isset(self::$parsers[$application]))
        {
            $classInfo = self::$parsers[$application];
            $className = $classInfo[0];
        }
        else
        {
            // throw error
        }
        
        $this->DB[$application]['parserInstance'] = new $className($this->DB[$application]['templates']);
        $this->DB[$application]['parsers'] = $this->DB[$application]['parserInstance']->GetSubParsers();
    }
    
    public function GetTemplates($application)
    {
        return $this->DB[$application]['templates'];
    }
    
    public function GetParsers($application)
    {
        return $this->DB[$application]['parsers'];
    }
}