<?php
require_once 'Console/ProgressBar.php';
$bar = new Console_ProgressBar('* %fraction% [%bar%] %percent%', '=>', '-', 76, 7);

for ($i = 0; $i <= 7; $i++) {

    $bar->update($i);

    sleep(1);
}

print "\n";
?>
