<?php

namespace Tools;

use \Symfony\Component\Yaml\Yaml;
use \Symfony\Component\Yaml\Dumper;

class FileOperation
{
    public static function getAllFilesByExtension($path='.', $extension = 'yml')
    {
        $Directory = new RecursiveDirectoryIterator($path);
    	$Iterator = new RecursiveIteratorIterator($Directory);
    	$Regex = new RegexIterator($Iterator, '/^.+\.'.$extension.'$/i', RecursiveRegexIterator::GET_MATCH);
    	$Files = array();
    
    	foreach ($Regex as $File)
    	{
    	        $Files[] = $File[0];
    	}
    	sort($Files, SORT_LOCALE_STRING);
    	return $Files;
    }
    
    public static function ToYAMLFile($array, $stdout = false, $file="tmp.yml")
    {
            $dumper = new Dumper();
            $yaml = $dumper->dump($array, 6);
            if ($stdout === false) file_put_contents($file, $yaml);
            else echo PHP_EOL.$yaml;
    }
    
}