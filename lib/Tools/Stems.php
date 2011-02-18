<?php

namespace Tools;

use \Tools\LogCLI;

class Stems
{
    public static function MergeArrays($Arr1, $Arr2)
    {
      foreach($Arr2 as $key => $Value)
      {
        if(array_key_exists($key, $Arr1) && is_array($Value) && is_array($Arr1[$key]))
          $Arr1[$key] = MergeArrays($Arr1[$key], $Arr2[$key]);
    
        else
          $Arr1[$key] = $Value;
    
      }
      return $Arr1;
    }
    
    public static function LoadDefinitions($application)
    {
        foreach (glob(HC_DIR.'/stems/'.$application."/*.php") as $filename)
        {
            try { 
                LogCLI::Message("Loading definitions from file: $filename", 2);
                include($filename);
                LogCLI::Result(LogCLI::OK);
            } catch (Exception $e) {
                LogCLI::Result(LogCLI::FAIL);
                LogCLI::Fatal('Caught exception - '.$e->getMessage());
            }
        }
        
        /*
        $file = HC_DIR.'/stems/'.$application.'/definitions.php';
        echo $file;
        if(file_exists($file))
        {
            try { 
                include_once($file);
            } catch (Exception $e) {
                echo 'Caught exception: ',  $e->getMessage(), "\n";
            }
        }
        */
    }
    
    public static function global_include($script_path) {
        // check if the file to include exists:
        if (isset($script_path) && is_file($script_path)) {
            // extract variables from the global scope:
            extract($GLOBALS, EXTR_REFS);
            ob_start();
            include($script_path);
            return ob_get_clean();
        } else {
            ob_clean();
            trigger_error('The script to parse in the global scope was not found');
        }
    }
}