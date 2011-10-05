<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace HypoConf\ConfigParser\Action;

use PEAR2\Console\CommandLine;

/**
 * Class that represent the StoreOnOff action.
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
        if(!isset($params['onValue'])) $onValue = 'on';
        else $onValue = $params['onValue'];
        if(!isset($params['offValue'])) $offValue = 'off';
        else $offValue = $params['offValue'];
        
        if($value !== -1)
        {
            if($value == true) $this->setResult($onValue);
            elseif($value == false) $this->setResult($offValue);
        }
        else $this->setResult(false);
    }
    // }}}
}
