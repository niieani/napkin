<?php

namespace Tools;

class ArrayTools
{
    public static function accessArrayElementByPath(&$arr, $path = null, $checkEmpty = true, $emptyResponse = false) //$trimPath=0
    {
        // Check path
        if (!$path) user_error("Missing array path for array", E_USER_WARNING);
        
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
}