<?php
namespace Applications;

use \ConfigStyles\BracketConfig\NginxConfig;
use \ConfigStyles\BracketConfig\NginxScope;
//use \ConfigStyles\GlobalConfig\GlobalConfig;
use \Symfony\Component\Yaml\Yaml;
use \Tools\LogCLI;
use \Tools\Stems;
use \Tools\FileOperation;
use \Tools\StringTools;
use \Tools\System;

class Nginx
{
    private $confScope;
    private $nginx = array();
    private $usableScopes = array();
    private $usableConfigs = array();
    private $settingsDB = array();
    private $foreignDefinitions = array();
    public function __construct()
    {
        $this->confScope = new NginxScope;
        $this->nginx = self::DefinitionsParsed();
        LogCLI::Message('Listing usable configuration scopes', 5);
        $this->usableScopes = $this->ListUsableScopes(&$this->nginx);
        LogCLI::Result(LogCLI::INFO);
        LogCLI::Message('Listing usable configurations', 5);
        $this->usableConfigs = $this->ListUsableConfigs(&$this->nginx);
        LogCLI::Result(LogCLI::INFO);
        var_dump($this->foreignDefinitions);
        //var_dump($this->usableScopes);
    }
    /*
    public function _reset()
    {
        $this->nginx = self::Definitions();
    }
    */
    public function PrintFile()
    {
        LogCLI::MessageResult(LogCLI::BLUE.'Printing configuration file'.LogCLI::RESET, 1, LogCLI::INFO);
        echo "\n".$this->ReturnConfigFile();
    }
    
    public function TraverseDevinitionTree(array $setting)
    {
        $matches = array();
        foreach(array_reverse(self::GetMultiDimentionalElements($setting), true) as $path)
        {
            LogCLI::MessageResult('Analysing path: '.LogCLI::BLUE.$path.LogCLI::RESET, 6, LogCLI::INFO);
            foreach($this->usableConfigs as $fullpath)
            {
                if(stripos($fullpath, $path) !== FALSE)
                {
                    LogCLI::MessageResult('Found match! At: '.LogCLI::BLUE.$fullpath.LogCLI::RESET, 2, LogCLI::INFO);
                    /*
                    this code is good, don't remove
                    if(!is_object(self::accessArrayElementByPath($this->nginx, $fullpath)))
                    {
                        $last = StringTools::ReturnLastBit($fullpath);
                        $fullpath = StringTools::DropLastBit($fullpath, 1);
                        $fullpath = StringTools::AddBit($fullpath, $this->foreignDefinitions[$last]);
                        LogCLI::MessageResult('Common config detected! Defined by: '.LogCLI::BLUE.$fullpath.LogCLI::RESET, 5, LogCLI::INFO);
                    }
                    */
                    $matches[] = $fullpath;
                }
            }
        }
        $count = count($matches);
        if($count>1) 
        LogCLI::MessageResult(LogCLI::BLUE.'Multiple matches! Try to be more specific.'.LogCLI::RESET, 1, LogCLI::INFO);
        elseif($count == 1) return $matches[0];
        else
        return false;
    }
    
    public function SetConfig($path, $setting)
    {
        //$name = explode('/', $path);
        //->set(array(end($name) => $setting));
        
        //self::accessArrayElementByPath($this->nginx, $path)->set($setting);
        self::createArrayElementByPath($this->settingsDB['nginx'], $path, $setting, 1);
        
        //self::accessArrayElementByPath($this->settingsDB, $path)->set($setting);
        
        //var_dump($this->settingsDB);
        
        //echo $path.PHP_EOL;
        //var_dump(self::accessArrayElementByPath($this->nginx, $path)->returnAll());
    }
    
    //public function SetConfigAll($
    
    public function ReturnYAML($path = false)
    {
        if($path === false)
            FileOperation::ToYAMLFile($this->settingsDB, true);
        else
            FileOperation::ToYAMLFile($this->settingsDB, false, $path);
    }
    
