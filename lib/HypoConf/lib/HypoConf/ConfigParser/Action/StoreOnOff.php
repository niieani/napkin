<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace HypoConf\ConfigParser\Action;

use PEAR2\Console\CommandLine;

/**
 * Class that represent the StoreStringFalse action.
 */
class StoreOnOff extends CommandLine\Action
{
    // execute() {{{

    /**
     * Executes the action with the value entered by the user.
     *
     * @param mixed $value  The option value
     * @param array $params An array of optional parameters
     *
     * @return string or false
     */
    public function execute($value = -1, $params = array())
    {
        if($value !== -1)
        {
            if($value == true) $this->setResult('on');
            if($value == false) $this->setResult('off');
        }
        else $this->setResult(false);
    }
    // }}}
}
