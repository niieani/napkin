#!/usr/bin/env php
<?php
//require_once 'Parser/ConfigParser.php';
require_once __DIR__.'/autoload.php';

use Tools\LogCLI;
use ConfigParser\ConfigParser;
use ConfigScopes\ConfigScopes;
use Tools\FileOperation;
//require_once 'Parser/ConfigParser/Action/IPPort.php';

//require_once 'Console/CommandLine.php';
//$actionInfo = \Console_CommandLine::$actions;
//var_dump($actionInfo);

LogCLI::SetVerboseLevel(5);

$config['root'] = array('user' => 'testowy', 'group' => 'group');
$config['events'] = array('connections' => 1024, 'multi_accept' => false);
//$config['http'] = array('user' => 'testowy', 'group' => 'group');
//$config['server'][0][] = array('domain'=>'koma.net');
$config['server'][0] = array('domain'=>'komalol.net');
$config['server'][0]['listen'][0] = array('ip'=>'10.0.0.1', 'port'=>'80');
$config['server'][0]['listen'][1] = array('ip'=>'10.0.0.1', 'port'=>'81');
$config['server'][0]['listen'][2] = array('ip'=>'10.0.0.1', 'port'=>'81');
$config['server'][1] = array('domain'=>'moma.com');
$config['server'][1]['listen'][0] = array('ip'=>'192.168.0.1', 'port'=>'80');
$config['server'][1]['listen'][1] = array('ip'=>'192.168.0.2', 'port'=>'81');
$config['server'][2] = array('domain'=>'jajco.com');
FileOperation::ToYAMLFile($config, true);

/*$template = '
user [[%(user)s]][[ %(group)s]];
listen [[%(listen)s]][[ %(listen_options)s]];';
*/

/*
<<multiple files>>
[[replace string]]
*/

//recursive


$template = array();

$template['root'] = <<< EOT
[[user %(user)s[[ %(group)s]];]]
events
{
    <<events>>
}
<<http>>
EOT;

$template['http'] = <<< EOT
http
{
    <!<server>!>
}
EOT;

$template['events'] = <<< EOT
[[worker_connections %(connections)s;]]
[[use %(use)s;]]
[[multi_accept %(multi_accept)s;]]
EOT;

$template['server'] = <<< EOT
server
{
    <<listen>>
    [[server_name %(domain)s;]]
}
EOT;

$template['listen'] = <<< EOT
listen [[%(listen)s]][[ %(listen_options)s]];
EOT;


/*

    wrzucić całość do jednej klasy a potem zrobić autoloader na zasadzie CommandLine
    i tak do każdej aplikacji

*/
$parsers = array();
/*
 * NGINX ROOT SCOPE PARSER
 */
$parsers['root'] = new ConfigParser(array(
    'name'        => 'nginx_root',
    'description' => 'nginx root',
    'version'     => '0.9',
    'template'    => &$template['root'],
    'configuration' => &$config
));

$parsers['root']->addSetting('user', array(
    'path'        => 'root/user',
    'action'      => 'StoreStringOrFalse',
    'default'     => 'www-data',
    'description' => 'user that runs nginx'
));

$parsers['root']->addSetting('group', array(
    'path'        => 'root/group',
    'action'      => 'StoreStringOrFalse',
    'default'     => 'www-data',
    'description' => 'group that runs nginx'
));


/*
 * NGINX EVENTS SCOPE PARSER
 */
$parsers['events'] = new ConfigParser(array(
    'name'        => 'nginx_events',
    'description' => 'nginx events',
    'version'     => '0.9',
    'template'    => &$template['events'],
    'configuration' => null
));
$parsers['events']->addSetting('connections', array(
    'path'        => 'connections',
    'action'      => 'StoreInt',
    'default'     => 4096
));
$parsers['events']->addSetting('use', array(
    'path'        => 'use',
    'action'      => 'StoreStringOrFalse',
    'default'     => 'epoll'
));
$parsers['events']->addSetting('multi_accept', array(
    'path'        => 'multi_accept',
    'action'      => 'StoreOnOff',
    'default'     => true
));

/*
 * NGINX HTTP SCOPE PARSER
 */
$parsers['http'] = new ConfigParser(array(
    'name'        => 'nginx_http',
    'description' => 'nginx http',
    'version'     => '0.9',
    'template'    => &$template['http'],
    'configuration' => &$config
));

/*
 * NGINX SERVER SCOPE PARSER
 */
$parsers['server'] = new ConfigParser(array(
    'name'        => 'nginx_server',
    'description' => 'nginx server',
    'version'     => '0.9',
    'template'    => &$template['server'],
    'configuration' => null
));