    private function ListUsableConfigs(array $definitions)
    {
        // MultiDimentional ForEach:
        //var_dump(self::GetMultiDimentionalElements($config['nginx']));
        //$pathsWithObjects[] = array();
        $pathsWithSettings = array();
        foreach(self::GetMultiDimentionalElements($definitions) as $path)
        {
            LogCLI::MessageResult('Getting path: '.LogCLI::BLUE.$path.LogCLI::RESET, 7, LogCLI::INFO);
            foreach(self::getArrayElementByPath($definitions, $path) as $subpath => $element)
            {
                
                LogCLI::MessageResult('Getting element on path: '.LogCLI::BLUE.$path.'/'.$subpath.LogCLI::RESET, 7, LogCLI::INFO);
                /*
                if((!is_object($element) && !is_array($element)) && !in_array($path.'/'.$subpath, $pathsWithSettings))
                {
                    LogCLI::MessageResult('Found config on path: '.LogCLI::BLUE.$path.LogCLI::RESET, 5, LogCLI::INFO);
                    $pathsWithSettings[] = $path.'/'.$subpath;
                }*/
                if(!(in_array($path.'/'.$subpath, $pathsWithSettings)))
                {
                    if(is_object($element))
                    {
                        LogCLI::MessageResult('Found config on path: '.LogCLI::BLUE.$path.'/'.$subpath.LogCLI::RESET, 5, LogCLI::INFO);
                        $pathsWithSettings[] = $path.'/'.$subpath;
                    }
                    elseif (is_array($element) && array_key_exists('definedby', $element))
                    // && !(in_array($path.'/'.$element['definedby'], $pathsWithSettings)
                    //&& isset($element['definedby']) && !(in_array($path.'/'.$element['definedby'], $pathsWithSettings)))
                    {
                            LogCLI::MessageResult('Found relative config on path: '.LogCLI::BLUE.$path.'/'.$subpath.LogCLI::RESET.', defined by: '.LogCLI::YELLOW.$element['definedby'].LogCLI::RESET, 5, LogCLI::INFO);
                            $pathsWithSettings[] = $path.'/'.$subpath;
                            $this->foreignDefinitions[$subpath] = $element['definedby'];
                    }
                }
            }
            //var_dump(self::getArrayElementByPath($config['nginx'], $path));
        }
        //return (array_unique($pathsWithObjects));
        return $pathsWithSettings;
    }
    
    private static function ListUsableScopes(array $definitions)
    {
        // MultiDimentional ForEach:
        //var_dump(self::GetMultiDimentionalElements($config['nginx']));
        $pathsWithObjects = array();
        foreach(self::GetMultiDimentionalElements($definitions) as $path)
        {
            //LogCLI::MessageResult('Path: '.LogCLI::BLUE.$path.LogCLI::RESET, 7, LogCLI::INFO);
            foreach(self::getArrayElementByPath($definitions, $path) as $element)
            {
                //var_dump($element);
                if(is_object($element) && !in_array($path, $pathsWithObjects))
                {
                    LogCLI::MessageResult('Found scope on path: '.LogCLI::BLUE.$path.LogCLI::RESET, 5, LogCLI::INFO);
                    $pathsWithObjects[] = $path;
                }
            }
            //var_dump(self::getArrayElementByPath($config['nginx'], $path));
        }
        //return (array_unique($pathsWithObjects));
        return $pathsWithObjects;
    }
    
    public static function DefinitionsParsed()
    {
        //Stems::LoadDefinitions('nginx');
        /*
        $application = 'nginx';
        foreach (glob(HC_DIR.'/stems/'.$application."/*.php") as $filename)
        {
            try { 
                LogCLI::Message("Loading definitions from file: $filename", 2);
                include($filename);
                LogCLI::Result(LogCLI::OK);
            } catch (Exception $e) {
                LogCLI::Result(LogCLI::FAIL);
                LogCLI::Fatal('Caught exception - '.$e->getMessage());
            }
        }
        */
        
        $nginx['_root']['user'] = 
                new NginxConfig('[[user %(user)s [[%(group)s]]]]');
        $nginx['_root']['group']['definedby'] = 'user';
                
        $nginx['_root']['log']['error']['file'] = 
                new NginxConfig('[[error_log %(file)s [[%(style)s]]]]', 'http', 1);
                
        $nginx['_root']['connections']['limit'] = 
                new NginxConfig('[[worker_connections %(limit)s]]', 'events', 1);
                
        $nginx['_root']['connections']['multi'] = 
                new NginxConfig('[[multi_accept %(multi)s]]', 'events', 1);

        //OS detector function
        $nginx['_root']['use'] = 
                new NginxConfig('[[use %(use)s]]', 'events', 1);
        
        //iterative (can be many 'listen' present)
        $nginx['sites']['listen']['_definition'] = 
                new NginxConfig('listen [[%(ip)s:]][[%(port)s ]][[%(options)s]]', 'server', 1, true);
                
        $nginx['sites']['listen']['ip']['definedby'] = '_definition';
        $nginx['sites']['listen']['ip']['iterateat'] = '/sites/listen';
        $nginx['sites']['listen']['port']['definedby'] = '_definition';
        $nginx['sites']['listen']['port']['iterateat'] = '/sites/listen';
        $nginx['sites']['listen']['options']['definedby'] = '_definition';
        $nginx['sites']['listen']['options']['iterateat'] = '/sites/listen';
                
        $nginx['sites']['domain'] = 
                new NginxConfig('[[server_name %(domain)s]]', 'server', 1);
        
        return $nginx;
    }
    
