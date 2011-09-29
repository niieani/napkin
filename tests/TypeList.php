<?php
/**
 * User: NIXin
 * Date: 24.09.2011
 * Time: 13:02
 */

use Tools\StringTools;

include ('../autoload.php');

var_dump(StringTools::TypeList('mambo,@jambo,+camper', array('@')));
