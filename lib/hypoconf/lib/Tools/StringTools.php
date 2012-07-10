<?php

namespace Tools;
use \Tools\LogCLI;

class StringTools
{
    public static function regexpify($string)
    {
        return '/'.$string.'/';
    }

    public static function multilineStringToArray($string)
    {
        $output = array();
        foreach(explode(PHP_EOL, $string) as $line)
        {
            $output[] = $line;
        }
        return $output;
    }

    public static function indentLinesToMatchOther($likeWhat, $likeWhere, $content, $skipLines = 0, $whereToStop = null)
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
        return rtrim($output);
    }
    
    public static function ReturnLastBit($path, $n = 1)
    {
        $pathElements = explode('/', $path);
        $last = count($pathElements)-$n;
        if($last >= 0)
            return $pathElements[$last];
        else return false;
        
        //return end($pathElements);
    }
    
    public static function DropLastBit($path, $skipN = 1)
    {
        if($skipN < 0)
        {
            $pathElements = explode('/', $path);
            $pathElements = array_splice($pathElements, -$skipN);
        }
        elseif($skipN > 0)
        {
            $pathElements = explode('/', $path);
            $pathElements = array_splice($pathElements, 0, count($pathElements)-$skipN);
        }
        else
        {
            $pathElements = array($path);
        }
        return implode('/', $pathElements);
    }
    
    public static function AddBit($path, $string)
    {
        return $path.'/'.$string;
    }

    /**
     * Formats an array to string with a specified delimiter,
     * or returns the string if not provided an array
     * @static
     * @param array|string $array
     * @param string $delimiter
     * @return string
     */
    public static function makeList($array, $delimiter = ' ')
    {
        return is_array($array) ? implode($delimiter, $array) : $array;
//        if(is_array($array)) return implode($delimiter, $array);
//        else return $array;
    }
    
    /*
    public static function makeList($args, $delimiter = ' ')
    {
        foreach($args as $k => $list)
        {
            //echo $list.PHP_EOL;
            if (is_array($list))
            {
                $args[$k] = self::rimplode($delimiter, $list);
                //if(is_array($args[$k])) var_dump($list);
            }
        }
        return $args;
    }
    */
    // recursive implode
    public static function rimplode( $glue, $pieces )
    {
        $retVal = array();
        foreach( $pieces as $r_pieces )
        {
            if( is_array( $r_pieces ) )
            {
                $retVal[] = self::rimplode( $glue, $r_pieces );
            }
            else
            {
                $retVal[] = $r_pieces;
            }
        }
        return implode( $glue, $retVal );
    }
    
    public static function RemoveExclamation($path)
    {
        return substr($path, 1);
    }
    
    public static function Delimit($value, $delimiter = ',')
    {
        return explode($delimiter, str_replace(' ', '', $value));
    }

    /*
    public static function HasExclamation($value, $sign = '@')
    {
        $info = array();
        if (strpos($value, $sign) === 0)
        {
          $info['exclamation'] = $sign;
          $info['text'] = substr($value, 1);
        }
        else
        {
          $info['exclamation'] = false;
          $info['text'] = $value;
        }
        return $info;
    }
    */

    /**
     * @static
     * @param string $value
     * @param array|string $signs
     * @param string|bool $delimiter    if false, don't delimit
     * @return array
     */
    public static function TypeList($value, $signs = array('@'), $delimiter = ',')
    {
        $info = array();

        if (!$delimiter) $list = array($value);
        else $list = self::Delimit($value, $delimiter);

        if(!is_array($signs)) $signs = array($signs);

        foreach ($list as $k => $v)
        {
            $firstChar = substr($v, 0, 1);
            $key = array_search($firstChar, $signs);
            if($key !== false)
            {
                $info[$k]['exclamation'] = $signs[$key];
                $info[$k]['text'] = substr($v, 1);
            }
            else
            {
                $info[$k]['exclamation'] = false;
                $info[$k]['text'] = $v;
            }
        }
        return $info;
    }

    /*
    public static function strstr_after($haystack, $needle) {
        $pos = strpos($haystack, $needle);
        if (is_int($pos)) {
            return substr($haystack, $pos + strlen($needle));
        }
        // Most likely false or null
        return $pos;
    }
    */

    function removeNewLines($content)
    {
        $output = null;
        foreach(preg_split("/(\r?\n)/", $content) as $line)
        {
            $output .= $line;
        }
        return $output;
    }

    /**
     * version of sprintf for cases where named arguments are desired (python syntax)
     *
     * with sprintf: sprintf('second: %2$s ; first: %1$s', '1st', '2nd');
     *
     * with sprintfn: sprintfn('second: %(second)s ; first: %(first)s', array(
     *  'first' => '1st',
     *  'second'=> '2nd'
     * ));
     *
     * @static
     * @param string $format sprintf format string, with any number of named arguments
     * @param array $args array of [ 'arg_name' => 'arg value', ... ] replacements to be made
     * @return string|false result of sprintf call, or bool false on error
     */
    public static function sprintfn($format, array $args = array())
    {
        LogCLI::Message('Parsing: '.LogCLI::GREEN_LIGHT.$format.LogCLI::RESET, 5);
        
        // map of argument names to their corresponding sprintf numeric argument value
        $arg_nums = array_slice(array_flip(array_keys(array(0 => 0) + $args)), 1);
    
        // find the next named argument. each search starts at the end of the previous replacement.
        for ($pos = 0; preg_match('/(?<=%)\(([a-zA-Z_]\w*)\)/', $format, $match, PREG_OFFSET_CAPTURE, $pos);) {
            $arg_pos = $match[0][1];
            $arg_len = strlen($match[0][0]);
            $arg_key = $match[1][0];

            LogCLI::Message('Parsing argument: '.LogCLI::YELLOW.$arg_key.LogCLI::RESET, 6);
            // programmer did not supply a value for the named argument found in the format string
            if (! array_key_exists($arg_key, $arg_nums)) {
                //var_dump($arg_nums);
                //$arg_nums[$arg_key] = false;
                ////array_push($arg_nums, $arg_key);
                //var_dump($arg_nums);
                LogCLI::MessageResult('Not set: '.LogCLI::YELLOW.$arg_key.LogCLI::RESET.', skipping', 4, LogCLI::INFO);
                //user_error("sprintfn(): Missing argument '${arg_key}'", E_USER_NOTICE);
                //return false;
                
                $format = substr_replace($format, $replace = '', $arg_pos-1, $arg_len+2);
            }
            else
            {
                //$posLeft = strlen($format)-strpos(strrev($format), '[[', strlen($format)-$arg_pos);
                $posLeft = strlen($format)-strpos(strrev($format), '[[', strlen($format)-$arg_pos);
                $posRight = strpos($format, ']]', $arg_pos);
                LogCLI::MessageResult('Original left position: '.LogCLI::BLUE.$arg_pos.LogCLI::RESET, 6, LogCLI::INFO);
                LogCLI::MessageResult('Found left position:    '.LogCLI::YELLOW.$posLeft.LogCLI::RESET, 6, LogCLI::INFO);
                LogCLI::MessageResult('Found right position:   '.LogCLI::YELLOW.$posRight.LogCLI::RESET, 6, LogCLI::INFO);
                
                $format = substr_replace($format, '', $posRight, 2);
                $format = substr_replace($format, '', $posLeft-2, 2);
                
                $arg_pos = $posLeft-2+($arg_pos-$posLeft);
                
                //$format = str_replace(']]', 'a', $format);
                // replace the named argument with the corresponding numeric one
                
                $format = substr_replace($format, $replace = $arg_nums[$arg_key] . '$', $arg_pos, $arg_len);
            }
            $pos = $arg_pos + strlen($replace); // skip to end of replacement for next iteration
            
            LogCLI::Result(LogCLI::INFO);
        }
        
        $format = preg_replace('#\[\[.*?\]\]#', '', $format);
        
        LogCLI::Result(LogCLI::INFO);
        return vsprintf($format, array_values($args));
    }


    /*
     * DEPRACATED VERSION
    public static function typeList($value=false, $sign = '@', $Delimit = ',')
    {
        if (!$Delimit) $list = array($value);
        else $list = self::Delimit($value, $Delimit);
        $info = array();
        foreach ($list as $k => $v)
        {
        //if(strstr($v, $sign))
        //{
            if(is_array($sign))
            {
                $signs = $sign;
                foreach ($signs as &$sign)
                {
                	$pos = strpos($v, $sign);
                	if ($pos === 0)
                	{
                		$info[$k]['exclamation'] = $sign;
                		$info[$k]['text'] = substr($v, 1);
                    	break;
                	}
                	else
                	{
                		$info[$k]['exclamation'] = false;
                		$info[$k]['text'] = $v;
                	}
                }
            }
            else
            {
                $pos = strpos($v, $sign);
                if ($pos === 0)
                {
                	$info[$k]['exclamation'] = $sign;
                	$info[$k]['text'] = substr($v, 1);
                }
                else
                {
                	$info[$k]['exclamation'] = false;
                	$info[$k]['text'] = $v;
                }
            }
        //}
        }
        return ($info);
    }
    */


    /**
     * version of sprintf for cases where named arguments are desired (php syntax)
     *
     * with sprintf: sprintf('second: %2$s ; first: %1$s', '1st', '2nd');
     *
     * with sprintfn: sprintfn('second: %second$s ; first: %first$s', array(
     *  'first' => '1st',
     *  'second'=> '2nd'
     * ));
     *
     * @param string $format sprintf format string, with any number of named arguments
     * @param array $args array of [ 'arg_name' => 'arg value', ... ] replacements to be made
     * @return string|false result of sprintf call, or bool false on error
     */
    
    //$lol = array('pie' => 'dolny', 'ama' => array('omg', 'wtf'));
    //echo sprintfn('1: %(pie)s, 2: %(ama)s', makeList($lol, ' '));
    
    /*
    function sprintfn ($format, array $args = array()) {
        // map of argument names to their corresponding sprintf numeric argument value
        $arg_nums = array_slice(array_flip(array_keys(array(0 => 0) + $args)), 1);
    
        // find the next named argument. each search starts at the end of the previous replacement.
        for ($pos = 0; preg_match('/(?<=%)([a-zA-Z_]\w*)(?=\$)/', $format, $match, PREG_OFFSET_CAPTURE, $pos);) {
            $arg_pos = $match[0][1];
            $arg_len = strlen($match[0][0]);
            $arg_key = $match[1][0];
        echo "$arg_pos $arg_len $arg_key\n";
    
            // programmer did not supply a value for the named argument found in the format string
            if (! array_key_exists($arg_key, $arg_nums)) {
                user_error("sprintfn(): Missing argument '${arg_key}'", E_USER_WARNING);
                return false;
            }
    
            // replace the named argument with the corresponding numeric one
            $format = substr_replace($format, $replace = $arg_nums[$arg_key], $arg_pos, $arg_len);
            $pos = $arg_pos + strlen($replace); // skip to end of replacement for next iteration
        }
    
        return vsprintf($format, array_values($args));
    }
    */
    
}