$parsers['server']->addSetting('domain', array(
    'path'        => 'domain',
    'action'      => 'StoreStringOrFalse',
    'default'     => null,
    'description' => 'listen options'
));

/*
 * NGINX SERVER/LISTEN SCOPE PARSER
 */

$parsers['listen'] = new ConfigParser(array(
    'name'        => 'nginx_listen',
    'description' => 'nginx listen',
    'version'     => '0.9',
    'template'    => &$template['listen'],
    'configuration' => null
));

$parsers['listen']->addSetting('listen', array(
    'path'        => array('ip'=>'ip','port'=>'port'),
    'default'     => array('ip'=>null,'port'=>'80'),
    'required_one'=> array('ip','port'),
    'action'      => 'IPPort',
    'description' => 'IP and port'
));

$parsers['listen']->addSetting('listen_options', array(
    'path'        => 'listen_options',
    'action'      => 'StoreStringOrFalse',
    'default'     => '',
    'description' => 'listen options'
));

$configScopes = new ConfigScopes(&$parsers, &$template, &$config);
$parsedFile = $configScopes->parseTemplateRecursively('root');
echo $parsedFile;

/*
function indentLinesToMatchOther($likeWhat, $likeWhere, $content, $skipLines = 0, $whereToStop = null)
{
    foreach(preg_split("/(\r?\n)/", $likeWhere) as $line)
    {
        if(!isset($indentationCharsNum) || $indentationCharsNum === false)
        {
            //var_dump($line);
            if(($indentationCharsNum = strpos($line, $likeWhat)) !== false)
            {
                //var_dump($line);
                //var_dump($indentationCharsNum);
                $indentationString = substr($line, 0, $indentationCharsNum);
                break;
            }
        }
    }
    $output = null;
    foreach(preg_split("/(\r?\n)/", $content) as $line)
    {
        if($whereToStop = null || strpos($line, $whereToStop) === false)
        {
            if(!isset($indentationCharsNum) || $indentationCharsNum === false || $skipLines > 0)
            {
                $output .= $line.PHP_EOL;
                $skipLines--;
            }
            else
            {
                $output .= $indentationString.$line.PHP_EOL;
            }
        }
        else
        {
            $output .= $line.PHP_EOL;
        }
    }
    return $output;
}

$parsed = array();
$patterns = array();
$results = array();
function regexpify($string)
{
    return '/'.$string.'/';
}

foreach(array_keys($template) as $name)
{
    $patterns[$name] = '<<'.$name.'>>';
}

function parseTree($scope = 'root', $depth=0, $parentIterative = false, $parent = '')
{
    global $template;
    global $config;
    global $parsers;
    global $results;
    global $patterns;
    
    $depth++;
    preg_match_all('/<<(?<name>\w+)>>/', $template[$scope], $matches);
    preg_match_all('/<!<(?<name>\w+)>!>/', $template[$scope], $matchesIterative);
    //echo "depth: $depth".PHP_EOL;
    //print_r($matches);
    $parentDisplay = (strlen($parent)>0) ? LogCLI::GREEN.$parent.LogCLI::RESET." => " : null;
    if(!empty($matches['name']))
    {
        foreach($matches['name'] as $match)
        {
            $patterns[$match] = '<<'.$match.'>>';
            
            LogCLI::Message('('.$depth.') '.$parentDisplay.LogCLI::BLUE.$scope.LogCLI::RESET." => ".LogCLI::YELLOW.$match.LogCLI::RESET, 2);
            $children = parseTree($match, $depth, $parentIterative, $scope);
            LogCLI::Result(LogCLI::INFO);
            //echo $match.PHP_EOL;
            
            if($parentIterative === true)
            {
                foreach($config[$scope] as $id => &$iterative)
                {
                    $parsers[$match]->configuration = &$iterative[$match];

                    LogCLI::MessageResult("Ordering parsing of: ".LogCLI::BLUE."${scope}_${id}_${match}".LogCLI::RESET." at depth = $depth", 3);
                    $parse = $parsers[$match]->parse();
                    //$scope_id_match = $scope.'_'.$id.'_'.$match;
                    $results["${scope}_${id}_${match}"] = trim($parse->parsed);
                    //$results[$scope_id_match] = trim($parse->parsed);
                }
            }
            elseif(!isset($results[$match]))
            {
                LogCLI::MessageResult("Ordering parsing of: ".LogCLI::BLUE."${match}".LogCLI::RESET." at depth = $depth", 3);
                $results[$match] = $parsers[$match]->parse();
                $results[$match] = trim($results[$match]->parsed);
                //var_dump($children);
                foreach($children as $child)
                {
                    //LogCLI::MessageResult("Inserting: $child to ".LogCLI::BLUE.$match.LogCLI::RESET." at depth = $depth", 5);
                    $results[$match] = insertScope($child, $match, $patterns[$child]);
                }
            }
        }
        //if (strlen($parent)>0)
        //{
        //    LogCLI::MessageResult("Inserting: $scope to ".LogCLI::BLUE.$parent.LogCLI::RESET." at depth = $depth", 5);
        //    $results[$scope] = insertScope($scope, $parent);
        //}
        $return = $matches['name'];
    }
    
    if(!empty($matchesIterative['name']))
    {
        foreach($matchesIterative['name'] as $match)
        {
            $patterns[$match] = '<!<'.$match.'>!>';
            LogCLI::Message('('.$depth.') '.$parentDisplay.LogCLI::BLUE.$scope.LogCLI::RESET." => ".LogCLI::YELLOW.$match.LogCLI::RESET." => ".LogCLI::RED.'[iterative]'.LogCLI::RESET, 2);
            $children = parseTree($match, $depth, true, $scope);
            LogCLI::Result(LogCLI::INFO);
            //echo $match.PHP_EOL;
            
            $results[$match] = null;
            foreach($config[$match] as $id => &$iterative)
            {
                LogCLI::Message('('.$depth.') '.$parentDisplay.LogCLI::BLUE.$scope.LogCLI::RESET." => ".LogCLI::YELLOW.$match.LogCLI::RESET." => [".LogCLI::GREEN_LIGHT.$id.LogCLI::RESET."]", 2);
                
                LogCLI::MessageResult("Ordering parsing of: ".LogCLI::BLUE."${match}".LogCLI::RESET." at depth = $depth", 3);
                $parsers[$match]->configuration = &$iterative;
                $parse = $parsers[$match]->parse();
                //$server_parse = trim($server_parse->parsed);
                //$match_id = $match.'_'.$id;
                $results["${match}_${id}"] = trim($parse->parsed);
                //$results[$match_id] = trim($parse->parsed);
                
                foreach($children as $child)
                {
                    //$match_id_child = $match.'_'.$id.'_'.$child;
                    //LogCLI::MessageResult("Inserting: ${match}_${id}_${child} to ".LogCLI::BLUE."${match}_${id}".LogCLI::RESET." at depth = $depth", 5);
                    $results[$match] .= insertScope("${match}_${id}_${child}", "${match}_${id}", $patterns[$child], $results["${match}_${id}"]);
                    //$results[$match] .= insertScope($match_id_child, $match_id, $patterns[$child], $results[$match_id]);
                }
                LogCLI::Result(LogCLI::INFO);
            }
        }
        //if (strlen($parent)>0)
        //{
        //    LogCLI::MessageResult("Inserting: $scope to ".LogCLI::BLUE.$parent.LogCLI::RESET." at depth = $depth", 5);
        //    $results[$scope] = insertScope($scope, $parent);
        //}
        $return = $matchesIterative['name'];
    }
    
    if($depth == 1)
    {
        //LogCLI::MessageResult("Testing at depth = $depth", 5);
        
        $parse = $parsers[$scope]->parse();
        $results[$scope] = trim($parse->parsed);
        
        //preg_match_all('/<<(?<name>\w+)>>/', $template[$scope], $matches);
        //preg_match_all('/<!<(?<name>\w+)>!>/', $template[$scope], $matchesIterative);
        $all_matches = array_merge_recursive($matches, $matchesIterative);
        if(!empty($all_matches['name']))
        {
            foreach($all_matches['name'] as $match)
            {
                //LogCLI::MessageResult("Inserting: $match to ".LogCLI::BLUE.$scope.LogCLI::RESET, 5);
                $results[$scope] = insertScope($match, $scope);
            }
        }
        
    }
    
    if(isset($return)) return $return;
    return array();
}

parseTree();
//insertOne('root');
echo $results['root'];
//var_dump($results);

function insertScope($child, $parent, $pattern = null, $overrideIndentationTemplate = false)
{
    global $patterns, $template, $results;
    
    
    if (!isset($pattern)) 
    {
        $pattern = &$patterns[$child];
    }
    
    LogCLI::MessageResult("Inserting: ".LogCLI::BLUE.$child.LogCLI::RESET." => ".LogCLI::GREEN.$parent.LogCLI::RESET." (will replace ".LogCLI::YELLOW.$pattern.LogCLI::RESET.")", 3);
    
    if (!isset($results[$parent])) 
    {
        $proper_parent = explode('_', $parent);
        //var_dump($proper_parent);
        $proper_parent = (isset($proper_parent[2])) ? $proper_parent[2] : $proper_parent[0];
        //$results[$parent] = $template[$parent];
        $results[$parent] = $template[$proper_parent];
    }
    if (!isset($results[$child])) 
    {
        $proper_child = explode('_', $child);
        $proper_child = (isset($proper_child[2])) ? $proper_child[2] : $proper_child[0];
        //$results[$child] = $template[$child];
        $results[$child] = $template[$proper_child];
    }
    if ($overrideIndentationTemplate !== false) 
        return preg_replace(regexpify($pattern), trim(indentLinesToMatchOther($pattern, $overrideIndentationTemplate, $results[$child], 0)), $results[$parent]).PHP_EOL;
    else return preg_replace(regexpify($pattern), trim(indentLinesToMatchOther($pattern, $template[$parent], $results[$child], 0)), $results[$parent]).PHP_EOL;

//        return preg_replace(regexpify($patterns[$child]), trim(indentLinesToMatchOther($patterns[$child], $overrideIndentationTemplate, $results[$child], 0)), $results[$parent]).PHP_EOL;
//    else return preg_replace(regexpify($patterns[$child]), trim(indentLinesToMatchOther($patterns[$child], $template[$parent], $results[$child], 0)), $results[$parent]).PHP_EOL;
}


foreach($parsers as $parsername => $parser)
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

*/

