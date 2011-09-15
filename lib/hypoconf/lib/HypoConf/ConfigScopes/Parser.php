<?php
namespace HypoConf\ConfigScopes;

use HypoConf;

//use HypoConf\Tools\ArrayTools;
//use \Tools\LogCLI;

abstract class Parser
{
    protected $parsers = array();
    public function GetSubParsers()
    {
        return $this->parsers;
    }
    abstract public function __construct(array &$templates);
    abstract public function FixPath($path, $iterativeSetting = 0);
}