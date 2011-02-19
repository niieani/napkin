<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace ConfigParser\Action;

use PEAR2\Console\CommandLine;

/**
 * Class that represent the IPPort action.
 */
class IPPort extends CommandLine\Action
{
    // execute() {{{

    /**
     * Executes the action with the value entered by the user.
     *
     * @param mixed $value  The option value
     * @param array $params An array of optional parameters
     *
     * @return string
     */
    public function execute($value = false, $params = array())
    {
        $left  = isset($value['ip']) ? (isset($value['port']) ? $value['ip'].(isset($value['delimiter']) ? $value['ip'].$value['delimiter'] : ':') : $value['ip']) : null;
        $right = isset($value['port']) ? $value['port'] : null;
        $value = $left.$right;
        $this->setResult((string)$value);
    }
    // }}}
}
