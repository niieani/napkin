<?php
namespace HypoConf;

use HypoConf;

use \Tools\LogCLI;
//use \ConfigParser\ConfigParser;
use \Tools\FileOperation;
use \Tools\StringTools;
use \Tools\ArrayTools;
use \Tools\MakePath;

class ConfigScopes
{
    /**
     * @var array|null gets loaded by ApplicationsDB::LoadConfig
     */
    public $config = null;

    public $rootscope = null;
    
    protected $templates = array();
    protected $parsers = array();
    protected $patterns = array();
    protected $results = array();
    protected $currentScope;
    
    protected $settingsList = array();
    protected $templateTree = array();
    protected $pathsList = array();
    
    public function __construct(array $parsers, array $templates, array $config = null)
    {
        $this->templates = $templates;
        $this->parsers = $parsers;
        
        //$this->listSettings();
        
        if($config !== null) $this->config = $config;
        
        foreach(array_keys($this->templates) as $name)
        {
            $this->patterns[$name] = '<<'.$name.'>>';
        }
    }
    
    //returns parsed file
    public function parseTemplateRecursively($scope) // = 'root'
    {
        if(isset($this->templates[$scope]) && isset($this->parsers[$scope]))
        {
            $this->currentScope = $scope;
            $this->parseTree($scope);
            return $this->results[$scope];
        }
        else return false;
    }
    
    public function listSettings($scope = false)
    {
        if($scope === false) $scope = $this->rootscope;
        
        LogCLI::Message(LogCLI::YELLOW.'Making a list of available settings '.LogCLI::BLUE.'@'.$scope.LogCLI::GREEN.':'.LogCLI::RESET, 5);
        foreach($this->parsers as $parsername => $parser)
        {
            foreach($parser->options as $option)
            {
                /* TODO: remove multi-settings
                if(is_array($option->path))
                {
                    foreach($option->path as $path)
                    {
                        LogCLI::MessageResult('Scope '.LogCLI::BLUE.$parsername.LogCLI::RESET.' => Found multi-setting => '.LogCLI::BLUE.$path.LogCLI::RESET, 5, LogCLI::INFO);
                        $this->settingsList[$parsername][] = $path;
                    }
                }
                else
                {
                */
                if($option->settable)
                {
                    LogCLI::MessageResult('Scope '.LogCLI::BLUE.$parsername.LogCLI::RESET.' => Found setting => '.LogCLI::BLUE.$option->path.LogCLI::RESET, 5, LogCLI::INFO);
                    $this->settingsList[$parsername][] = $option->path;
                }
                /*
                }
                */
            }
        }
        // we need to know relationships between the scopes and which of them are recursive
        $paths = new MakePath();
        $this->makeTree($paths, $scope);
        $this->pathsList = $paths->getPaths();
        LogCLI::Result(LogCLI::INFO);
    }
    
    public function returnSettingsList($scope = false)
    {
        $this->listSettings($scope);
        //var_dump($this->settingsList);
        //var_dump($this->pathsList);
        
        $settingsInPaths = array();
        
        foreach($this->pathsList as $path)
        {
            $scope = StringTools::ReturnLastBit($path);
            if(isset($this->settingsList[$scope]))
            {
                ArrayTools::mergeArrayElementByPath(&$settingsInPaths, $path, $this->settingsList[$scope]);
                //var_dump($this->settingsList[$scope]);
            }
            else
            {
                LogCLI::Warning('The specification for the stem '.LogCLI::GREEN.$scope.LogCLI::YELLOW.' is missing in the parser class.');
                //ArrayTools::mergeArrayElementByPath(&$settingsInPaths, $path, $this->settingsList);
            }
        }
        //var_dump($settingsInPaths);
        
        return $settingsInPaths;
        
        //return $this->settingsList;
        //return ArrayTools::GetMultiDimentionalElements();
    }
    
