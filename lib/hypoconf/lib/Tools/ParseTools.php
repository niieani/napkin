<?php
/**
 * Created by Bazyli Brzoska.
 * Date: 04.03.11
 * Time: 23:35
 */

namespace Tools;
use Tools\SuperStack;
use Tools\StringTools;

/*
 * TODO: Add LogCLI logging capabilities.
 */

/**
 * Example usage:
 *
 * $listFormat = array(
 *   "gzip_buffers" => 40,
 *   "gzip_yeah" => 'yeah!'
 * );
 * $toFormat = '[[gzip_buffers %(gzip_buffers)[[ %(gzip_yeah)]]; this should still be here]]';
 * $formatArray = StackUp($toFormat, '[[', ']]');
 *
 * $parsed = parseArray($formatArray, $listFormat);
 * echo StringTools::rimplode(null, $parsed);
 */
class ParseTools
{
    public static function parseStringWithReplacementList($toFormat, array $replacementList, $open = '[', $close = ']', $pattern = '/%\(([a-zA-Z_]\w*)\)/', $ifNoMatchReturnFalse = true)
    {
        $formatArray = SuperStack::StackUp($toFormat, $open, $close);
        $parsed = self::parseArray($formatArray, $replacementList, $pattern, $ifNoMatchReturnFalse);
        return StringTools::rimplode(null, $parsed);
    }

    public static function parseArray(array $formatArray, array &$args, $pattern = '/%\(([a-zA-Z_]\w*)\)/', $ifNoMatchReturnFalse = true)
    {
        return current(self::parseRecursive(array($formatArray), &$args, $pattern, $ifNoMatchReturnFalse));
    }

    public static function parseRecursive(array $formatArray, array &$args, $pattern = '/%\(([a-zA-Z_]\w*)\)/', $ifNoMatchReturnFalse = true)
    {
        foreach($formatArray as $id => &$format)
        {
            if(is_array($format))
            {
                $returnValue = self::parseRecursive(&$format, &$args, $pattern, $ifNoMatchReturnFalse);
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
                if(self::parse(&$format, &$args, $pattern, $ifNoMatchReturnFalse) === false)
                {
                    return false;
                }
            }
        }
        return $formatArray;
    }

    public static function parse($format, array &$args, $pattern = '/%\(([a-zA-Z_]\w*)\)/', $ifNoMatchReturnFalse = true)
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
                if($ifNoMatchReturnFalse === true)
                    return false;
                else
                    $format = str_replace($match[0], null, $format);
            }
        }
        return $format;
    }
}
