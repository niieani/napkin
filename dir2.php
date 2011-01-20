<?php
// http://phpedia.pl/wiki/RecursiveDirectoryIterator

//$Directory = new DirList('.');

function getAllYaml($path)
{
$Directory = new RecursiveDirectoryIterator($path);
$Iterator = new RecursiveIteratorIterator($Directory);
$Regex = new RegexIterator($Iterator, '/^.+\.yml$/i', RecursiveRegexIterator::GET_MATCH);
$Yamls = array();

foreach ($Regex as $File)
{
	$Yamls[] = $File[0];
//	echo $File[0].PHP_EOL;
}

}

//$DirIterator = new RecursiveIteratorIterator(new DirList('./'));
//$DirIterator = new DirList('./');
//$DirIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('./'));
/*
try
{
foreach ($DirIterator as $File) {
//   if ( (!$DirIterator->isDot()) && ($File->getFilename() != basename($_SERVER['PHP_SELF'])) ) {
  if(!$DirIterator->isDot())
  {
	//if($Directory->isDir())
	//{
	//  echo "(Dir) ".$File->getPathname().PHP_EOL;
	//}
	//else
	//{
	  if (strstr($File->getFilename(),'.yml')) {
	    //if (!$entry['handle'] = fopen($path,'r')) $entry['handle'] = "FAIL";
	//echo ($DirIterator->isDir()) ? "(Dir) ".$File->getPathname() : $File->getPathname();
	  echo $File->getPathname().PHP_EOL;
	  }
	//}
  }
}
}
catch(Exception $e)
{
echo $e->getMessage();
}
*/

/*
function cmpSPLFileInfo( $splFileInfo1, $splFileInfo2 )
{
    return strcmp( $splFileInfo1->getFileName(), $splFileInfo2->getFileName() );
}

class DirList extends RecursiveDirectoryIterator
{
    private $dirArray;

    public function __construct( $p )
    {
        parent::__construct( $p );
        $this->dirArray = new ArrayObject();
        foreach( $this as $item )
        {
            $this->dirArray->append( $item );
        }
        $this->dirArray->uasort( "cmpSPLFileInfo" );
    }

    public function getIterator()
    {
        return $this->dirArray->getIterator();
    }

}
*/
?>

<?php
/*
echo "<select name=\"file\">\n";
foreach (new DirectoryIterator('.') as $file) {
   // if the file is not this file, and does not start with a '.' or '..',
   // then store it for later display
   if ( (!$file->isDot()) && ($file->getFilename() != basename($_SERVER['PHP_SELF'])) ) {
      echo "<option>";
      // if the element is a directory add to the file name "(Dir)"
      echo ($file->isDir()) ? "(Dir) ".$file->getFilename() : $file->getFilename();
      echo "</option>\n";
   }
}
echo "</select>\n";
*/
?>
