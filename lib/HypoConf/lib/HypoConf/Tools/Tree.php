<?php

namespace Tools;

class Tree
{
    public static function addToTree($arrayin)
    {
        $arrayin = array_reverse($arrayin);
        $tree = array();
    	for ($i = 0; $i < count($arrayin); $i++)
    	{
                    //$last = $arrayin[$i];
                    $tree = array($arrayin[$i] => $tree);
    	}
    	return $tree;
    }
    
    public static function addToTreeSet($arrayin, $values) //, $skip = false
    {
        $arrayin = array_reverse($arrayin);
        $tree = array();
        $all = count($arrayin);
        for ($i = 0; $i < $all; $i++)
        {
            //$last = $arrayin[$i];
            ($i == 0) ?
                $tree = array($arrayin[$i] => $values) :
                $tree = array($arrayin[$i] => $tree);
        }
        return $tree;
    }
}
