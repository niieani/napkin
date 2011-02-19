<?php
namespace ConfigScopes;

use \Tools\LogCLI;
use \ConfigParser\ConfigParser;
use \Tools\FileOperation;
use \Tools\StringTools;

class ConfigScopes
{
    public $config = null;
    protected $templates = array();
    protected $parsers = array();
    protected $patterns = array();
    protected $results = array();
    protected $currentScope;
    
    public function __construct(array $parsers, array $templates, array $config = null)
    {
        $this->templates = $templates;
        $this->parsers = $parsers;
        if($config !== null) $this->config = $config;
        
        foreach(array_keys($this->templates) as $name)
        {
            $this->patterns[$name] = '<<'.$name.'>>';
        }
    }
    
    //returns parsed file
    public function parseTemplateRecursively($scope)
    {
        if(isset($this->templates[$scope]) && isset($this->parsers[$scope]))
        {
            $this->currentScope = $scope;
            $this->parseTree($scope);
            return $this->results[$scope];
        }
        else return false;
    }
    
    public function listSettings()
    {
        foreach($this->parsers as $parsername => $parser)
        {
            foreach($parser->options as $option)
            {
                if(is_array($option->path))
                {
                    foreach($option->path as $path)
                    {
                        LogCLI::MessageResult(LogCLI::BLUE.$parsername.LogCLI::RESET.' => Found multi-option => '.LogCLI::BLUE.$path.LogCLI::RESET, 5, LogCLI::INFO);
                        $list[] = $path;
                    }
                }
                else
                {
                    LogCLI::MessageResult(LogCLI::BLUE.$parsername.LogCLI::RESET.' => Found option => '.LogCLI::BLUE.$option->path.LogCLI::RESET, 5, LogCLI::INFO);
                    $list[] = $option->path;
                }
            }
        }
    }
    
    public function insertScope($child, $parent, $pattern = null, $overrideIndentationTemplate = false)
    {
        if (!isset($pattern)) 
        {
            $pattern = &$this->patterns[$child];
        }
        
        LogCLI::MessageResult("Inserting: ".LogCLI::BLUE.$child.LogCLI::RESET." => ".LogCLI::GREEN.$parent.LogCLI::RESET." (will replace ".LogCLI::YELLOW.$pattern.LogCLI::RESET.")", 3);
        
        if (!isset($this->results[$parent])) 
        {
            $proper_parent = explode('_', $parent);
            $proper_parent = (isset($proper_parent[2])) ? $proper_parent[2] : $proper_parent[0];
            $this->results[$parent] = $template[$proper_parent];
        }
        if (!isset($this->results[$child])) 
        {
            $proper_child = explode('_', $child);
            $proper_child = (isset($proper_child[2])) ? $proper_child[2] : $proper_child[0];
            $this->results[$child] = $template[$proper_child];
        }
        if ($overrideIndentationTemplate !== false) 
            return preg_replace(StringTools::regexpify($pattern), trim(StringTools::indentLinesToMatchOther($pattern, $overrideIndentationTemplate, $this->results[$child], 0)), $this->results[$parent]).PHP_EOL;
        else return preg_replace(StringTools::regexpify($pattern), trim(StringTools::indentLinesToMatchOther($pattern, $this->templates[$parent], $this->results[$child], 0)), $this->results[$parent]).PHP_EOL;
    }
    
    public function parseTree($scope = 'root', $depth = 0, $parentIterative = false, $parent = '')
    {
        $depth++;
        preg_match_all('/<<(?<name>\w+)>>/', $this->templates[$scope], $matches);
        preg_match_all('/<!<(?<name>\w+)>!>/', $this->templates[$scope], $matchesIterative);
        
        $parentDisplay = (strlen($parent)>0) ? LogCLI::GREEN.$parent.LogCLI::RESET.' => ' : null;
        
        if(!empty($matches['name']))
        {
            foreach($matches['name'] as $match)
            {
                $this->patterns[$match] = '<<'.$match.'>>';
                
                LogCLI::Message('('.$depth.') '.$parentDisplay.LogCLI::BLUE.$scope.LogCLI::RESET." => ".LogCLI::YELLOW.$match.LogCLI::RESET, 2);
                $children = $this->parseTree($match, $depth, $parentIterative, $scope);
                LogCLI::Result(LogCLI::INFO);
                
                if($parentIterative === true)
                {
                    foreach($this->config[$scope] as $id => &$iterative)
                    {
                        $this->parsers[$match]->configuration = &$iterative[$match];
    
                        LogCLI::MessageResult("Ordering parsing of: ".LogCLI::BLUE."${scope}_${id}_${match}".LogCLI::RESET." at depth = $depth", 3);
                        
                        $parse = $this->parsers[$match]->parse();
                        $this->results["${scope}_${id}_${match}"] = trim($parse->parsed);
                    }
                }
                elseif(!isset($this->results[$match]))
                {
                    if(empty($this->parsers[$match]->configuration)) $this->parsers[$match]->configuration = &$this->config[$match];
                    
                    LogCLI::MessageResult("Ordering parsing of: ".LogCLI::BLUE."${match}".LogCLI::RESET." at depth = $depth", 3);
                    
                    $this->results[$match] = $this->parsers[$match]->parse();
                    $this->results[$match] = trim($this->results[$match]->parsed);
                    foreach($children as $child)
                    {
                        //LogCLI::MessageResult("Inserting: $child to ".LogCLI::BLUE.$match.LogCLI::RESET." at depth = $depth", 5);
                        $this->results[$match] = $this->insertScope($child, $match, $this->patterns[$child]);
                    }
                }
            }
            $return = $matches['name'];
        }
        
        if(!empty($matchesIterative['name']))
        {
            foreach($matchesIterative['name'] as $match)
            {
                $this->patterns[$match] = '<!<'.$match.'>!>';
                
                LogCLI::Message('('.$depth.') '.$parentDisplay.LogCLI::BLUE.$scope.LogCLI::RESET." => ".LogCLI::YELLOW.$match.LogCLI::RESET." => ".LogCLI::RED.'[iterative]'.LogCLI::RESET, 2);
                
                $children = $this->parseTree($match, $depth, true, $scope);
                
                LogCLI::Result(LogCLI::INFO);
                
                $this->results[$match] = null;
                foreach($this->config[$match] as $id => &$iterative)
                {
                    LogCLI::Message('('.$depth.') '.$parentDisplay.LogCLI::BLUE.$scope.LogCLI::RESET." => ".LogCLI::YELLOW.$match.LogCLI::RESET." => [".LogCLI::GREEN_LIGHT.$id.LogCLI::RESET."]", 2);
                    LogCLI::MessageResult("Ordering parsing of: ".LogCLI::BLUE."${match}".LogCLI::RESET." at depth = $depth", 3);
                    
                    $this->parsers[$match]->configuration = &$iterative;
                    $parse = $this->parsers[$match]->parse();
                    $this->results["${match}_${id}"] = trim($parse->parsed);
                    
                    foreach($children as $child)
                    {
                        $this->results[$match] .= $this->insertScope("${match}_${id}_${child}", "${match}_${id}", $this->patterns[$child], $this->results["${match}_${id}"]);
                    }
                    
                    LogCLI::Result(LogCLI::INFO);
                }
            }
            $return = $matchesIterative['name'];
        }
        
        if($depth == 1)
        {
            if(empty($this->parsers[$scope]->configuration)) $this->parsers[$scope]->configuration = &$this->config[$scope];
            
            $parse = $this->parsers[$scope]->parse();
            $this->results[$scope] = trim($parse->parsed);
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
        
        if(isset($return)) return $return;
        return array();
    }
}