/*
foreach($template as $key=>$temp)
{
    $results[$key] = $temp;
}
*/

/*
$results['server'] = null;
foreach($config['server'] as $id => &$server)
{
    $parsers['listen']->configuration = &$server['listen'];
    $listen_parse = $parsers['listen']->parse();
    //$listen_parse = 
    $results['listen'] = trim($listen_parse->parsed);
    //$results['listen'] = true;
    
    //$parsers['server']->configuration = array_merge($parsers['server']->configuration, $listen
    
    $parsers['server']->configuration = &$server;
    $server_parse = $parsers['server']->parse();
    //$server_parse = trim($server_parse->parsed);
    $results["server$id"] = trim($server_parse->parsed);
    
    //$results['server'] .= preg_replace('/<<listen>>/', trim(indentLinesToMatchOther('<<listen>>', $server_parse, $listen_parse, 1)), $server_parse).PHP_EOL;
    $results['server'] .= insertScope('listen', "server$id", $results["server$id"]);
}
*/




//var_dump($results);
//echo $results['server'];

/*
foreach($results['server'] as $result)
{
    echo $result.PHP_EOL;
}
*/

//$results['root'] = $parsers['root']->parse();
//$results['listen'] = $parsers['listen']->parse();

/*
function insertOne($scope)
{
    global $results;
    global $template;
    global $parsers;
    global $results;
    
    $parse = $parsers[$scope]->parse();
    $results[$scope] = trim($parse->parsed);
    
    preg_match_all('/<<(?<name>\w+)>>/', $template[$scope], $matches);
    preg_match_all('/<!<(?<name>\w+)>!>/', $template[$scope], $matchesIterative);
    $all_matches = array_merge_recursive($matches, $matchesIterative);
    if(!empty($all_matches['name']))
    {
        foreach($all_matches['name'] as $match)
        {
            LogCLI::MessageResult("Inserting: $match to ".LogCLI::BLUE.$scope.LogCLI::RESET, 5);
            $results[$scope] = insertScope($match, $scope);
        }
    }
}
*/
/*
function insertTree()
{
    
}
foreach(array_reverse(&$parsers) as $name => $parser)
{
    if(!isset($results[$name]))
    {
        $results[$name] = $parser->parse();
        $results[$name] = trim($results[$name]->parsed);
    }
}
*/

//echo 
//$results['http'] = preg_replace(regexpify($patterns['server']), trim(indentLinesToMatchOther($patterns['server'], $template['http'], $results['server'], 0)), $template['http']).PHP_EOL;

//$results['http'] = insertScope('server', 'http');
//$results['root'] = insertScope('events', 'root');
//$results['root'] = insertScope('http', 'root');
/*
function parseTagsRecursive($input)
{
    $regex = '#\[]((?:[^[]|\[(?!/?])|(?R))+)\[/]#';

    if (is_array($input)) {
        $input = 'bum'.$input[1].'/bum';
    }

    return preg_replace_callback($regex, 'parseTagsRecursive', $input);
}


$input = "plain [] deep [] deeper [/] deep [/] plain";

function parseTagsRecursive($input)
{
    $regex = '#\[]((?:[^[]|\[(?!/?])|(?R))+)\[/]#';

    if (is_array($input)) {
        $input = 'bum'.$input[1].'/bum';
    }

    return preg_replace_callback($regex, 'parseTagsRecursive', $input);
}

$output = parseTagsRecursive($input);

echo $output;
*/