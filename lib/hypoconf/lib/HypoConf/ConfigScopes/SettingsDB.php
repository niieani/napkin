<?php
namespace HypoConf\ConfigScopes;

use HypoConf;

use Tools\LogCLI;
use Tools\FileOperation;
use Tools\ArrayTools;
use Tools\StringTools;
use HypoConf\Paths;
use Symfony\Component\Yaml\Yaml;

class SettingsDB
{
    public $DB = array();
    protected $defaultsDefinitions = array();
    protected $parentDefinitions = array();
    
    public function __construct(array $preliminary = null)
    {
        if($preliminary !== null)
        {
            $this->DB = $preliminary;
        }
    }
    
    public function returnYAML($path = false)
    {
        if($path === false)
            FileOperation::ToYAMLFile($this->DB, true);
        else
        {
            LogCLI::Message('Saving file: '.LogCLI::BLUE.$path.LogCLI::RESET, 0);
            FileOperation::ToYAMLFile($this->DB, false, $path);
            LogCLI::Result(LogCLI::OK);
        }
    }

    // useful for pre-compilation of config file
    protected function applyDefaults(array &$setting, array &$defaultsDefinitions, $path, $nameOfObjectToApply = 'defaults', $directDefinition = false, $applyToAll = false)
    {
        if($applyToAll === false)
        {
            $child = StringTools::ReturnLastBit($path);
            $parent = StringTools::DropLastBit($path);
        }
        else
        {
            $parent = $path;
        }

        $parentConfig = ArrayTools::accessArrayElementByPath(&$setting, $parent);

        if($directDefinition === false)
            $thisDefault = ArrayTools::accessArrayElementByPath(&$defaultsDefinitions, $parent);
        else
            $thisDefault = &$defaultsDefinitions;

        if($nameOfObjectToApply === null)
            $defaults = &$thisDefault;
        elseif(!isset($thisDefault[$nameOfObjectToApply]))
            return false;
        else
            $defaults = $thisDefault[$nameOfObjectToApply];

        //var_dump($thisDefault);

        if($applyToAll === false)
        {
            $config = &$parentConfig[$child];
            if(is_numeric($child))
            {
                LogCLI::MessageResult('Applying defaults to object '.LogCLI::GREEN.$parent.'/'.LogCLI::YELLOW.$child.LogCLI::RESET, 6, LogCLI::INFO);

                // this will not override any configs, only merge in the ones that were not set
                $config = ArrayTools::MergeArrays($defaults, $config);
            }
        }
        else
        {
            foreach($parentConfig as $child => &$config)
            {
                if(is_numeric($child))
                {
                    LogCLI::MessageResult('Applying defaults to object '.LogCLI::GREEN.$parent.'/'.LogCLI::YELLOW.$child.LogCLI::RESET, 6, LogCLI::INFO);
                    // this will not override any configs, only merge in the ones that were not set
                    $config = ArrayTools::MergeArrays($defaults, $config);
                }
            }
        }
        // finally replace the array element
        ArrayTools::replaceArrayElementByPath($this->DB, $parent, $parentConfig);
    }

    /* not used anywhere yet */
    public function mergeOneByPath($path, $setting)
    {
        // verify this works?
        ArrayTools::mergeArrayElementByPath($this->DB, $path, $setting, 0); //skip any or not?
    }

    public function returnOneByPath($path)
    {
        return ArrayTools::accessArrayElementByPath($this->DB, $path);
    }
    
    public function removeByPath($path)
    {
        ArrayTools::unsetArrayElementByPath($this->DB, $path); //skip any or not?
    }

    /**
     * @param string    $path     where to merge
     * @param array     $setting  what to merge
     *
     * @return int                number of iteration of the newly merged/added element
     */
    public function mergeOneIterativeByPath($path, $setting)
    {
        return end(array_keys(ArrayTools::mergeArrayElementByPath($this->DB, $path, array($setting), 0, true))); //do not override, iterative element!
        //return number of iteration
    }

    public function mergeFromArray(array $settingsArray, $applyDefaults = false, $mergeDefaults = true, $path = 'defaults')
    {
        if($mergeDefaults === true) $this->mergeDefinitionsDB($settingsArray, &$this->defaultsDefinitions, $applyDefaults, $path);
        $this->DB = ArrayTools::MergeArrays($this->DB, $settingsArray);
    }
    
