<?php

namespace Tools;

class Tree
{
    /**
     * Makes a tree from a sequential array.
     *
     * Like this:
     * array('one', 'two', 'three')
     *      would be this:
     * array('one' => 'two' => 'three')
     *
     * @static
     * @param array $arrayin
     * @return array
     */
    public static function addToTree(array $arrayin)
    {
        $arrayin = array_reverse($arrayin);
        $tree = array();
        for ($i = 0; $i < count($arrayin); $i++)
        {
            $tree = array($arrayin[$i] => $tree);
        }
        return $tree;
    }

    /**
     * Makes a tree from a sequential array
     * and sets the value of the deepest level.
     *
     * Like this:
     * addToTreeAndSet(array('one', 'two', 'three'), 'values')
     *      would be this:
     * array('one' => 'two' => 'three' => 'values')
     *
     * @static
     * @param array $arrayin
     * @param mixed $values
     * @return array
     */
    public static function addToTreeAndSet(array $arrayin, $values)
    {
        $arrayin = array_reverse($arrayin);
        $tree = array();
        $all = count($arrayin);
        for ($i = 0; $i < $all; $i++)
        {
            ($i == 0) ?
                $tree = array($arrayin[$i] => $values) :
                $tree = array($arrayin[$i] => $tree);
        }
        return $tree;
    }
}
