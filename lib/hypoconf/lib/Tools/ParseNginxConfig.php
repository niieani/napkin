<?php
/**
 * User: NIXin
 * Date: 09.07.12
 * Time: 18:35
 */

namespace Tools;
use Tools\SuperStack;
use Tools\StringTools;

class ParseNginxConfig
{
    public static function doParse($toFormat)
    {
        $toFormat = StringTools::removeNewLines($toFormat);
        return SuperStack::StackUp($toFormat, '{', '}');
    }
}