    public function mergeDefinitionsDB(array $settingsArray, array &$definitions, $applyDefaults = false, $path = 'defaults')
    {
        if($path !== null)
        {
            $definitionsPaths = ArrayTools::TraverseTree($settingsArray, $path);
            //var_dump($settingsArray);
//            $mergeNotReplace = ArrayTools::TraverseTree($settingsArray, 'custom');
            //var_dump($mergeNotReplace);
        }
        else
        {
            // TODO
        }
        /*
        if($mergeNotReplace)
        {
            foreach($mergeNotReplace as $mergePath)
            {
                LogCLI::MessageResult('Merging definitions in storage: '.LogCLI::BLUE.$mergePath.LogCLI::RESET, 5, LogCLI::INFO);
                var_dump(ArrayTools::accessArrayElementByPath($settingsArray, $mergePath));
                var_dump($definitions);
                // let's copy all the definitions and merge them, overriding any previously set settings in this instance
                ArrayTools::mergeArrayElementByPath(&$definitions, $mergePath, ArrayTools::accessArrayElementByPath($settingsArray, $mergePath), 0, true); // no override
                var_dump($definitions);
            }
        }
        */
        if($definitionsPaths)
        {
            foreach($definitionsPaths as $definitionsPath)
            {
                LogCLI::MessageResult('Copying to definitions storage: '.LogCLI::BLUE.$definitionsPath.LogCLI::RESET, 5, LogCLI::INFO);

                // let's copy all the definitions and merge them, overriding any previously set settings in this instance
                ArrayTools::mergeArrayElementByPath(&$definitions, $definitionsPath, ArrayTools::accessArrayElementByPath($settingsArray, $definitionsPath), 0);

                // remove the defaults, they are not needed anymore, we have them in a separated array
                $this->removeByPath($definitionsPath);
                
                // optionally apply them to all elements
                if($applyDefaults === true)
                {
                    $this->applyDefaults(&$this->DB, &$definitions, $definitionsPath, $path, false, true);
                }
            }
        }
    }

    public static function findPathForSetting(&$settings, $settingPath, $basicScope = false)
    {
        $searchResults = ArrayTools::TraverseTreeWithPath(&$settings, $settingPath);
        if(empty($searchResults))
        {
            LogCLI::MessageResult(LogCLI::YELLOW.'Sorry, no settings found for: '.LogCLI::BLUE.$settingPath.LogCLI::RESET, 0, LogCLI::INFO);
            return false;
        }
        else
        {
            if(count($searchResults['all'])>1) LogCLI::MessageResult(LogCLI::YELLOW.'Multiple settings found for: '.LogCLI::BLUE.$settingPath.LogCLI::RESET, 4, LogCLI::INFO);
            LogCLI::MessageResult(LogCLI::GREEN.'Best match: '.LogCLI::BLUE.$searchResults['best'].LogCLI::RESET, 0, LogCLI::INFO);

            $path = $searchResults['best'];

            if($basicScope !== false)
            {
                if(($pos = strpos($path, $basicScope)) !== false && $pos === 0)
                    $path = StringTools::DropLastBit($path, -1);
            }

            $parent = ArrayTools::accessArrayElementByPath(&$settings, StringTools::DropLastBit($searchResults['best']));
            if(isset($parent['iterative']))
                $path = StringTools::DropLastBit($path, -1);

            LogCLI::MessageResult('Fixed path: '.LogCLI::YELLOW.$searchResults['best'].' => '.LogCLI::BLUE.$path.LogCLI::RESET, 6, LogCLI::INFO);
            return $path;
        }
    }

