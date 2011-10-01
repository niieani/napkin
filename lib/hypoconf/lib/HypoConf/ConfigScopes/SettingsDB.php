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
    
    public function ReturnYAML($path = false)
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

    /*
    protected function ApplyDefaultsNonIterative(array &$setting, array &$defaultsDefinitions, $path, $nameOfObjectToApply = 'defaults')
    {
        $child = StringTools::ReturnLastBit($path);
        $parent = StringTools::DropLastBit($path);

        $parentConfig = ArrayTools::accessArrayElementByPath(&$setting, $parent);

        LogCLI::MessageResult('Parent '.LogCLI::GREEN.$parent.LogCLI::RESET, 6, LogCLI::INFO);

        $thisDefault = &$defaultsDefinitions;
        //$thisDefault = ArrayTools::accessArrayElementByPath(&$defaultsDefinitions, $parent);


        if(!isset($thisDefault[$nameOfObjectToApply]))
            return false;

        $defaults = $thisDefault[$nameOfObjectToApply];

        $config = &$parentConfig[$child];

        LogCLI::MessageResult('Applying non-iterative defaults to object '.LogCLI::GREEN.$path.LogCLI::RESET, 6, LogCLI::INFO);
        $config = ArrayTools::MergeArrays($defaults, $config);
        var_dump($config);

        ArrayTools::replaceArrayElementByPath($this->DB, $path, $config);
    }
    */

    // useful for pre-compilation of config file
    protected function ApplyDefaults(array &$setting, array &$defaultsDefinitions, $path, $nameOfObjectToApply = 'defaults', $directDefinition = false, $applyToAll = false)
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

        if(!isset($thisDefault[$nameOfObjectToApply]))
            return false;

        $defaults = $thisDefault[$nameOfObjectToApply];

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

    public function MergeOneByPath($path, $setting)
    {
        // verify this works?
        ArrayTools::mergeArrayElementByPath($this->DB, $path, $setting, 0); //skip any or not?
    }

    public function ReturnOneByPath($path)
    {
        return ArrayTools::accessArrayElementByPath($this->DB, $path);
    }
    
    public function RemoveByPath($path)
    {
        ArrayTools::unsetArrayElementByPath($this->DB, $path); //skip any or not?
    }

    /**
     * @param string    $path     where to merge
     * @param array     $setting  what to merge
     *
     * @return int                number of iteration of the newly merged/added element
     */
    public function MergeOneIterativeByPath($path, $setting)
    {
        return end(array_keys(ArrayTools::mergeArrayElementByPath($this->DB, $path, array($setting), 0, true))); //do not override, iterative element!
        //return number of iteration
    }
    
    //public function ReplaceFromArray(array $settingsArray, $applyDefaults = false)
    
    public function MergeFromArray(array $settingsArray, $applyDefaults = false, $mergeDefaults = true)
    {
        if($mergeDefaults === true) $this->MergeDefaultsDB($settingsArray, &$this->defaultsDefinitions, $applyDefaults);
        $this->DB = ArrayTools::MergeArrays($this->DB, $settingsArray);
    }
    
    public function MergeDefaultsDB(array $settingsArray, array &$defaultsDefinitions, $applyDefaults = false)
    {
        if($defaultsDefinitionsPaths = ArrayTools::TraverseTree($settingsArray, 'defaults'))
        {
            //LogCLI::MessageResult('Defaults definitions found!', 5, LogCLI::INFO);
            //var_dump($defaultsDefinitionsPaths);
            foreach($defaultsDefinitionsPaths as $defaultsPath)
            {
                LogCLI::MessageResult('Copying to defaults storage: '.LogCLI::BLUE.$defaultsPath.LogCLI::RESET, 5, LogCLI::INFO);

                //LogCLI::MessageResult(LogCLI::BLUE.$defaultsPath.LogCLI::RESET, 5, LogCLI::INFO);
                //var_dump($defaultsPath);

                // let's copy all the definitions and merge them, overriding any previously set settings in this instance
//                ArrayTools::mergeArrayElementByPath($this->defaultsDefinitions, $defaultsPath, ArrayTools::accessArrayElementByPath($settingsArray, $defaultsPath), 0);
                ArrayTools::mergeArrayElementByPath(&$defaultsDefinitions, $defaultsPath, ArrayTools::accessArrayElementByPath($settingsArray, $defaultsPath), 0);

                // remove the defaults, not needed anymore, have them in a separated array
                $this->RemoveByPath($defaultsPath);
                
                // optionally apply them to all elements
                if($applyDefaults === true)
                {
                    $this->ApplyDefaults(&$this->DB, &$defaultsDefinitions, $defaultsPath, 'defaults', false, true);
//                    $this->ApplyDefaults(&$this->DB, &$this->defaultsDefinitions, $defaultsPath, 'defaults', true);
                }
            }
        }
    }
    
    /**
     * @param string $file path to a YAML file
     * @param string|bool $path start the merge at designated settings path
     * @param bool $applyDefaults
     * @param bool $mergeDefaults
     * @param bool $createNewIfNonexistant
     * @return void
     */
    public function MergeFromYAML($file, $path = false, $applyDefaults = false, $mergeDefaults = true, $createNewIfNonexistant = false, $addFilename = false)
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
                        //var_dump("DUPA");
                        if($mergeDefaults === true) $this->MergeDefaultsDB($config, &$this->defaultsDefinitions, $applyDefaults);
                    }
                    else
                    {
                        if($addFilename === true)
                        {
    //                      $fileInfo = FileOperation::pathinfo_utf($file);
    //                      $config['filename'] = $fileInfo['filename'];
                            $config['filename'] = $file;
                            //var_dump($config);
                        }

                        $iteration = $this->MergeOneIterativeByPath($path, &$config);
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
                                            $parentConfig = array('defaults' => Yaml::parse($parentFile));
                                            $this->parentDefinitions[$parentFile] = array();
                                            $this->MergeDefaultsDB($parentConfig, &$this->parentDefinitions[$parentFile], false);
                                        }
        //                                $this->ApplyDefaultsNonIterative(&$this->DB, &$this->parentDefinitions[$parentFile], $path.'/'.$iteration, 'defaults', false);
                                        $this->ApplyDefaults(&$this->DB, &$this->parentDefinitions[$parentFile], $path.'/'.$iteration, 'defaults', true, false); //defaultsDefinitions
                                        LogCLI::Result(LogCLI::OK);
                                    }
                                    //var_dump($this->parentDefinitions);
                                }
                            }
                        }
                        
                        if($applyDefaults === true) $this->ApplyDefaults(&$this->DB, &$this->defaultsDefinitions, $path.'/'.$iteration, 'defaults', false, false); //defaultsDefinitions

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
                    $this->MergeFromYAML($file, $path, $applyDefaults, $mergeDefaults, false);
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
    
    public function MergeFromYAMLs(array $files, $path = false, $applyDefaults = false, $mergeDefaults = true, $createNewIfNonexistant = false, $addFilename = false)
    {
        foreach($files as $file)
        {
            $this->MergeFromYAML($file, $path, $applyDefaults, $mergeDefaults, $createNewIfNonexistant, $addFilename);
        }
    }
}