<?php
// inspired by Bash Beauty - http://www.zulius.com/freebies/bash-beauty-output-for-bash-scripts/
// class by Bazyli Brzoska
/* Example usage:

LogCLI::SetDisplayTime(true);
LogCLI::Message('Testing');
LogCLI::Result('OK');
LogCLI::Message('Testing');
LogCLI::Result('FATAL');
LogCLI::Message('Testing');
LogCLI::Result('INFO');
LogCLI::Message('Testing', FATAL);

*/

namespace Tools;

class LogCLI
{
    const COLOR_RESET="\x1b[39;49;00m";
    const COLOR_GREEN="\x1b[32;01m";
    
    const STATUS_OK ="[  OK  ]"; 
    const COLOR_OK = "\x1b[33;32m";
    
    const STATUS_FAILED = "[FAILED]";
    const COLOR_FAILED="\x1b[31;31m";
    
    const STATUS_WARN = "[ WARN ]"; 
    const COLOR_WARN = "\x1b[33;33m";
    
    const STATUS_INFO = "[ INFO ]"; 
    const COLOR_INFO = "\x1b[36;01m";
    
    const STATUS_UNKNOWN = "[ ???? ]"; 
    const COLOR_UNKNOWN = "";
    
    private static $numCols = 80;
    private static $numColsKnown = false;
    private static $displayTime = false;
    private static $lastMessageLength = 0;
    private static $messageSet = false;
    private static $fatal = false;
    
    public static function Message($message, $type = false)
    {
        if (self::$messageSet === true) { 
            self::DisplayResult(); 
            throw new Exception('Result not set - defaulting to unknown.'); 
            }
        self::DisplayMessage($message, $type);
        self::$messageSet = (self::$fatal === true) ? false : true;
    }
    public static function Result($type = false)
    {
        if (self::$messageSet === false) { 
            throw new Exception('Message not set.'); 
            self::$lastMessageLength = 0; 
            }
        self::DisplayResult($type);
        self::$messageSet = false;
    }
    public static function MessageResult($message, $type = false)
    {
        self::Message($message, $type);
        if ($type != 'FATAL') self::Result($type);
    }
        
    public static function SetDisplayTime($displayTime)
    {
        if(is_bool($displayTime)) self::$displayTime = $displayTime;
    }
    private static function DisplayMessage($message, $type = false)
    {
        $output = (self::$displayTime === true) ? "\x20".self::getDateTime()."\x20" : "\x20";
        $output .= $message;
        self::$lastMessageLength = strlen($output);
        
        if ($type === 'FATAL')
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
            case 'OK':
                $statusOutput = self::COLOR_OK.self::STATUS_OK.self::COLOR_RESET;
                break;
            case 'FAILED':
                $statusOutput = self::COLOR_FAILED.self::STATUS_FAILED.self::COLOR_RESET;
                break;
            case 'WARN':
                $statusOutput = self::COLOR_WARN.self::STATUS_WARN.self::COLOR_RESET;
                break;
            case 'INFO':
                $statusOutput = self::COLOR_INFO.self::STATUS_INFO.self::COLOR_RESET;
                break;
            default:
                $statusOutput = self::COLOR_UNKNOWN.self::STATUS_UNKNOWN.self::COLOR_RESET;
                break;
        }
        
        $output = null;
        
        (self::$numColsKnown) === true ?: self::GetNumCols();
        
        for($i = self::$lastMessageLength+8; $i < self::$numCols-1; $i++)
        {
            $output .= "\x20";
        }
        echo $output.$statusOutput.PHP_EOL;
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