    /*
     * TODO: whole logic of parsing multiple files should be extracted and put somewhere else
     * we should hold all the file contents and merge/override them only when needed (on demand)
     * this way we can do some nice dynamic things
     */
    /**
     * @param string $file          path to a YAML file
     * @param string|bool $path     start the merge at designated settings path
     * @param bool $applyDefaults
     * @param bool $mergeDefaults
     * @param bool $createNewIfNonexistant
     * @param bool $addFilename
     * @return void
     */
    public function mergeFromYAML($file, $path = false, $applyDefaults = false, $mergeDefaults = true, $createNewIfNonexistant = false, $addFilename = false)
    {
        LogCLI::Message('Loading file: '.LogCLI::BLUE.$file.LogCLI::RESET, 1);
        if (file_exists($file))
        {
            LogCLI::Result(LogCLI::OK);
            LogCLI::Message('Parsing YAML file: '.LogCLI::BLUE.$file.LogCLI::RESET, 1);
            try
            {
                //$config = YAML::load($file);
                $config = Yaml::parse($file);
//                 if the file is empty create an empty array:
//                    $config = array();
                if(!empty($config))
                {
                    if($path === false)
                    {
                        $this->DB = ArrayTools::MergeArrays($this->DB, $config);
                        if($mergeDefaults === true) $this->mergeDefinitionsDB($config, &$this->defaultsDefinitions, $applyDefaults, 'defaults');
                    }
                    else
                    {
                        if($addFilename === true)
                        {
                            $fileInfo = FileOperation::pathinfo_utf($file);
                            $config['filename'] = $fileInfo['filename'];
                            //$config['filename'] = $file;
                        }
                        if(!isset($config['template']) && (!isset($config['disabled']) || $config['disabled'] == false))
                        {
                            $iteration = $this->mergeOneIterativeByPath($path, &$config);
                            //var_dump($this->defaultsDefinitions);

                            /**
                             * applying parents as defaults
                             */
                            if(isset($config['parent']))
                            {
                                foreach((array) $config['parent'] as $parentName)
                                {
                                    if(is_array($parentFiles = FileOperation::findFile($parentName, Paths::$db)))
                                    {
                                        if(count($parentFiles) > 1)
                                        {
                                            LogCLI::MessageResult('You have to be more specific in the way you specify the parent, found more than one file under this name: '.$parentName, 0, LogCLI::INFO);
                                        }
                                        else
                                        {
                                            $parentFile = &current($parentFiles);

                                            LogCLI::Message('Parent definition found: '.LogCLI::BLUE.$parentFile.LogCLI::RESET.', parsing and merging.', 2);
                                            if(!isset($this->parentDefinitions[$parentFile]))
                                            {
                                                $parentConfig = array('parent' => Yaml::parse($parentFile));
                                                $this->parentDefinitions[$parentFile] = array();
                                                $this->mergeDefinitionsDB($parentConfig, &$this->parentDefinitions[$parentFile], false, 'parent');
                                            }
                                            $this->applyDefaults(&$this->DB, &$this->parentDefinitions[$parentFile], $path.'/'.$iteration, 'parent', true, false);
                                            LogCLI::Result(LogCLI::OK);
                                        }
                                        //var_dump($this->parentDefinitions);
                                    }
                                }
                            }

                            if($applyDefaults === true) $this->applyDefaults(&$this->DB, &$this->defaultsDefinitions, $path.'/'.$iteration, 'defaults', false, false);
                        }
                    }

                    LogCLI::MessageResult('Settings DB populated with new data!', 5, LogCLI::INFO);
                }
                else
                {
                    LogCLI::MessageResult('File empty, ignoring', 5, LogCLI::INFO);
                }
                LogCLI::Result(LogCLI::OK);
            }
            catch (\Exception $e)
            {
                LogCLI::Result(LogCLI::FAIL);
                LogCLI::Fail($e->getMessage());
            }
        }
        else 
        {
            LogCLI::Result(LogCLI::FAIL);
            LogCLI::Fail("No such file: $file");
            if($createNewIfNonexistant === true)
            {
                LogCLI::Message('Creating a new empty file.', 0);
                try
                {
                    //fclose(fopen($file, 'x'));
                    FileOperation::CreateEmptyFile($file);
                    $this->mergeFromYAML($file, $path, $applyDefaults, $mergeDefaults, false);
                    LogCLI::Result(LogCLI::OK);
                }
                catch (\Exception $e)
                {
                    LogCLI::Result(LogCLI::FAIL);
                    LogCLI::Fail($e->getMessage());
                }
            }
        }
    }
    
    public function mergeFromYAMLs(array $files, $path = false, $applyDefaults = false, $mergeDefaults = true, $createNewIfNonexistant = false, $addFilename = false)
    {
        foreach($files as $file)
        {
            $this->mergeFromYAML($file, $path, $applyDefaults, $mergeDefaults, $createNewIfNonexistant, $addFilename);
        }
    }
}