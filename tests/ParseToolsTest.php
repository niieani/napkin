<?php
/**
 * Created by Bazyli Brzoska
 * Date: 27.02.11
 * Time: 21:24
 */

require_once '../autoload.php';

use Tools\LogCLI;
use Tools\ParseTools;

LogCLI::SetVerboseLevel(10);

$toFormat = '[[gzip_buffers %(gzip_buffers_num)s %(gzip_buffers_size)s;]]';
$listFormat = array(
    "gzip_buffers_num" => 40,
    "gzip_buffers_size" => "6k"
);

ParseTools::sprintfnnew($toFormat, $listFormat);