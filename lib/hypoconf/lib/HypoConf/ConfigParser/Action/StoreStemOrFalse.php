<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace HypoConf\ConfigParser\Action;

use PEAR2\Console\CommandLine;

/**
 * Class that represent the StoreStringFalse action.
 */
class StoreStemOrFalse extends CommandLine\Action
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
    public function execute($value = false, $params = array())
    {
        //var_dump($value); var_dump($params);
        (!empty($value) && strlen((string)$value) != 0 && isset($params['template'])) ? $this->setResult('<<'.$params['template'].'>>') : $this->setResult(false);
    }
    // }}}
}
