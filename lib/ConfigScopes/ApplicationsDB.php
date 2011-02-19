<?php
namespace ConfigScopes;

//use \ConfigScopes\nginx;
use \ConfigScopes;
use \Tools\FileOperation;
use \Tools\ArrayTools;
use \Tools\LogCLI;

class ApplicationsDB
{
    protected static $DB = array();
    protected static $AppsDir = 'stems/';
    
    public static $parsers = array(
//        'nginx'              => array('Parser\\nginx', true)
        'nginx'              => array('Nginx', true)
    );
    
    /*
    public function __construct()
    {
        
    }
    */
    
    public static function RegisterApplication($application)
    {
        // load templates:
        $templateFiles = FileOperation::getAllFilesByExtension(self::$AppsDir.$application, '.tpl');
        self::$DB[$application]['templatesInstance'] = new TemplatesDB($templateFiles);
        self::$DB[$application]['templates'] = self::$DB[$application]['templatesInstance']->DB;
        
        // register possible settings:
        
        //print_r(get_declared_classes());
        
        if(isset(self::$parsers[$application]))
        {
            $classInfo = self::$parsers[$application];
            $className = $classInfo[0];
        }
        else
        {
            // throw error
        }
        $className = "Nginx";
        self::$DB[$application]['parserInstance'] = new $className(self::$DB[$application]['templates']);
        //self::$DB[$application]['parserInstance'] = new Nginx(self::$DB[$application]['templates']);
        self::$DB[$application]['parsers'] = self::$DB[$application]['parserInstance']->GetSubParsers();
    }
    
    public static function GetTemplates($application)
    {
        return self::$DB[$application]['templates'];
    }
    
    public static function GetParsers($application)
    {
        return self::$DB[$application]['parsers'];
    }
}