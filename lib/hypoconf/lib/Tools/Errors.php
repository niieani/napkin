<?php
/**
 * User: NIXin
 * Date: 15.09.2011
 * Time: 14:26
 */

namespace Tools;

class Errors
{
    static function Handle($errno, $errstr, $errfile = '', $errline = '')
    {
        if (!(error_reporting() & $errno))
        {
            // This error code is not included in error_reporting
            return;
        }

        switch ($errno)
        {
            case E_USER_ERROR:
//                echo "<b>My ERROR</b> [$errno] $errstr<br />\n";
//                echo "  Fatal error on line $errline in file $errfile";
//                echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
//                echo "Aborting...<br />\n";
                LogCLI::Fail($errstr);
//                exit(1);
                break;

            case E_USER_WARNING:
//                echo "<b>My WARNING</b> [$errno] $errstr<br />\n";
                LogCLI::Warning($errstr);
                break;

            case E_USER_NOTICE:
//                echo "<b>My NOTICE</b> [$errno] $errstr<br />\n";
                LogCLI::Notice($errstr);
                break;

            default:
//                echo "Unknown error type: [$errno] $errstr<br />\n";
                LogCLI::Fail($errstr);
                break;
        }

        /* Don't execute PHP internal error handler */
        return true;
    }
}
