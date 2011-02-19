<?php

namespace Tools;

use \Tools\LogCLI;

class ArrayTools
{
    public static function accessArrayElementByPath(&$arr, $path = null, $checkEmpty = false, $emptyResponse = null) //$trimPath=0
    {
        // Check path
        if (!$path) user_error("Missing array path for array", E_USER_WARNING);
        
        // Vars
        $pathElements = explode('/', $path);
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
        return $path;
    }
    
    public static function createArrayElementByPath(&$arr, $path = null, $value = null, $skipN = 0) //$trimPath=0
    {
        // Check path
        if (!$path) user_error("Missing array path for array", E_USER_WARNING);
        
        // Vars
        $pathElements = explode('/', $path);
        $path =& $arr;
        
        if($skipN > 0) $pathElements = array_splice($pathElements, 0, count($pathElements)-$skipN);
        
        // Go through path elements
        foreach ($pathElements as $e)
        {
            // Check set
            if (!isset($path[$e])) $path[$e] = array();
            
            // Update path
            $path =& $path[$e];
        }
        $path = self::MergeArrays($path, $value);
        
        // Everything checked out, return value
        return $path;
    }
    
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
    
    
    /**
     * Merges any number of arrays of any dimensions, the later overwriting
     * previous keys, unless the key is numeric, in whitch case, duplicated
     * values will not be added.
     *
     * The arrays to be merged are passed as arguments to the function.
     *
     * @access public
     * @return array Resulting array, once all have been merged
     */
    public static function MergeArrays($arr1, $arr2) {
        // Holds all the arrays passed
        //$params = & func_get_args ();
        if(!is_array($arr1))
        {
            if(!is_array($arr2)) 
            {
                $arr1 = array();
                $arr2 = array();
            }
            else return $arr2;
        }
        
        $params = array($arr1, $arr2);
       
        // First array is used as the base, everything else overwrites on it
        $return = array_shift ( $params );
       
        // Merge all arrays on the first array
        foreach ( $params as $array ) {
            foreach ( $array as $key => $value ) {
                // Numeric keyed values are added (unless already there)
                if (is_numeric ( $key ) && (! in_array ( $value, $return ))) {
                    if (is_array ( $value ) && isset($return[$key])) {
                        $return [] = self::MergeArrays ( $return[$key], $value ); // double $$key ?
                    } else {
                        $return [] = $value;
                    }
                   
                // String keyed values are replaced
                } else {
                    if (isset ( $return [$key] ) && is_array ( $value ) && is_array ( $return [$key] )) {
                        $return [$key] = self::MergeArrays ( $return[$key], $value ); // double $$key ?
                    } else {
                        $return [$key] = $value;
                    }
                }
            }
        }
       
        return $return;
    }
    
    
    public static function TraverseTree(array &$settings, $lookFor = 'defaults')
    {
        LogCLI::Message('Traversing definition tree in search for: '.LogCLI::YELLOW.$lookFor.LogCLI::RESET, 6);
        
        $matches = array();
        foreach(self::GetMultiDimentionalElements($settings) as $path)
        {
            $pathElements = explode('/', $path);
            //$lastElement = end($pathElements);
            //if(stripos($path, $lookFor) !== FALSE)
            if(end($pathElements) == $lookFor)
            {
                LogCLI::MessageResult('Match found at: '.LogCLI::BLUE.$path.LogCLI::RESET, 2, LogCLI::INFO);
                $matches[] = $path;
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
            }
        }
        
        LogCLI::Result(LogCLI::OK);
        
        if(!empty($matches)) return $matches;
        else return false;
    }
    
    /*
    public static function MergeArrays($Arr1, $Arr2)
    {
        //var_dump($Arr1);
        //debug_print_backtrace();
        //$Arr1 = array_merge_recursive($Arr1, $Arr2);
        
        foreach($Arr2 as $key => $Value)
        {
    
            if(array_key_exists($key, $Arr1) && is_array($Value))
              $Arr1[$key] = self::MergeArrays($Arr1[$key], $Arr2[$key]);
            
            else
              $Arr1[$key] = $Value;
        }
        
        return $Arr1;
    }
    */
    /**
     * Merges any number of arrays / parameters recursively, replacing
     * entries with string keys with values from latter arrays.
     * If the entry or the next value to be assigned is an array, then it
     * automagically treats both arguments as an array.
     * Numeric entries are appended, not replaced, but only if they are
     * unique
     *
     * calling: result = array_merge_recursive_distinct(a1, a2, ... aN)
    **/
    /*
    public static function MergeArrays() {
        //$numeric = 0;
      $arrays = func_get_args();
      $base = array_shift($arrays);
      if(!is_array($base)) $base = empty($base) ? array() : array($base);
      foreach($arrays as $append) {
        if(!is_array($append)) $append = array($append);
        foreach($append as $key => $value) {
          if(!array_key_exists($key, $base) and !is_numeric($key)) {
            $base[$key] = $append[$key];
            continue;
          }
          if(is_array($value) or is_array($base[$key])) {
            $base[$key] = self::MergeArrays($base[$key], $append[$key]);
          } else if(is_numeric($key)) {
              //$numeric++;
              //var_dump($numeric);
            if(!in_array($value, $base)) $base[] = $value;
          } else {
            $base[$key] = $value;
          }
        }
      }
      return $base;
    }*/
    
}