<?php
namespace Tools;
use Tools\LogCLI;
use Tools\MakePath;

class ParseTools
{
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
     * @param string $format sprintf format string, with any number of named arguments
     * @param array $args array of [ 'arg_name' => 'arg value', ... ] replacements to be made
     * @return string|false result of sprintf call, or bool false on error
     */
    public static function sprintfn ($format, array $args = array()) {
        //var_dump($format);
        if(is_string($format))
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
                if (! array_key_exists($arg_key, $arg_nums) || $args[$arg_key] === false) {
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
            $return = vsprintf($format, array_values($args));
            if($return == $format)
            {
                //LogCLI::MessageResult('No dynamic content.', 6, LogCLI::INFO);
                LogCLI::Result(LogCLI::OK);
            }
            else
            {
                LogCLI::MessageResult('Parsed content: '.LogCLI::BLUE.$return.LogCLI::RESET, 6, LogCLI::INFO);
                LogCLI::Result(LogCLI::OK);
            }
            return $return;

        }
        else
        {
            LogCLI::Fail('Input template is not a string');
        }
    }

    
    public static function RemoveBracket($format, $depth = 0)
    {
        $reversed = strrev($format);
        $posRight = 0;
        $i = 0;
        $depthRight = 0;
        while ($depthRight <= $depth && ($posRight = strpos($reversed, ']]', $posRight+$i)) !== false)
        {
            $i = 2;
            $depthRight++;
            var_dump($posRight);
            $thisPosRight = $posRight;
        }
        var_dump($rightCut = strrev(substr_replace($reversed, '', $thisPosRight, 2)));

        $posLeft = 0;
        $i = 0;
        $depthLeft = 0;
        while ($depthLeft <= $depth && ($posLeft = strpos($rightCut, '[[', $posLeft+$i)) !== false)
        {
            $i = 2;
            $depthLeft++;
            var_dump($posLeft);
            $thisPosLeft = $posLeft;
        }
        var_dump($leftCut = substr_replace($rightCut, '', $thisPosLeft, 2));
        
    }
    
    public static function StripTag($format, $origin = false)
    {

        /*
        while (($posRight = strpos($reversed, ']]', $posRight+$i)) !== false)
        {
            if($lastPosRight != 0)
                $fragment = substr($reversed, 0, $lastPosRight);
            else $fragment = '';
            
            if($fragment != '') 
            {
                LogCLI::Message('Cutting: '.LogCLI::GREEN_LIGHT.(strrev($fragment)).LogCLI::RESET, 5);
                self::StripTag(strrev($fragment));
                LogCLI::Result(LogCLI::OK);
            }
            $i = 2;
            $lastPosRight = $posRight;
            
        }*/
        //$path = new MakePath();
        
        $lastPosLeft = $posLeft = 0;
        $i = 0;
        $finitoLeft = false;
        $level = 0;
        
        $posLeftAcc = array();
        $posRightAcc = array();
        
        $iterationLeft = 0;
        
        while ($posLeft+$i <= (strlen($format)))
        {
            $posLeft = strpos($format, '[[', $posLeft+$i); 
            if($posLeft === false) { $posLeft = strlen($format); $finitoLeft = true; }
            
            
            //if($lastPosLeft != 0)
            //var_dump($posLeft);
            //$fragment = substr($format, $posLeft, strlen($format)-$posLeft);
            $fragment = substr($format, $lastPosLeft, $posLeft-$lastPosLeft);

            //$reversed = strrev($format);
            
            //else $fragment = '';
            if($fragment != '') 
            {
                //self::StripTag($fragment);
                
                //$path->begin($fragment);
                $level++;
                LogCLI::Message('    '.$level.') Start: '.LogCLI::GREEN_LIGHT.($fragment).LogCLI::RESET, 5);
                
                $iterationLeft++;
            
                $lastPosRight = $posRight = 0;
                $j = 0;
                
                //$finitoRight = false;
                
                $iterationRight = 0;
                while($posRight+$j <= (strlen($fragment)))
                {
                    $posRight = strpos($fragment, ']]', $posRight+$j);
                    //if($posRight === false) { $posRight = strlen($fragment); $finitoRight = true; }
                    if($posRight === false) { break; }
                    
                    $iterationRight++;
                    $posLeftAcc[$level][$iterationLeft] = $lastPosLeft+$lastPosRight;
                    $posRightAcc[$level][$iterationLeft] = strlen($fragment)-$lastPosRight;
                    //$posRightAcc[$level][$iterationLeft][$iterationRight] = $lastPosLeft+($posRight-$lastPosRight);
                    
                    /* napisać klasę w stylu MakePath z trzema poleceniami: start, end - start powieksza level i dokleja zawartość do niższego lvlu */
                    
                    LogCLI::MessageResult('Pos: '.$posRight.' LastPos: '.$lastPosRight, 5);
                    $subfragment = substr($fragment, $lastPosRight, $posRight-$lastPosRight);
                    
                    LogCLI::MessageResult($level.') End: '.LogCLI::GREEN_LIGHT.($subfragment).']]'.LogCLI::RESET, 5);
                    //$path->begin($subfragment);
                    //$path->end();
                    
                    $level--;
                    LogCLI::Result(LogCLI::OK);
                    
                    $j = 2;
                    $lastPosRight = $posRight;
                    
                    //if($finitoRight === true) break;
                }
                
                //LogCLI::Result(LogCLI::OK);
            }
            
            $i = 2;
            $lastPosLeft = $posLeft;
            
            if($finitoLeft === true) break;
        }
        LogCLI::Result(LogCLI::OK);
        
        //var_dump($posLeftAcc);
        //var_dump($posRightAcc);
        foreach($posLeftAcc as $level => $listLeft)
        {
            foreach($listLeft as $n => $posLeft)
            {
                var_dump($fragment = substr($format, $posLeft, $posRightAcc[$level][$n]));
            }
        }
        //$path->end();
        //var_dump($path->getPaths());
    }
    
    public static function sprintfnnew ($format, array $args = array()) {
        if(is_string($format))
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
                if (! array_key_exists($arg_key, $arg_nums) || $args[$arg_key] === false) {
                    LogCLI::MessageResult('Not set: '.LogCLI::YELLOW.$arg_key.LogCLI::RESET.', skipping', 4, LogCLI::INFO);
                    
                    LogCLI::MessageResult('Old format: '.LogCLI::YELLOW.$format.LogCLI::RESET, 4, LogCLI::INFO);
                    LogCLI::MessageResult('Cutting pos: '.LogCLI::YELLOW.($arg_pos-1).LogCLI::RESET, 4, LogCLI::INFO);
                    LogCLI::MessageResult('Cutting len: '.LogCLI::YELLOW.($arg_len+2).LogCLI::RESET, 4, LogCLI::INFO);
                    $format = substr_replace($format, $replace = '', $arg_pos-1, $arg_len+2);
                    
                    LogCLI::MessageResult('New format: '.LogCLI::YELLOW.$format.LogCLI::RESET, 4, LogCLI::INFO);
                }
                else
                {
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
            $return = vsprintf($format, array_values($args));
            if($return == $format)
            {
                //LogCLI::MessageResult('No dynamic content.', 6, LogCLI::INFO);
                LogCLI::Result(LogCLI::OK);
            }
            else
            {
                LogCLI::MessageResult('Parsed content: '.LogCLI::BLUE.$return.LogCLI::RESET, 6, LogCLI::INFO);
                LogCLI::Result(LogCLI::OK);
            }
            return $return;

        }
        else
        {
            LogCLI::Fail('Input template is not a string');
        }
    }
}