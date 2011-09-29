<?php
/**
 * By: Bazyli Brzóska
 * Date: 25.09.2011
 * Time: 01:09
 */

namespace Tools;
use \Symfony\Component\Console\Helper\FormatterHelper;

class XFormatterHelper extends FormatterHelper
{
    /**
     * Formats multiple messages as side-by-side blocks of text, separated by $glue.
     *
     * @param array     $blocks   Multidimensional array with ['messages'] and ['style'] and optionally ['large'] for each block
     * @param string    $glue     What separates the blocks
     * @param bool      $large    Whether to return a large block
     * @param bool      $alignToCenter
     * @param bool      $copyLastLine
     *
     * @return string The formatter message
     */
    public function formatMultipleBlocks($blocks, $glue = ' ', $large = false, $alignToCenter = true, $copyLastLine = false)
    {
        $formattedBlocks = array();
        $output = null;
        $indent = array();
        $count = array();
        $start = array();
        $longest = 0;
        if($copyLastLine) $indentString = array();

        foreach($blocks as $id => &$block)
        {
            if(!isset($block['large'])) $block['large'] = $large;
            $formattedBlocks[$id] = explode(PHP_EOL, $this->formatBlock($block['messages'], $block['style'], $block['large']));
            if(($count[$id] = count($formattedBlocks[$id])) > $longest) $longest = $count[$id];
        }
        $middle = $longest / 2;
        foreach($blocks as $id => &$block)
        {
            $start[$id] = floor($middle - ($count[$id] / 2));
            $fragment = end($formattedBlocks[$id]).$glue;
            $indent[$id] = $this->strlen(str_replace(array("<{$block['style']}>", "</{$block['style']}>"), '', $fragment));
            if($copyLastLine) $indentString[$id] = $fragment;
        }
        for($i = 0; $i < $longest; ++$i)
        {
            foreach(array_keys($formattedBlocks) as $id) //foreach($blocks as $id => &$block)
            {
                $shiftI = $alignToCenter === true ? $i - $start[$id] : $i;
                if(isset($formattedBlocks[$id][$shiftI]))
                {
                    $output .= $formattedBlocks[$id][$shiftI].$glue;
                }
                else
                {
                    if($copyLastLine) $output .= $indentString[$id];
                    else $output .= str_repeat(' ', $indent[$id]);
                }
            }
            $output .= PHP_EOL;
        }
        return rtrim($output); // trim the last end of line
    }

    /**
     * Returns the length of a string, uses mb_strlen if it is available.
     *
     * @param string $string The string to check its length
     *
     * @return integer The length of the string
     */
    private function strlen($string)
    {
        return function_exists('mb_strlen') ? mb_strlen($string, mb_detect_encoding($string)) : strlen($string);
    }

    /**
     * Returns the helper's canonical name
     *
     * @return string The canonical name of the helper
     */
    public function getName()
    {
        return 'xformatter';
    }
}
