#!/usr/bin/env php
<?php
/*
    TODO:
- std::out generation (instead file output) as arg option
- Insert all relative nginx data
- Insert all PHP-FPM generation data
- Case is LINUX, MAC, BSD - change epoll
- Automatic recommended options for RAM/CPU (if not specified)
- Debug option
- Ability to quickly turn-off single website and replace with placeholder ("maintainance in progress")
- Ability to quickly turn-off a user and all of his websites
- Create directories (and users) for websites
- Automatic symlinks for all websites in one directory (enables quick listing of all websites on the server)
- Site-add wizard
- Defaults config file (where to store all websites, in what structure)
- Option to automatically reload nginx after applying
- Failsafe modify (only modify a website when using "modifySite", not "modify")
- Add option to chroot all sites by default
- Add option to run PHP-FPM under different user or user-by-site
- Specify output file(s) in config and use that as default (-o=filename.yml only override)
- Multi-file output (with includes) and single-file output
- Simple switches - CGI: PHP; CGI: Passanger; etc. (mutually exclusive, pick one)
- ovz-web-panel integration and GUI interface
- multiple server support
- per-site per-location deny (like /pma deny all)
- ability to set source IP/domain for 'allow' per-domain
- SSL cert creator
- MySQL database creation along with user or prefix user_ permission granting
- memcached support
- YAML storage:

./config.main.yml
./templates/website.yml
./templates/website.php.yml
./templates/website-ssl.yml
./websites/%server_hostname%/%username%.yml

*/

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
