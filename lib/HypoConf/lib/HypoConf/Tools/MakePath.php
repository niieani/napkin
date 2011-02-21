<?php

namespace Tools;

class MakePath
{
    protected $level = -1;
    public $path = array();
    public $allpaths = array();
    public $delimiter = '/';
    
    public function begin($name)
    {
        $this->level++;
        $this->path[$this->level] = (isset($this->path[$this->level-1])) ? $this->path[$this->level-1].$this->delimiter.$name : $name;
        //$this->path[$this->level][] = $name;
    }
    public function end()
    {
        $this->allpaths[] = $this->path[$this->level];
        $this->level--;
    }
    
    public function getPaths()
    {
        return $this->allpaths;
    }
}
