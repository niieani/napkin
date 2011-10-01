<?php

namespace Tools;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Finder\Finder;

class FileOperation
{
    public static function getAllFilesByExtension($path='.', $extension = 'yml')
    {
        if(is_dir($path))
        {
            $files = array();
            $finder = new Finder();
            $finder->files()->name('*.'.$extension)->in($path)->sortByName();

            foreach ($finder as $file)
            {
                $files[] = $file->getRealpath();
//              print $file->getRealpath()."\n";
            }
            return $files;
        }
        else
        {
            user_error('No such directory: '.$path, E_USER_ERROR);
            return false;
        }
    }

    public static function findFile($name, $path='.', $extension = 'yml')
    {
        if(is_dir($path))
        {
            $files = array();
            $finder = new Finder();
            $finder->files()->name($name.'.'.$extension)->in($path)->sortByName();

            foreach ($finder as $file)
            {
                $files[] = $file->getRealpath();
            }
            return $files;
        }
        else
        {
            user_error('No such directory: '.$path, E_USER_ERROR);
            return false;
        }
    }
    
    public static function ToYAMLFile($array, $stdout = false, $file="tmp.yml")
    {
            $dumper = new Dumper();
            $yaml = $dumper->dump($array, 6);
            if ($stdout === false) file_put_contents($file, $yaml);
            else print PHP_EOL.$yaml;
    }
    
    public static function pathinfo_utf($path) 
    { 
        if (strpos($path, '/') !== false) $basename = end(explode('/', $path)); 
        elseif (strpos($path, '\\') !== false) $basename = end(explode('\\', $path)); 
        else return false; 
        if (empty($basename)) return false; 
        
        $dirname = substr($path, 0, strlen($path) - strlen($basename) - 1); 
        
        if (strpos($basename, '.') !== false) 
        { 
            $extension = end(explode('.', $path)); 
            $filename = substr($basename, 0, strlen($basename) - strlen($extension) - 1); 
        } 
        else 
        { 
            $extension = ''; 
            $filename = $basename; 
        } 
        
        return array 
        ( 
            'dirname' => $dirname, 
            'basename' => $basename, 
            'extension' => $extension, 
            'filename' => $filename 
        );
    }
    
    public static function CreateEmptyFile($path)
    {
        // TODO: this needs proper error handling!
        fclose(fopen($path, 'x'));
    }

    //---: Consider using glob() function instead (whichever is faster?) http://php.net/manual/en/function.glob.php
    /*
     * DEPRACATED by Symfony/Finder: TODO: this is actually a bit faster than Symfony/Finder
     * 
    public static function getAllFilesByExtension($path='.', $extension = 'yml')
    {
        if(is_dir($path))
        {
            $Directory = new \RecursiveDirectoryIterator($path);
            $Iterator = new \RecursiveIteratorIterator($Directory);
            $Regex = new \RegexIterator($Iterator, '/^.+\.'.$extension.'$/i', \RecursiveRegexIterator::GET_MATCH);
            $Files = array();

            foreach ($Regex as $File)
            {
                    $Files[] = $File[0];
            }
            sort($Files, SORT_LOCALE_STRING);
            return $Files;
        }
        else
        {
            user_error('No such directory: '.$path, E_USER_ERROR);
            return false;
        }
    }
    */
}