<?php

namespace Tools;

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
    
    function YAMLtoFile($array, $file="tmp.yml")
    {
            $dumper = new \Symfony\Component\Yaml\Dumper();
            $yaml = $dumper->dump($array, 3);
            file_put_contents(__DIR__.'/'.$file, $yaml);
    }
    
}