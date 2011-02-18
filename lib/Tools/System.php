<?php

namespace Tools;

class System
{
    public static function getCPUs()
    {
    //  echo "Detecting number of CPU cores: ";
        return system("cat /proc/cpuinfo | grep \"core id\" | sort | uniq | wc -l");
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