    public function insertScope($child, $parent, $pattern = null, $overrideIndentationTemplate = false)
    {
        if (!isset($pattern)) 
        {
            $pattern = &$this->patterns[$child];
        }
        
        LogCLI::MessageResult("Inserting: ".LogCLI::BLUE.$child.LogCLI::RESET." => ".LogCLI::GREEN.$parent.LogCLI::RESET." (will replace ".LogCLI::YELLOW.$pattern.LogCLI::RESET.")", 3);

        //TODO: make sure this two IF's never get executed
        if (!isset($this->results[$parent])) 
        {
            LogCLI::MessageResult("Notice: ".LogCLI::YELLOW.'No such parent'.LogCLI::RESET." - ".LogCLI::GREEN.$parent, 2);
            $proper_parent = explode('/', $parent);
            //$proper_parent = (isset($proper_parent[2])) ? $proper_parent[2] : $proper_parent[0];
            $proper_parent = end($proper_parent); //!is_numeric(end($proper_parent)) ? 
            $this->results[$parent] = $this->templates[$proper_parent];
        }
        if (!isset($this->results[$child])) 
        {
            LogCLI::MessageResult("Notice: ".LogCLI::YELLOW.'No such child'.LogCLI::RESET." - ".LogCLI::GREEN.$child, 2);

            $proper_child = explode('/', $child);
            
            //echo "PROPER CHILD: \n";
            //var_dump($proper_child);

            $proper_child = (isset($proper_child[2])) ? $proper_child[2] : $proper_child[0];
            $this->results[$child] = $this->templates[$proper_child];
        }
        
        //var_dump($this->results[$child]);
        if ($overrideIndentationTemplate !== false) 
            return preg_replace(StringTools::regexpify($pattern), trim(StringTools::indentLinesToMatchOther($pattern, $overrideIndentationTemplate, $this->results[$child], 0)), $this->results[$parent]).PHP_EOL;
        else return preg_replace(StringTools::regexpify($pattern), trim(StringTools::indentLinesToMatchOther($pattern, $this->templates[$parent], $this->results[$child], 0)), $this->results[$parent]).PHP_EOL;
    }
    
    // writes down the tree of templates including each other and saves a note about the ones that are iterative
    public function makeTree($makePathInstance = null, $scope, $depth = 0, $parentIterative = false, $parent = '')
    {
        $return = array();
        if(isset($this->templates[$scope]))
        {
            ++$depth;
            preg_match_all('/<<(?<name>\w+)>>/', $this->templates[$scope], $matches);
            preg_match_all('/@@(?<name>\w+)@@/', $this->templates[$scope], $matchesDynamic);
            $matches = array_merge_recursive($matches, $matchesDynamic);
            
            preg_match_all('/<!<(?<name>\w+)>!>/', $this->templates[$scope], $matchesIterative);
            /*
            preg_match_all('/@!@(?<name>\w+)@!@/', $this->templates[$scope], $matchesIterativeDynamic);
            $matchesIterative = array_merge_recursive($matchesIterative, $matchesIterativeDynamic);
            */
            
            $parentDisplay = (strlen($parent)>0) ? LogCLI::GREEN.$parent.LogCLI::RESET.' => ' : null;
            
            if(isset($makePathInstance)) $makePathInstance->begin($scope);
            
            if(!empty($matches['name']))
            {
                foreach($matches['name'] as $match)
                {
                    //if(isset($makePathInstance)) $makePathInstance->begin($match);
                    LogCLI::Message('('.$depth.') '.$parentDisplay.LogCLI::BLUE.$scope.LogCLI::RESET." => ".LogCLI::YELLOW.$match.LogCLI::RESET, 2);
                    $children = $this->makeTree($makePathInstance, $match, $depth, $parentIterative, $scope);
                    LogCLI::Result(LogCLI::INFO);
                    //if(isset($makePathInstance)) $makePathInstance->end();
                }
                $return = $matches['name'];
            }
            
            if(!empty($matchesIterative['name']))
            {
                foreach($matchesIterative['name'] as $match)
                {
                    //if(isset($makePathInstance)) $makePathInstance->begin($match);
                    LogCLI::Message('('.$depth.') '.$parentDisplay.LogCLI::BLUE.$scope.LogCLI::RESET." => ".LogCLI::YELLOW.$match.LogCLI::RESET." => ".LogCLI::RED.'[iterative]'.LogCLI::RESET, 2);

                    // some debugging:
                    /*
                    LogCLI::MessageResult('MakePathInstance = '.$makePathInstance, LogCLI::INFO);
                    LogCLI::MessageResult('Match = '.$match, LogCLI::INFO);
                    LogCLI::MessageResult('Depth = '.$depth, LogCLI::INFO);
                    LogCLI::MessageResult('Scope = '.$scope, LogCLI::INFO);
                    */

                    $children = $this->makeTree($makePathInstance, $match, $depth, true, $scope);
                    LogCLI::Result(LogCLI::INFO);
                    //if(isset($makePathInstance)) $makePathInstance->end();
                    
                    $this->settingsList[$match]['iterative'] = true;
                }
                $return = array_merge($return, $matchesIterative['name']);
            }
            if(isset($makePathInstance)) $makePathInstance->end();  // $scope
            
            if(isset($return)) return $return;
        }
        else
        {
            LogCLI::Fail('No matching stem for: '.$scope.'! Please verify your configuration file.');
        }
        return array();
    }

