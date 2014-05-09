<?php
/**
 * Created by Bazyli Brzoska
 * Date: 27.02.11
 * Time: 21:24
 */

require_once '../autoload.php';

use Tools\LogCLI;
use Tools\ParseTools;

LogCLI::SetVerboseLevel(10);

class SuperStack
{
    public $superStack = array();
    protected $level = -1;
    protected $currentPointer;
    protected $parentID = array();
    
    public function __construct()
    {
        $this->Begin();
    }
    public function Add($value)
    {
        $this->currentPointer[] = $value;
    }
    public function Begin()
    {
        $this->level++;
        $next = count($this->currentPointer);
        $this->currentPointer[$next] = array();
        
        $this->parentID[$this->level] = count($this->superStack);
        $this->superStack[] = &$this->currentPointer[$next];
        $this->currentPointer = &$this->currentPointer[$next];
        
    }
    public function End()
    {
        $this->level--;
        $this->currentPointer = &$this->superStack[$this->parentID[$this->level]];
    }
    public function GetStack()
    {
        return $this->superStack[0];
    }
    public function GetLevel()
    {
        return $this->level;
    }
}

function StackUp($format, $open = '[', $close = ']')
{
    $stack = new SuperStack;
    /*
    $head = strpos($format, $open);
    $stack->Add(substr($format, 0, $head));
    $stack->Begin();
    */
    
    $head = -strlen($open);
    do $head = StackRecursiveHelper($stack, $format, $open, $close, $head);
    while($head !== false);
    
    return $stack->GetStack();
}

function StackRecursiveHelper($stack, $format, $open, $close, $head)
{
    //$head++;
    $head += strlen($open);
    
    $headClose = strpos($format, $close, $head);
    $headOpen = strpos($format, $open, $head);
    
    if($headClose === false)
    {
        $toAdd = substr($format, $head);
        if(!empty($toAdd))
            $stack->Add($toAdd);
        
        // we are at the bottom level already
    }
    elseif($headClose < $headOpen || $headOpen === false)
    {
        $toAdd = substr($format, $head, $headClose-$head);
        if(!empty($toAdd)) 
            $stack->Add($toAdd);
        
        $stack->End();
    }
    else
    {
        $toAdd = substr($format, $head, $headOpen-$head);
        if(!empty($toAdd)) 
            $stack->Add($toAdd);
            
        $stack->Begin();

        StackRecursiveHelper($stack, $format, $open, $close, $headOpen);
    }
    return $headClose;
}

function r_implode( $glue, $pieces ) 
{ 
  foreach( $pieces as $r_pieces ) 
  { 
    if( is_array( $r_pieces ) ) 
    { 
      $retVal[] = r_implode( $glue, $r_pieces ); 
    } 
    else 
    { 
      $retVal[] = $r_pieces; 
    } 
  } 
  return implode( $glue, $retVal ); 
}

function parseArray(array $formatArray, array &$args, $pattern = '/%\(([a-zA-Z_]\w*)\)/')
{
    return current(parseRecursive(array($formatArray), $args, $pattern));
}

function parseRecursive(array $formatArray, array &$args, $pattern = '/%\(([a-zA-Z_]\w*)\)/')
{
    foreach($formatArray as $id => &$format)
    {
        if(is_array($format))
        {
            $returnValue = parseRecursive($format, $args, $pattern);
            if(is_array($returnValue) && array_key_exists('unset', $returnValue))
            {
                unset($format[$returnValue['unset']]);
            }
            elseif($returnValue === false)
            {
                return array('unset' => $id);
            }
            
        }
        else
        {
            if(parse(&$format, &$args, $pattern) === false) 
            {
                return false;
            }
        }
    }
    return $formatArray;
}

function parse($format, array &$args, $pattern = '/%\(([a-zA-Z_]\w*)\)/', $ifNoMatchReturnFalse = true)
{
    preg_match_all($pattern, $format, $matches, PREG_SET_ORDER);
    foreach($matches as $match)
    {
        if (array_key_exists($match[1], $args))
        {
            //$format = preg_replace($pattern, $args[$match[1]], $format);
            if($args[$match[1]] === false) return false;
            $format = str_replace($match[0], $args[$match[1]], $format);
        }
        else //not found, replace with null:
        {
            if($ifNoMatchReturnFalse === true) return false;
            else $format = str_replace($match[0], null, $format);
        }
    }
    return $format;
}

//$input = "poziom 0 [%(gzip_buffers) [poziom 2[poziom 3]%(gzip_buffers_inny)] poziom 1] poziom 0 [znowu poziom 1] 0 [poziom 1 %(dupeczka)[znowu poziom 2 [znowu poziom 3]]] koncowkaLVL0";
//$formatArray = StackUp($input);

$listFormat = array(
    "gzip_buffers" => 40,
    "gzip_yeah" => 'yeah!'
);
$toFormat = '[[gzip_buffers %(gzip_buffers)[[ %(gzip_yeah)]]; this should still be here]]';
$formatArray = StackUp($toFormat, '[[', ']]');

$parsed = parseArray($formatArray, $listFormat);
echo r_implode(null, $parsed);

//var_dump($formatArray);
//var_dump($parsed);

//$out = parse('kapucha %(gzip_buffers)s %(gzip_buffers_inny)s', $listFormat);
//echo $out;

//unset($output[2]);
//echo r_implode( '', $output ) . "\n"; 

//$input = "poziom 0 [poziom 1 [poziom 2[poziom 3]poziom 2] poziom 1] poziom 0 [jeszcze inno] [innoLVL1 [wnetrzeLVL2] zewnetrzeLVL1] i zero";
//$input = "poziom 0 [poziom 1 [poziom 2 [poziom 3] 2 [inny poziom 3] poziom 2] 1 [alt2 [trzy] poziom 2 alt [trzy alt] alt2] poziom 1] poziom 0";
//$input = "0[1]0[1]0[1]0[1[2]1]";




//$toFormat = '[[gzip_buffers %(gzip_buffers_num)s %(gzip_buffers_size)s;]]';
//$toFormat = '[[ gzip_buffers %(gzip_buffers)s[[ %(gzip_buffers_s)s]]; this should here ]]';
$toFormat = ' [[a[[balabolka]][[c[[xawery]]C]]AB]] [[lolzS [[masakra]] lolzZ]] [[d[[e]]f]] tu tez';


//ParseTools::sprintfnnew($toFormat, $listFormat);
//ParseTools::RemoveBracket($toFormat, 1);
//ParseTools::StripTag($toFormat);


//$input = "plain [] wt []deep [] dee[]kozmos[/]per[/] [/] deep [/] plain [] some more here [/]";

//$input = "[[test [[to ta]] la [[serc]] lo [[sert  ]] ujemy]]";

/*
function parseTagsRecursive($input)
{

    $regex = '#\[]((?:[^[]|\[(?!/?])|(?R))+)\[/]#';
    //$regex = '# \[ \[ ( (?: [^[] | ]] | (?R) ) +) \[/]#';

    //$regex = '#\[((?:[^[]|]|(?R))+)]#';
    
    if (is_array($input)) {
        var_dump($input);
        ($input = $input[1]);
    }

    return( preg_replace_callback($regex, 'parseTagsRecursive', $input) );
}

$output = parseTagsRecursive($input);
*/

