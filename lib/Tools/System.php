<?php

namespace Tools;

class System
{
    public static function getCPUs()
    {
    //  echo "Detecting number of CPU cores: ";
        LogCLI::Message('Detecting number of CPUs (not cores)', 2, LogCLI::INFO);
        $cpuCoreNum = (int)trim(shell_exec("cat /proc/cpuinfo | grep \"core id\" | sort | uniq | wc -l"));
        LogCLI::MessageResult(LogCLI::BLUE.'CPUs'.LogCLI::RESET.' = '.LogCLI::YELLOW.$cpuCoreNum.LogCLI::RESET, 2, LogCLI::INFO);
        LogCLI::Result(LogCLI::INFO);
        return $cpuCoreNum;
    }
    public static function getCPUcores()
    {
    //  echo "Detecting number of CPU cores: ";
        LogCLI::Message('Detecting number of CPU cores', 2, LogCLI::INFO);
        $cpuCoreNum = (int)trim(shell_exec("cat /proc/cpuinfo | grep \"core id\" | sort | wc -l"));
        LogCLI::MessageResult(LogCLI::BLUE.'CPU cores'.LogCLI::RESET.' = '.LogCLI::YELLOW.$cpuCoreNum.LogCLI::RESET, 2, LogCLI::INFO);
        LogCLI::Result(LogCLI::INFO);
        return $cpuCoreNum;
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