<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

//namespace ConfigParser;

/**
 * Required by this class.
 */
require_once 'Console/CommandLine/Action.php';

/**
 * Class that represent the IPPort action.
 */
class Action_IPPort extends \Console_CommandLine_Action
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
