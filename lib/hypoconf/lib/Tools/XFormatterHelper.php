<?php
/**
 * Created by JetBrains PhpStorm.
 * User: NIXin
 * Date: 25.09.2011
 * Time: 01:09
 * To change this template use File | Settings | File Templates.
 */

//use Symfony\Component\Console;
namespace Tools;

class XFormatterHelper extends Symfony\Component\Console\FormatterHelper
{

    /**
     * Formats a message as a block of text.
     *
     * @param string|array $messages The message to write in the block
     * @param string       $style    The style to apply to the whole block
     * @param Boolean      $large    Whether to return a large block
     *
     * @return string The formatter message
     */
    public function formatMultipleBlocks($messages, $style, $large = false)
    {
        $messages = (array) $messages;

        $len = 0;
        $lines = array();
        foreach ($messages as $message) {
            $lines[] = sprintf($large ? '  %s  ' : ' %s ', $message);
            $len = max($this->strlen($message) + ($large ? 4 : 2), $len);
        }

        $messages = $large ? array(str_repeat(' ', $len)) : array();
        foreach ($lines as $line) {
            $messages[] = $line.str_repeat(' ', $len - $this->strlen($line));
        }
        if ($large) {
            $messages[] = str_repeat(' ', $len);
        }

        foreach ($messages as &$message) {
            $message = sprintf('<%s>%s</%s>', $style, $message, $style);
        }

        return implode("\n", $messages);
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
