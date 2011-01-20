<?php 
function listdir($dir='.') { 
    if (!is_dir($dir)) { 
        return false; 
    } 
    
    $files = array(); 
    listdiraux($dir, $files); 

    return $files; 
} 

function listdiraux($dir, &$files) { 
    $handle = opendir($dir); 
    while (($file = readdir($handle)) !== false) { 
        if ($file == '.' || $file == '..') { 
            continue; 
        } 
        $filepath = $dir == '.' ? $file : $dir . '/' . $file; 
        if (is_link($filepath)) 
            continue; 
        if (is_file($filepath)) 
            $files[] = $filepath; 
        else if (is_dir($filepath)) 
            listdiraux($filepath, $files); 
    } 
    closedir($handle); 
} 

$files = listdir('.'); 
sort($files, SORT_LOCALE_STRING); 

foreach ($files as $f) { 
  if (strstr($f,'.yml')) {
    //if (!$entry['handle'] = fopen($path,'r')) $entry['handle'] = "FAIL";
	echo $f, "\n";
  }
//    echo  $f, "\n"; 
} 
?>
