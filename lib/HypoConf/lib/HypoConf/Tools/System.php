<?php

namespace Tools;

class System
{
    public static function getCPUs()
    {
    //  echo "Detecting number of CPU cores: ";
        LogCLI::Message('Detecting number of CPU cores (not counting HyperThreading)', 2, LogCLI::INFO);
        $cpuCoreNum = (int)trim(shell_exec("cat /proc/cpuinfo | grep \"core id\" | sort | uniq | wc -l"));
        LogCLI::MessageResult(LogCLI::BLUE.'CPUs'.LogCLI::RESET.' = '.LogCLI::YELLOW.$cpuCoreNum.LogCLI::RESET, 2, LogCLI::INFO);
        LogCLI::Result(LogCLI::INFO);
        return $cpuCoreNum;
    }
    public static function getCPUsWithHT()
    {
    //  echo "Detecting number of CPU cores: ";
        LogCLI::Message('Detecting number of CPU cores (counting HyperThreading)', 2, LogCLI::INFO);
        $cpuCoreNum = (int)trim(shell_exec("cat /proc/cpuinfo | grep \"core id\" | sort | wc -l"));
        LogCLI::MessageResult(LogCLI::BLUE.'CPU cores'.LogCLI::RESET.' = '.LogCLI::YELLOW.$cpuCoreNum.LogCLI::RESET, 2, LogCLI::INFO);
        LogCLI::Result(LogCLI::INFO);
        return $cpuCoreNum;
    }
    
    public static function Dump($var, $verbosityLevel = 0, $message = null, $level = 0)
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
                $output .= self::Dump($value, $verbosityLevel, $message, ($level +1));
            }
            else
            {
                if($display === true) LogCLI::MessageResult($message.LogCLI::GREEN.$i.LogCLI::RESET.' = '.LogCLI::YELLOW.$value.LogCLI::RESET, $verbosityLevel, LogCLI::INFO);
                else 
                {
                    $output .= "[$i = $value]";
                }
            }
        }
        return $output;
    }
}