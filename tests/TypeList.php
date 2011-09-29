<?php
/**
 * User: NIXin
 * Date: 24.09.2011
 * Time: 13:02
 */

use Symfony\Component\Finder\Finder;

include ('../autoload.php');

$finder = new Finder();
$finder->files()->name('*.yml')->in('../database')->sortByName();

foreach ($finder as $file) {
    print $file->getRealpath()."\n";
}

