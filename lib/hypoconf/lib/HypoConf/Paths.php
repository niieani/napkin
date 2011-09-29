<?php

namespace HypoConf;

use Tools\LogCLI;
use Tools\FileOperation;

/**
 * @link http://alanhogan.com/tips/php/directory-separator-not-necessary
 */

class Paths
{
    public static $root;
    public static $db;
    public static $separator = '/';
    public static $defaultGroup = 'sites';
    public static $defaultUser = 'default';
    public static $disabled = '_disabled';
    public static $hypoconf = '_hypoconf';
    public static $templates = '_templates';
    public static $defaultConfig = 'config.yml';

    public static function GetFullPath($site)
    {
        $files = FileOperation::getAllFilesByExtension(Paths::$db, 'yml');
        $pathinfo = array();
        //$siteYML = false;
        foreach($files as $id => $file)
        {
            $pathinfo[$id] = FileOperation::pathinfo_utf($file);
            if($pathinfo[$id]['filename'] == $site)
            {
                return $files[$id];
            }
        }
        return false;
    }
}