    /*public static function DefinitionsCustom()
    {
        $nginxCustom['sites']['listen']
    }*/
    
    public function SetAutomatedValues()
    {
        // replace this with a function detecting the operating system and setting the right one
        $this->nginx['_root']['use']->set(array('use' => 'epoll'));
    }
    
    public function ReturnConfigFile()
    {
        //var_dump($this->confScope);
        //$nginx = self::Definitions();
        
        //NginxScope::addAllStems($this->nginx, $this->confScope);
        
        return $this->confScope->returnScopes();
    }
    /*
    public static function array_dimen_count($ArrayInput, $dimCount = 0)
    { // Count an array dimensions
       if(is_array($ArrayInput)) {
          return self::array_dimen_count(current($ArrayInput), $dimCount + 1);
       } else {
          return $dimCount;
       }
    }
    */
    /*
    public static function array_dimen_count(&$ArrayInput)
    {
        $recursive = new \ParentIterator(new \RecursiveArrayiterator($ArrayInput));
        $iterator  = new \RecursiveIteratorIterator($recursive, \RecursiveIteratorIterator::SELF_FIRST);
        $highest = 0;
        
        foreach ($iterator as $item)
        {
            // Build path from "parent" array keys
            for ($path = "", $i = 0; $i <= $iterator->getDepth(); $i++) {
                $path .= "/" . $iterator->getSubIterator($i)->key();
            }
            // Output depth and "path"
            //printf("%d %s\n", $iterator->getDepth() + 1, ltrim($path, "/"));
            $currentDepth = $iterator->getDepth() + 1;
            if ($highest < $currentDepth) $highest = $currentDepth;
        }
        return $highest;
    }
    */
    public static function GetMultiDimentionalElements(&$ArrayInput)
    {
        //if(is_array($ArrayInput) && !is_object($ArrayInput))
        //{
        $recursive = new \ParentIterator(new \RecursiveArrayiterator($ArrayInput));
        $iterator  = new \RecursiveIteratorIterator($recursive, \RecursiveIteratorIterator::SELF_FIRST);
        $elements = array();
        foreach ($iterator as $item)
        {
            // Build path from "parent" array keys
            for ($path = "", $i = 0; $i <= $iterator->getDepth(); $i++) {
                $path .= "/" . $iterator->getSubIterator($i)->key();
            }
            // Output depth and "path"
            //printf("%d %s\n", $iterator->getDepth() + 1, ltrim($path, "/"));
            $elements[] = ltrim($path, "/");
        }
        return $elements;
        //}
    }
    
    // http://www.24hourapps.com/2009/01/dot-notation-array-access-in-php.html
    public static function accessArrayElementByPath(&$arr, $path = null, $checkEmpty = true, $emptyResponse = false) //$trimPath=0
    {
        // Check path
        if (!$path) user_error("Missing array path for array", E_USER_WARNING);
        
        // HypoConf Specific
        //$position = strpos($path, '/');
        //$name = ltrim(strrchr($path, '/'), '/');
        //if (empty($name)) $name = $path;
        
        // Vars
        $pathElements = split('/', $path);
        $path =& $arr;
        
        // Go through path elements
        foreach ($pathElements as $e)
        {
            // Check set
            if (!isset($path[$e])) return $emptyResponse;
            
            // Check empty
            if ($checkEmpty and empty($path[$e])) return $emptyResponse;
            
            // Update path
            $path =& $path[$e];
        }
        
        // Everything checked out, return value
        //if($trimPath > 0) return array($name => $path);
        return $path;
    }
    
