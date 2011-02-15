<?php
// inspired by Bash Beauty - http://www.zulius.com/freebies/bash-beauty-output-for-bash-scripts/
// class by Bazyli Brzoska
/* Example usage:

LogCLI::SetDisplayTime(true);
LogCLI::SetRunTimer(true);

LogCLI::Message('Testing');
LogCLI::Result(LogCLI::OK);

LogCLI::Fatal('Fatal error, cannot continue');

LogCLI::Message('Testing');
LogCLI::Result(LogCLI::INFO);

LogCLI::Message('Testing', LogCLI::FATAL);

LogCLI::MessageResult('Testing', LogCLI::WARN);

*/

namespace Tools;
use \Tools\Timer;

class LogCLI
{
    /**
     * Error severity, from low to high. From BSD syslog RFC, secion 4.1.1
     * @link http://www.faqs.org/rfcs/rfc3164.html
     */
    /*
    const EMERG  = 0;  // Emergency: system is unusable
    const ALERT  = 1;  // Alert: action must be taken immediately
    const CRIT   = 2;  // Critical: critical conditions
    const ERR    = 3;  // Error: error conditions
    const WARN   = 4;  // Warning: warning conditions
    const NOTICE = 5;  // Notice: normal but significant condition
    const INFO   = 6;  // Informational: informational messages
    const DEBUG  = 7;  // Debug: debug messages
    */
    
    const OK     = 1;
    const INFO   = 2;
    const WARN   = 3;
    const FAIL   = 4;
    const FAILED = 5;
    const FATAL  = 6;
    
    const COLOR_RESET="\x1b[39;49;00m";
    const COLOR_GREEN="\x1b[32;01m";
    
    const STATUS_OK ="[  OK  ]"; 
    const COLOR_OK = "\x1b[33;32m";
    
    const STATUS_INFO = "[ INFO ]"; 
    const COLOR_INFO = "\x1b[36;01m";
    
    const STATUS_WARN = "[ WARN ]"; 
    const COLOR_WARN = "\x1b[33;33m";
    
    const STATUS_FAILED = "[FAILED]";
    const COLOR_FAILED="\x1b[31;31m";
    
    const STATUS_UNKNOWN = "[ ???? ]"; 
    const COLOR_UNKNOWN = "";
    
    const NoTimer = false;
    
    private static $numCols = 80;
    private static $numColsKnown = false;
    private static $displayTime = false;
    private static $runTimer = true;
    private static $timerRunning = false;
    private static $lastMessageLength = 0;
    private static $lastVerboseLevel = 0;
    private static $messageSet = false;
    private static $fatal = false;
    private static $verboseLevel = 0;

    public static function SetVerboseLevel($level)
    {
        self::$verboseLevel = $level;
    }

    public static function Message($message, $level = 1, $timerOn = true)
    {
        if(self::$verboseLevel >= $level)
        {
            if (self::$messageSet === true) { 
                //throw new \Exception('Previous result not set - defaulting to unknown.'); 
                self::DisplayResult(); 
            }
        
            self::DisplayMessage($message);
            //self::$messageSet = (self::$fatal === true) ? false : true;
            self::$messageSet = true;
        
            if(self::$runTimer === true && $timerOn === true && self::$timerRunning === false)
            {
                Timer::Start();
                self::$timerRunning = true;
            }
        }
        self::$lastVerboseLevel = $level;
    }
    
    
    public static function Error($message)
    {
        if (self::$messageSet === true) { 
            throw new \Exception('Previous result not set - defaulting to unknown.'); 
            self::DisplayResult(); 
        }
        
        if(self::$verboseLevel != -1) //if not quiet
        {
            self::DisplayMessage($message, self::FATAL);
        }
        self::$messageSet = false;
    }
    
    public static function Fatal($message)
    {
        self::Error('Unrecoverable error: '.$message);
    }
    
    public static function Fail($message)
    {
        self::Error('Error: '.$message);
    }
    
    public static function Result($type = false)
    {
        if (self::$messageSet === false) { 
            if (self::$lastVerboseLevel < self::$verboseLevel) trigger_error("Message not set", E_USER_NOTICE);
            //throw new \Exception('Message not set.'); 
            self::$lastMessageLength = 0; 
        }
        else
        {
            self::DisplayResult($type);
            self::$messageSet = false;
        }
    }
    public static function MessageResult($message, $level = 1, $type = null)
    {
        if ($type == self::FATAL) self::Fatal($message);
        else 
        {
            self::Message($message, $level, self::NoTimer);
            self::Result($type);
        }
    }
        
    public static function SetDisplayTime($displayTime)
    {
        if(is_bool($displayTime)) self::$displayTime = $displayTime;
    }
    
    public static function SetRunTimer($runTimer)
    {
        if(is_bool($runTimer)) self::$runTimer = $runTimer;
    }
    private static function DisplayMessage($message, $type = false)
    {
        $output = (self::$displayTime === true) ? "\x20".self::getDateTime()."\x20" : "\x20";
        $output .= $message;
        self::$lastMessageLength = strlen($output);
        
        if ($type === self::FATAL)
        {
            echo self::COLOR_FAILED.$output.self::COLOR_RESET.PHP_EOL;
            self::$fatal = true;
            return true;
        }
        else echo $output;
    }
    private static function DisplayResult($type = null)
    {
        switch ($type)
        {
            case self::OK:
                $statusOutput = self::COLOR_OK.self::STATUS_OK.self::COLOR_RESET;
                break;
            case self::INFO:
                $statusOutput = self::COLOR_INFO.self::STATUS_INFO.self::COLOR_RESET;
                break;
            case self::WARN:
                $statusOutput = self::COLOR_WARN.self::STATUS_WARN.self::COLOR_RESET;
                break;
            case self::FAIL:
                $statusOutput = self::COLOR_FAILED.self::STATUS_FAILED.self::COLOR_RESET;
                break;
            default:
                $statusOutput = self::COLOR_UNKNOWN.self::STATUS_UNKNOWN.self::COLOR_RESET;
                break;
        }
        
        $output = null;
        $outputTimer = null;
        
        (self::$numColsKnown) === true ?: self::GetNumCols();
        
        if(self::$runTimer === true && self::$timerRunning === true) 
        {
            Timer::Stop();
            $outputTimer = Timer::Get(Timer::MICROSECONDS)."μs\x20";
            Timer::Reset();
            self::$timerRunning = false;
        }
        $outputTimerLength = ($outputTimer) ? (strlen($outputTimer)-1) : 0;
        for($i = self::$lastMessageLength+$outputTimerLength+8; $i < self::$numCols-1; $i++)
        {
            $output .= "\x20";
        }
        echo $output.$outputTimer.$statusOutput.PHP_EOL;
        return true;
    }
    private static function getDateTime()
    {
        return date("YmdHis");
    }
    private static function GetNumCols()
    {
        self::$numCols = trim(exec('tput cols'));
        self::$numColsKnown = true;
    }
}