    /**
     * similar to makeTree, but also parses the tree and puts the actual elements in place
     *
     * @param string $scope
     * @param bool $parseResult
     * @param int $depth
     * @param bool $parentIterative
     * @param string $parent
     * @return array
     */
    public function parseTree($scope, $parseResult = false, $depth = 0, $parentIterative = false, $parent = '') // = 'root'
    {
        $parentDisplay = null;
        ++$depth;
        $return = array();

        if(!empty($parent))
        {
            $fullScopePath = $parent.'/'.$scope;
            $parentDisplay = LogCLI::GREEN.$parent.LogCLI::RESET.' => ';
        }
        else
        {
            $fullScopePath = $scope;
        }
        
        if($parseResult === false)
            $pregSubject = &$this->templates[$scope];
        elseif(isset($this->results[$scope]))
            $pregSubject = &$this->results[$scope];
        else return $return;

        preg_match_all('/<<(?<name>\w+)>>/', $pregSubject, $matches);
        preg_match_all('/<!<(?<name>\w+)>!>/', $pregSubject, $matchesIterative);

        if(!empty($matches['name']))
        {
            foreach($matches['name'] as $match)
            {
                if(!$this->patterns[$match])
                    $this->patterns[$match] = '<<'.$match.'>>';
                
                LogCLI::Message('('.$depth.') Non-iterative match: '.$parentDisplay.LogCLI::BLUE.$scope.LogCLI::RESET." => ".LogCLI::YELLOW.$match.LogCLI::RESET, 2);
                $children = $this->parseTree($match, false, $depth, $parentIterative, $scope);

                LogCLI::Result(LogCLI::INFO);
                
                if($parentIterative === true)
                {
                    $path = $scope.'/'.$match;
                    if(!$this->parsers[$match]->loadConfiguration(&$this->config, $path))
                    {
                        LogCLI::MessageResult('No configuration data for: '.LogCLI::BLUE.$scope.LogCLI::RESET, LogCLI::INFO);
                    }
                    LogCLI::Message("Ordering parsing of: ".LogCLI::BLUE.$path.LogCLI::RESET." at depth = $depth", 3);
                    LogCLI::MessageResult('Parent scope is iterative: '.LogCLI::BLUE.$scope.LogCLI::RESET, LogCLI::INFO);

                    $this->results[$path] = $this->parsers[$match]->getParsed();

                    LogCLI::Result(LogCLI::INFO);
                }
                elseif(!isset($this->results[$match]))
                {
                    $this->parsers[$match]->loadConfiguration(&$this->config, $match);

                    LogCLI::Message("Ordering parsing of: ".LogCLI::BLUE.$match.LogCLI::RESET." at depth = $depth", 3);
                    LogCLI::MessageResult('Parent scope is not iterative: '.LogCLI::BLUE.$scope.LogCLI::RESET, LogCLI::INFO);

                    $this->results[$match] = $this->parsers[$match]->getParsed();

                    foreach($children as $child)
                    {
                        //LogCLI::MessageResult("Inserting: $child to ".LogCLI::BLUE.$match.LogCLI::RESET." at depth = $depth", 5);
                        $this->results[$match] = $this->insertScope($child, $match, $this->patterns[$child]);
                    }
                    LogCLI::Result(LogCLI::INFO);
                }
            }
            $return = $matches['name'];
        }
        
        if(!empty($matchesIterative['name']))
        {
            foreach($matchesIterative['name'] as $match)
            {
                $path = $fullScopePath.'/'.$match;

                $this->patterns[$match] = '<!<'.$match.'>!>';
                $this->results[$match] = null;
                $this->results[$path] = null;

                /*
                 * recursive iterative matching
                 */

                //$currentConfig = $this->parsers[$match]->loadConfiguration(&$this->config, $path, $match);
                $currentConfig =& ArrayTools::accessArrayElementByPath(&$this->config, $path);
                //$parentConfig = ArrayTools::accessArrayElementByPath(&$this->config, $fullScopePath);

                // translation:
                if(!ArrayTools::isIterativeScope($currentConfig))
                {
                    LogCLI::MessageResult('Non-iterative format, translating path: '.LogCLI::BLUE.$path.LogCLI::RESET, LogCLI::INFO);
                    //ArrayTools::replaceArrayElementByPath(&$this->config, $path, ArrayTools::translateToIterativeScope($match, $currentConfig));
                    $currentConfig = ArrayTools::translateToIterativeScope($match, $currentConfig);
                }

                foreach(array_keys($currentConfig) as $id)
                {
                    LogCLI::Message('('.$depth.') '.$parentDisplay.LogCLI::BLUE.$scope.LogCLI::RESET." => ".LogCLI::YELLOW.$match.LogCLI::RESET." => [".LogCLI::GREEN_LIGHT.$id.LogCLI::RESET."]", 2);
                    
                    LogCLI::MessageResult("Ordering parsing of: ".LogCLI::BLUE."$match".LogCLI::RESET." at depth = $depth", 3);

                    $iterativePath = "${fullScopePath}/${match}/${id}";

                    $this->parsers[$match]->loadConfiguration(&$this->config, $iterativePath);
                    // at this moment it's still the same configuration, just cut out

                    $this->results[$iterativePath] = $this->parsers[$match]->getParsed();

                    /**
                     * let's parse all the children
                     */
                    LogCLI::Message('('.$depth.') '.$parentDisplay.LogCLI::BLUE.$scope.LogCLI::RESET." => ".LogCLI::YELLOW.$match.LogCLI::RESET." => ".LogCLI::RED.'[iterative]'.LogCLI::RESET, 2);

                    $children = $this->parseTree($iterativePath, true, $depth, true, '');

                    //LogCLI::MessageResult("Child's path: ".LogCLI::YELLOW.$iterativePath.LogCLI::RESET, 7);

                    foreach($children as $child)
                    {
                        $this->results[$iterativePath] = $this->insertScope("${iterativePath}/${child}", $iterativePath, $this->patterns[$child], $this->results[$iterativePath]);
                    }

                    LogCLI::MessageResult("Adding up the iterative scope values: ".LogCLI::YELLOW.$iterativePath.LogCLI::RESET, 5);
                    $this->results[$path] .= trim($this->results[$iterativePath]).PHP_EOL;

                    LogCLI::Result(LogCLI::INFO); // end children parse

                    LogCLI::Result(LogCLI::INFO); // end iteration
                }

                $this->results[$match] = rtrim($this->results[$path]);
            }
            $return = array_merge($return, $matchesIterative['name']);
        }
        
        if($depth == 1)
        {
            $this->parsers[$scope]->loadConfiguration(&$this->config, $scope);
            $this->results[$scope] = $this->parsers[$scope]->getParsed();
            $all_matches = array_merge_recursive($matches, $matchesIterative);
            if(!empty($all_matches['name']))
            {
                foreach($all_matches['name'] as $match)
                {
                    //LogCLI::MessageResult("Inserting: $match to ".LogCLI::BLUE.$scope.LogCLI::RESET, 5);
                    $this->results[$scope] = $this->insertScope($match, $scope);
                }
            }
        }
        
        return $return;
    }
}