    public static function createArrayElementByPath(&$arr, $path = null, $value = null, $skipN = 0, $emptyResponse = false) //$trimPath=0
    {
        // Check path
        if (!$path) user_error("Missing array path for array", E_USER_WARNING);
        
        // Vars
        $pathElements = split('/', $path);
        $path =& $arr;
        
        if($skipN > 0) $pathElements = array_splice($pathElements, 0, count($pathElements)-$skipN);
        
        // Go through path elements
        foreach ($pathElements as $e)
        {
            // Check set
            if (!isset($path[$e])) $path[$e] = array();
            
            // Check empty
            //if ($checkEmpty and empty($path[$e])) return $emptyResponse;
            
            // Update path
            $path =& $path[$e];
        }
        $path = System::MergeArrays($path, $value);
        
        // Everything checked out, return value
        //if($trimPath > 0) return array($name => $path);
        return $path;
    }
    
    public static function getArrayElementByPath($array, $path, $trimPath = false)
    {
        if(is_array($array) && !is_object($array))
        {
            //var_dump($path);
            //http://www.jonasjohn.de/snippets/php/array-get-path.htm
            $position = strpos($path, '/');
            $name = ltrim(strrchr($path, '/'), '/');
            if (empty($name)) $name = $path;
            //$name = $path;
            //echo $name.PHP_EOL;
            /*if ((($path == '') || $position===false) && $trimPath > 0)
            {
                echo 'zwracam calosc'.PHP_EOL;
                return $array;
            }
            for($i=0; $i<$trimPath; $i++)
            {
                $path = strstr($path, '/', true);
            }*/
            
            //if (empty(strrchr($path, '/'))) return $array;
            
            $found = true;
            
            $path = explode('/', $path);
    
            for ($x=0; ($x < count($path) and $found); $x++){ //-$trimPath
         
                $key = $path[$x];
                
                //if($trimPath>0) echo 'KEY: '.$key.PHP_EOL;
                //if(is_array($array[$key]))
                //{
                if (isset($array[$key])){
                    $array = $array[$key];
                }        
                else { $found = false; }
                //}
            }
            if($found === false) return array(false);
            //$result = $array;
            //echo "name: $name\n";
            if($trimPath > 0) return array($name => $array);
            return $array;
            //return &$found;
        }
    }
    
    public function RecurseArray( $inarray, $toarray )
    {
        /*
        foreach ($config['nginx'] as $scope => $scopeConfig)
        {
            if(is_array($scopeConfig) && $scope != 'sites') //&& is_array($this->nginx[$scope]) 
            {
                //LogCLI::MessageResult($scope);
                NginxConfig::setAll($config['nginx'][$scope], &$this->nginx[$scope]);
                if ($last) 
                {
                    //LogCLI::MessageResult($scope);
                    LogCLI::Message("Generating scope: [$scope]", 3);
                    NginxScope::addAllStems($this->nginx[$scope], $this->confScope);
                    LogCLI::Result(LogCLI::INFO);
                }
            }
        }
        */
        foreach ( $inarray as $inkey => $inval )
        {
            if ( is_array( $inval ) )
            {
                $toarray = $this->RecurseArray( $inval, $toarray );
            }
            else
            {
                $toarray[] = $inval;
            }
        }
       
        return $toarray;
    } 
    
    
    public static function ConformizeConfigSite($site)
    {
        if(isset($site['listen']))
            if(!isset($site['listen'][1]))
            {
                $site['listen'] = array($site['listen']);
            }
        
        if(isset($site['ssl']) && $site['ssl'] == true)
        {
            foreach($site['listen'] as $listenkey => $listen)
            {
                if(isset($site['listen'][$listenkey]['options']))
                $site['listen'][$listenkey]['options'] = System::MergeArrays((array)$listen['options'], (array)'ssl');
            }
        }
        
        return $site;
    }
    
    public static function HumanizeConfigSite($site)
    {
        foreach($site['listen'] as $listenkey => $listen)
        {
            foreach($site[$listenkey]['options'] as $option)
            {
                if($option == 'ssl') 
                {
                    $site['ssl'] = true;
                    break 2;
                }
            }
        }
        
        return $site;
    }
    
