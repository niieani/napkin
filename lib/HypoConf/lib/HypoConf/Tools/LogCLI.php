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


need to add multilevel logging (when called two times, just nest instead of erroring)

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
    const RESET="\x1b[39;49;00m";
    const GREEN_LIGHT = "\x1b[32;01m";
    
    const STATUS_OK ="[  OK  ]"; 
    const COLOR_OK = "\x1b[33;32m";
    const GREEN = "\x1b[33;32m";
    
    const STATUS_INFO = "[ INFO ]"; 
    const COLOR_INFO = "\x1b[36;01m";
    const BLUE = "\x1b[36;01m";
    
    const STATUS_WARN = "[ WARN ]"; 
    const COLOR_WARN = "\x1b[33;33m";
    const YELLOW = "\x1b[33;33m";
    
    const STATUS_FAILED = "[FAILED]";
    const COLOR_FAILED="\x1b[31;31m";
    const RED = "\x1b[31;31m";
    
    const STATUS_UNKNOWN = "[   >> ]"; 
    const COLOR_UNKNOWN = "";
    
    const NoTimer = false;
    
    protected static $numCols = 80;
    protected static $numColsKnown = false;
    protected static $displayTime = false;
    protected static $runTimer = true;
    //protected static $timerRunning = false;
    protected static $timerRunning = array();
    protected static $lastMessageLength = 0;
    //protected static $lastVerboseLevel = 0;
    protected static $lastVerboseLevel = array();
    protected static $messageSet = false;
    protected static $fatal = false;
    protected static $verboseLevel = 0;
    protected static $nesting = -1;
    protected static $displayEmptyResults = true;
    protected static $timers = array();
    
    const Distance = 4;

    public static function SetVerboseLevel($level)
    {
        self::$verboseLevel = $level;
    }
    
    private static function GenerateSpaces($howMany)
    {
        $spacing = '';
        
        for($i = 0; $i < $howMany-1; $i++)
        {
            $spacing .= (($i % self::Distance) == 0) ? '¦' : ' ';
        }
        return $spacing;
    }

    public static function Message($message, $level = 1, $timerOn = true, $lessSpaces = 0) //, $lowerTimer = 0
    {
        self::$nesting++;
        if(self::$verboseLevel >= $level)
        {
            if(self::$runTimer === true && $timerOn === true && (!isset(self::$timerRunning[self::$nesting]) || self::$timerRunning[self::$nesting] === false))
            {
                //Timer::Start();
                self::$timers[self::$nesting] = new Timer();
                self::$timers[self::$nesting]->start();
                //echo PHP_EOL."STARTED TIMER: ".self::$nesting.PHP_EOL;
                //self::$timerRunning = true;
                
                self::$timerRunning[self::$nesting] = true;
            }
            
            if (self::$messageSet === true) { 
                //echo PHP_EOL."message set".PHP_EOL;
                
                //throw new \Exception('Previous result not set - defaulting to unknown.'); 
                //self::DisplayResult(); 
                self::DisplayResult(null, false);  //null, null, false
            }
            
            self::DisplayMessage(self::GenerateSpaces((self::$nesting*self::Distance)-$lessSpaces).$message);
            //self::$messageSet = (self::$fatal === true) ? false : true;
            self::$messageSet = true;

        }
        self::$lastVerboseLevel[self::$nesting] = $level;
    }
    
    public static function Result($type = null, $stopTimer = true, $lowerTimer = 0) //$level = null, $timerOn = true
    {
        //echo PHP_EOL."(RESULT)";
        if (self::$messageSet === false) 
        { 
            //if (self::$lastVerboseLevel < self::$verboseLevel) 
            //trigger_error("Message not set", E_USER_NOTICE);
            //throw new \Exception('Message not set.'); 
            //echo "hi\n";
            self::$lastMessageLength = 0; 
            if (self::$displayEmptyResults === true && self::$lastVerboseLevel[self::$nesting] !== null)
            {
                self::MessageResult(self::YELLOW.'[DONE]'.self::RESET, self::$lastVerboseLevel[self::$nesting], $type, true); //, 1
            }
        }
        else
        {
            self::DisplayResult($type, $stopTimer, $lowerTimer); //
            self::$messageSet = false;
        }
        self::$nesting--;
        
    }
    private static function DisplayMessage($message, $type = null)
    {
        (self::$numColsKnown) === true ?: self::GetNumCols();
        
        //echo PHP_EOL."(DISPLAYMESSAGE)";
        $output = (self::$displayTime === true) ? "\x20".self::getDateTime()."\x20" : "\x20";
        $output .= $message;

/*
        $maxlength = self::$numCols-5;
        if(mb_strlen($output) >= $maxlength) 
        {
            $output = substr($output, 0, $maxlength).'...';
        }
*/

        $outputNoColors = preg_replace('#\\x1b\[[0-9][0-9];.*?[0-9][0-9]m#', '', $output);
        $outputNoColors = preg_replace('#¦#', '|', $outputNoColors);
        
        self::$lastMessageLength = (function_exists('mb_strlen')) ? mb_strlen($outputNoColors) : strlen($outputNoColors);
        
        
        if ($type === self::FATAL)
        {
            echo self::COLOR_FAILED.$output.self::COLOR_RESET.PHP_EOL;
            self::$fatal = true;
            return true;
        }
        else echo $output;
    }
    private static function DisplayResult($type = null, $stopTimer = true, $lowerTimer = 0)
    {
        //echo PHP_EOL."(DISPLAYRESULT)";
        
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
                $statusOutput = self::COLOR_RESET.self::STATUS_UNKNOWN.self::COLOR_RESET;
                break;
        }
        
        $output = null;
        $outputTimer = null;
        
        if(self::$runTimer === true && $stopTimer === true && isset(self::$timerRunning[self::$nesting-$lowerTimer]) && self::$timerRunning[self::$nesting-$lowerTimer] === true) // 
        {
            self::$timers[self::$nesting-$lowerTimer]->stop();
            $outputTimer = '('.(self::$nesting-$lowerTimer).') '.self::$timers[self::$nesting-$lowerTimer]->get(Timer::MICROSECONDS)."μs\x20";
            self::$timerRunning[self::$nesting-$lowerTimer] = false;
        }
        $outputTimerLength = ($outputTimer) ? (strlen($outputTimer)-1) : 0;
                
        for($i = self::$lastMessageLength+$outputTimerLength+8; $i < self::$numCols-1; $i++)
        {
            $output .= "\x20";
        }
        echo $output.$outputTimer.$statusOutput.PHP_EOL;
        
        return true;
    }
    
    
    public static function MessageResult($message, $level = 1, $type = null, $internal = false)
    {
        if ($type == self::FATAL) self::Fatal($message);
        else 
        {
            if(self::$verboseLevel >= $level)
            {
                if($internal === true)
                {
                    self::Message($message, $level, self::NoTimer, self::Distance+1);
                    self::Result($type, true, 1);
                }
                else
                {
                    self::Message($message, $level, self::NoTimer);
                    self::Result($type, false, 0);
                }
            }
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
    
    public static function Fail($message)
    {
        self::Error('[ERROR] '.$message);
    }
    
    public static function Fatal($message)
    {
        self::Error('[UNRECOVERABLE ERROR] '.$message);
    }
    
    private static function getDateTime()
    {
        return date("YmdHis");
    }
    private static function GetNumCols()
    {
        self::$numCols = (int)trim(exec('tput cols'));
        self::$numColsKnown = true;
    }
}