<?php

namespace HypoConf;

use Tools\LogCLI;

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
}