    public function ParseFromYAMLs(array $files)
    {
        $this->SetAutomatedValues();
        
        //Stems::LoadDefinitions('nginx');
        $last = false;
        $total = count($files) - 1;
        $sites_defaults = array();
        foreach($files as $i => $file)
        {
            if ($total == $i) $last = true;
            
            LogCLI::Message('Loading file: '.LogCLI::BLUE.$file.LogCLI::RESET, 1);
            if (file_exists($file))
            {
                LogCLI::Result(LogCLI::OK);
                
                LogCLI::Message('Parsing file: '.LogCLI::BLUE.$file.LogCLI::RESET, 1);
                try
                {
                    $config = YAML::load($file);
                    LogCLI::Result(LogCLI::OK);
                }
                catch (Exception $e)
                {
                    LogCLI::Result(LogCLI::FAIL);
                    LogCLI::Fail($e->getMessage());
                }
                
                if (isset($config['nginx']))
                {
                    // adding to main DB
                    $this->settingsDB = System::MergeArrays($this->settingsDB, $config);
                    
                    foreach($this->usableScopes as $arrayPath)
                    {
                        //LogCLI::MessageResult($arrayPath);
                        //var_dump(self::getArrayElementByPath($config['nginx'], $arrayPath, 1));
                        /*
                        foreach(self::accessArrayElementByPath($config['nginx'], $arrayPath) as $scope => $scopeConfig)
                        {
                            echo("skope ".$scope.":\n");
                        }
                        */
                        //LogCLI::MessageResult('Path: '.LogCLI::BLUE.$arrayPath.LogCLI::RESET, 6, LogCLI::INFO);
                        foreach(self::getArrayElementByPath($config['nginx'], $arrayPath, true) as $scope => $scopeConfig)
                        {
                            if($scopeConfig !== false)
                            {
                                //echo("skope ".$scope.":\n");
                                //var_dump($scopeConfig);
                                if(is_array($scopeConfig) && $scope != 'sites') //&& is_array($this->nginx[$scope]) 
                                {
                                    //LogCLI::MessageResult("Number of dimentions in scope [$scope]: ".self::array_dimen_count($scopeConfig), 3);
                                    //self::array_dimen_count($scopeConfig);
                                    //var_dump($scopeConfig);
                                    //LogCLI::MessageResult($scope);
                                        NginxConfig::setAll(self::accessArrayElementByPath($config['nginx'], $arrayPath), self::accessArrayElementByPath($this->nginx, $arrayPath));
                                    if ($last) 
                                    {
                                        //LogCLI::MessageResult($scope);
                                        LogCLI::Message("Generating scope: [$arrayPath]", 3);
                                        NginxScope::addAllStems(self::accessArrayElementByPath($this->nginx, $arrayPath), $this->confScope);
                                        LogCLI::Result(LogCLI::INFO);
                                    }
                                }
                            }
                        }
                    }
                    // sites scopes
                    if (isset($config['nginx']['sites']['_defaults']))
                    {
                        $config['nginx']['sites']['_defaults'] = self::ConformizeConfigSite($config['nginx']['sites']['_defaults']);
                        $sites_defaults = System::MergeArrays($sites_defaults, $config['nginx']['sites']['_defaults']);
                    }
                    
                    if (isset($config['nginx']['sites']))
                    {
                        foreach ($config['nginx']['sites'] as $key => $site)
                        {
                            if ($key != '_defaults')
                            {
                                $site = self::ConformizeConfigSite($site);
                                
                                $siteScope[$key] = new NginxScope;
                                
                                LogCLI::Message("Pushing defaults for subscope [server]: $key", 3);
                                NginxConfig::resetAll($this->nginx['sites']);
                                
                                if (isset($sites_defaults))
                                {
                                    NginxConfig::setAll($sites_defaults, $this->nginx['sites']);
                                }
                                
                                LogCLI::Result(LogCLI::INFO);
                                
                                LogCLI::Message("Setting in subscope [server]: $key", 3);
                            	//$siteScope[$key] = new NginxScope;
                            	NginxConfig::setAll($site, $this->nginx['sites']);
                                LogCLI::Result(LogCLI::INFO);
                                LogCLI::Message("Adding subscope [server]: $key", 3);
                            	NginxScope::addAllStems($this->nginx['sites'], $siteScope[$key]);
                                LogCLI::Result(LogCLI::INFO);
                            }
                        }
                    }
                        
                        if (isset($siteScope))
                        {
                            foreach ($siteScope as $scope)
                            {
                                LogCLI::Message("Adding scope: [server]", 3);
                                $this->confScope->addStem(array('scope' => 'http', 'output' => $scope->returnScopes(), 'level' => 1));
                                LogCLI::Result(LogCLI::INFO);
                            }
                        }
                        
                    $this->confScope->orderScopes(array('_ROOT', 'events', 'http'));
                }
            } else {
                LogCLI::Result(LogCLI::FAIL);
                LogCLI::Fatal("No such file: $file");
            }
        }
        //return false;
    }
}
