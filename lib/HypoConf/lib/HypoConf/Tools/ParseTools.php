<?php
namespace Tools;
use Tools\LogCLI;

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
                    $format = substr_replace($format, $replace = '', $arg_pos-1, $arg_len+2);
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