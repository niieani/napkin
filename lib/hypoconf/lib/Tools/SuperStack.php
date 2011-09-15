<?php
/**
 * Created by Bazyli Brzoska.
 * Date: 04.03.11
 * Time: 23:32
 */

namespace Tools;

class SuperStack
{
    public $superStack = array();
    protected $level = -1;
    protected $currentPointer;
    protected $parentID = array();

    public function __construct()
    {
        $this->Begin();
    }
    public function Add($value)
    {
        $this->currentPointer[] = $value;
    }
    public function Begin()
    {
        $this->level++;
        $next = count($this->currentPointer);
        $this->currentPointer[$next] = array();

        $this->parentID[$this->level] = count($this->superStack);
        $this->superStack[] = &$this->currentPointer[$next];
        $this->currentPointer = &$this->currentPointer[$next];

    }
    public function End()
    {
        $this->level--;
        $this->currentPointer = &$this->superStack[$this->parentID[$this->level]];
    }
    public function GetStack()
    {
        return $this->superStack[0];
    }
    public function GetLevel()
    {
        return $this->level;
    }

    /*
     * Static Functions
     */
    public static function StackUp($format, $open = '[', $close = ']')
    {
        $stack = new self();
        //$stack = new SuperStack;
        
        /*
        $head = strpos($format, $open);
        $stack->Add(substr($format, 0, $head));
        $stack->Begin();
        */

        $head = -strlen($open);
        do $head = self::StackRecursiveHelper($stack, $format, $open, $close, $head);
        while($head !== false);

        return $stack->GetStack();
    }

    public static function StackRecursiveHelper($stack, $format, $open, $close, $head)
    {
        //$head++;
        $head += strlen($open);

        $headClose = strpos($format, $close, $head);
        $headOpen = strpos($format, $open, $head);

        if($headClose === false)
        {
            $toAdd = substr($format, $head);
            if(!empty($toAdd))
                $stack->Add($toAdd);

            // we are at the bottom level already
        }
        elseif($headClose < $headOpen || $headOpen === false)
        {
            $toAdd = substr($format, $head, $headClose-$head);
            if(!empty($toAdd))
                $stack->Add($toAdd);

            $stack->End();
        }
        else
        {
            $toAdd = substr($format, $head, $headOpen-$head);
            if(!empty($toAdd))
                $stack->Add($toAdd);

            $stack->Begin();

            self::StackRecursiveHelper($stack, $format, $open, $close, $headOpen);
        }
        return $headClose;
    }
}
