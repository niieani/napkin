<?php
namespace ConfigScopes;

//use \Tools\ArrayTools;
//use \Tools\LogCLI;

abstract class Parser
{
    protected $parsers = array();
    public function GetSubParsers()
    {
        return $this->parsers;
    }
    abstract public function __construct(array &$templates);
}