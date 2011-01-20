#!/usr/bin/env php
<?php
require_once __DIR__.'/autoload.php';
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Dumper;
use PwFisher\CommandLine\CommandLine;

$cmdline = new CommandLine;
$args = $cmdline->parseArgs($argv);

if ($argc <= 1 || in_array($argv[1], array('--help', '-help', '-h', '-?')))
{
?>
	Welcome to HypoConf v0.02 alpha!

	  Syntax:
	hypoconf add|rename -o=<filename.yml> [source.yml] [chainName] [value]
	hypoconf modify -o=<filename.yml> [source.yml] [chainToModify] [modification]
	hypoconf modifySite -o=<filename.yml> [source.yml] [siteToModify] [chainToModify] [modification]
	hypoconf generateOne -o=<filename.yml> [source.yml]
	hypoconf generateAll -o=<filename.yml> [path]
<?php
}

if(isset($args['d']))
{
	var_dump($args);
//	var_dump($argv);
}

$filename="default.yml";
if(isset($args["o"]))
{
	$filename=$args["o"];
	echo "Filename set\n";
}

//if(isset($argv[1])) switch($argv[1])
if(isset($args[0])) switch($args[0])
{
case 'debug_yaml':
	$config = YAML::load($args[1]);
	var_dump($config);
	break;

case 'generateOne':
	if(isset($args[1]))
	{
		// loading a YAML file or a YAML string
		$config = YAML::load($args[1]);
		
		if(isset($config['nginx.main']))
		{
		// get number of CPU cores (without counting HyperThreading)
		echo "Detecting number of CPU cores: ";
		$cpus = system("cat /proc/cpuinfo | grep \"core id\" | sort | uniq | wc -l");
		//system("clear");
		
		$output = "user ".$config['nginx.main']['user']." ".$config['nginx.main']['group'].";\n";
		$output .= "worker_processes ".$cpus.";\n";
		
		echo $output;
		echo "\n";
		}
		foreach($config['nginx.sites'] as $siteName => $siteData)
		{
			echo "site $siteName domain is: ".$siteData['domain']."\n";
		}
	}
	break;

case 'add':
	echo "Adding\n";
	//if(isset($argv[2]) && isset($argv[3]))
//	{
		$domain = array($args[1] => array("domain" => $args[1], "someinfo" => $args[2]));
		var_dump($domain);
		toFile($domain, $filename);
//	}
	break;

case 'rename':
        if($argc == 4)
	echo "Renaming\n";
	{
		$configfile=$args[1];
		$config = YAML::load($configfile);
		$edited[$args[3]]=$config[$args[2]];
		var_dump($edited);
//		unset($config);
		toFile($edited, $filename);
	}
	break;

case 'modify':
//        if($argc == 5)
        echo "Modifying\n";
//        {
                $config = YAML::load($args[1]);
                $config[$args[2]][$args[3]]=$args[4];
                var_dump($config);
                toFile($config, $filename);
//        }
        break;

case 'modifySite':
        echo "Modifying site\n";
                $config = YAML::load($args[1]);
                $config['nginx.sites'][$args[2]][$args[3]]=$args[4];
                var_dump($config);
                toFile($config, $filename);
        break;

case 'generateAll':

//  $result = process_dir($args[1],TRUE);
  $yamls = getAllYaml($args[1]);

 // Output each opened file and then close
/*  foreach ($result as $file) {
    if (is_resource($file['handle'])) {
        echo "\n\nFILE (" . $file['dirpath'].'/'.$file['filename'] . "):\n\n" . fread($file['handle'], filesize($file['dirpath'].'/'.$file['filename']));
        fclose($file['handle']);
    }
  }*/
  //var_dump($yamls);
	foreach($yamls as $yaml)
	{
		echo "\nGenerating from $yaml:\n";
		$config = YAML::load($yaml);
                foreach($config['nginx.sites'] as $siteName => $siteData)
                {
                        echo "site $siteName domain is: ".$siteData['domain']."\n";
                }
	}

}

function toFile($array, $file="tmp.yml")
{
        $dumper = new Dumper();
        $yaml = $dumper->dump($array, 3);
        file_put_contents(__DIR__.'/'.$file, $yaml);
}

function getAllYaml($path='.')
{
$Directory = new RecursiveDirectoryIterator($path);
$Iterator = new RecursiveIteratorIterator($Directory);
$Regex = new RegexIterator($Iterator, '/^.+\.yml$/i', RecursiveRegexIterator::GET_MATCH);
$Yamls = array();

foreach ($Regex as $File)
{
        $Yamls[] = $File[0];
//      echo $File[0].PHP_EOL;
}
sort($Yamls, SORT_LOCALE_STRING);
return $Yamls;

}


  function process_dir($dir,$recursive = FALSE) {
    if (is_dir($dir)) {
      for ($list = array(),$handle = opendir($dir); (FALSE !== ($file = readdir($handle)));) {
        if (($file != '.' && $file != '..') && (file_exists($path = $dir.'/'.$file))) {
          if (is_dir($path) && ($recursive)) {
            $list = array_merge($list, process_dir($path, TRUE));
          } else {
            $entry = array('filename' => $file, 'dirpath' => $dir);

 //---------------------------------------------------------//
 //                     - SECTION 1 -                       //
 //          Actions to be performed on ALL ITEMS           //
 //-----------------    Begin Editable    ------------------//
//	var_dump($entry);
  $entry['modtime'] = filemtime($path);

 //-----------------     End Editable     ------------------//
            do if (!is_dir($path)) {
 //---------------------------------------------------------//
 //                     - SECTION 2 -                       //
 //         Actions to be performed on FILES ONLY           //
 //-----------------    Begin Editable    ------------------//

  $entry['size'] = filesize($path);
  if (strstr(pathinfo($path,PATHINFO_BASENAME),'.log')) {
    if (!$entry['handle'] = fopen($path,'r')) $entry['handle'] = "FAIL";
  }

 //-----------------     End Editable     ------------------//
              break;
            } else {
 //---------------------------------------------------------//
 //                     - SECTION 3 -                       //
 //       Actions to be performed on DIRECTORIES ONLY       //
 //-----------------    Begin Editable    ------------------//

 //-----------------     End Editable     ------------------//
              break;
            } while (FALSE);
            $list[] = $entry;
          }
        }
      }
      closedir($handle);
      return $list;
    } else return FALSE;
  }
