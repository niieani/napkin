<?php

namespace HypoConf;

use Tools\LogCLI;
//use Tools\StringTools;

//use Tools\ArrayTools;
//use Tools\Tree;
//use Tools\FileOperation;

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