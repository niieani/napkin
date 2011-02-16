<?php

namespace Tools;

class System
{
    public static function getCPUs()
    {
    //  echo "Detecting number of CPU cores: ";
        return system("cat /proc/cpuinfo | grep \"core id\" | sort | uniq | wc -l");
    }
    /*
    public static function MergeArrays($Arr1, $Arr2)
    {
        var_dump($Arr1);
        //debug_print_backtrace();
        $Arr1 = array_merge_recursive($Arr1, $Arr2);
        
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
    
    public static function MergeArrays() {
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
            if(!in_array($value, $base)) $base[] = $value;
          } else {
            $base[$key] = $value;
          }
        }
      }
      return $base;
    }
    
    public static function Dump($var, $message = null, $level=0)
    {
        $output = '';
        if($level==0)
        {
            $display = false;
        }
        else
        {
            $display = true;
            //$output = '][';
        }
       
        foreach((array)$var as $i=>$value)
        {
            if(is_array($value) or is_object($value))
            {
                $output .= self::Dump($value, $message, ($level +1));
            }
            else
            {
                if($display === true) LogCLI::MessageResult($message.LogCLI::GREEN.$i.LogCLI::RESET.' = '.LogCLI::YELLOW.$value.LogCLI::RESET, 5, LogCLI::INFO);
                else 
                {
                    $output .= "[$i = $value]";
                }
            }
        }
        return $output;
    }
}