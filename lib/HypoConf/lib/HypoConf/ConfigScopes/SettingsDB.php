<?php
namespace HypoConf\ConfigScopes;

use HypoConf;

use \Tools\LogCLI;
use \Tools\FileOperation;
use \Tools\ArrayTools;
use \Tools\StringTools;
use \Symfony\Component\Yaml\Yaml;


class SettingsDB
{
    public $DB = array();
    protected $defaultsDefinitions;
    
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
    
    // useful for pre-compilation of config file
    public function ApplyDefaultsToAllElements(array &$setting, $path, $nameOfObjectToApplyToAll = 'defaults', $parentGiven = false)
    {
        if($parentGiven === false)
        {
            $parent = StringTools::DropLastBit($path);
            $objectName = StringTools::DropLastBit($parent, -1);
            
            $parentConfig = ArrayTools::accessArrayElementByPath(&$setting, $parent);
            //var_dump($parentConfig);
        }
        else
        {
            $parent = $path;
            $objectName = $path;
            // not actually the parent, need to change the naming scheme
            $parentConfig = ArrayTools::accessArrayElementByPath(&$setting, $path);
        }
        
        $thisDefault = ArrayTools::accessArrayElementByPath(&$this->defaultsDefinitions, $parent);
        //$defaults = $parentConfig[$nameOfObjectToApplyToAll];
        $defaults = $thisDefault[$nameOfObjectToApplyToAll];
        
        foreach($parentConfig as $key => &$config)
        {
            if(is_numeric($key))
            {
                LogCLI::MessageResult('Applying defaults of: '.LogCLI::BLUE.$path.LogCLI::RESET.' to objects ['.LogCLI::GREEN.$objectName.LogCLI::RESET.'], iteration nr '.LogCLI::YELLOW.$key.LogCLI::RESET, 6, LogCLI::INFO);
                // this will not override any configs, only merge in the ones that were not set
                //$config = array_merge_recursive($config, $defaults);
                $config = ArrayTools::MergeArrays($defaults, $config);
            }
        }
        ArrayTools::replaceArrayElementByPath($this->DB, $parent, $parentConfig); 
    }
    
    public function MergeOneByPath($path, $setting)
    {
        // verify this works?
        ArrayTools::createArrayElementByPath($this->DB, $path, $setting, 0); //skip any or not?
        //var_dump($this->DB);
    }
    
    public function RemoveByPath($path)
    {
        //var_dump($this->DB);
        ArrayTools::unsetArrayElementByPath($this->DB, $path); //skip any or not?
        //var_dump($this->DB);
        //var_dump($this->DB);
    }
    
    public function MergeOneIterativeByPath($path, $setting)
    {
        // verify this works?
        ArrayTools::createArrayElementByPath($this->DB, $path, array($setting), 0, true); //do not override, iterative element!
        //var_dump($this->DB);
    }
    
    //public function ReplaceFromArray(array $settingsArray, $addDefaults = false)
    
    public function MergeFromArray(array $settingsArray, $addDefaults = false, $mergeDefaults = true)
    {
        if($mergeDefaults === true) $this->MergeDefaultsDB($settingsArray, $addDefaults);
        $this->DB = ArrayTools::MergeArrays($this->DB, $settingsArray);
    }
    
    public function MergeDefaultsDB(array $settingsArray, $addDefaults = false)
    {
        if($defaultsDefinitionsPaths = ArrayTools::TraverseTree($settingsArray, 'defaults'))
        {
            //LogCLI::MessageResult('Defaults definitions found!', 5, LogCLI::INFO);
            //var_dump($defaultsDefinitionsPaths);
            foreach($defaultsDefinitionsPaths as $defaultsPath)
            {
                LogCLI::MessageResult('Copying to defaults storage: '.LogCLI::BLUE.$defaultsPath.LogCLI::RESET, 5, LogCLI::INFO);
                // let's copy all the definitions and merge them, overriding any previously set settings in this instance
                ArrayTools::createArrayElementByPath($this->defaultsDefinitions, $defaultsPath, ArrayTools::accessArrayElementByPath($settingsArray, $defaultsPath), 0);
                
                // remove the defaults, not needed anymore, have them in a separated array
                self::RemoveByPath($defaultsPath);
                
                // optionally apply them
                if($addDefaults === true)
                {
                    self::ApplyDefaultsToAllElements(&$this->DB, $defaultsPath);
                }
            }
        }
    }
    
    //public function ApplyAllDefaultsToAllElements()
    
    public function MergeFromYAML($file, $path = false, $addDefaults = false, $mergeDefaults = true, $createNewIfNonexistant = true)
    {
        LogCLI::Message('Loading file: '.LogCLI::BLUE.$file.LogCLI::RESET, 1);
        if (file_exists($file))
        {
            LogCLI::Result(LogCLI::OK);
            LogCLI::Message('Parsing YAML file: '.LogCLI::BLUE.$file.LogCLI::RESET, 1);
            try
            {
                $config = YAML::load($file);
                
                // if the file is empty create an empty array:
                if(empty($config)) $config = array();
                
                if($path === false)
                {
                    $this->DB = ArrayTools::MergeArrays($this->DB, $config);
                    if($mergeDefaults === true) $this->MergeDefaultsDB($config, $addDefaults);
                }
                else
                {
                    self::MergeOneIterativeByPath($path, &$config);
                    if($addDefaults === true) self::ApplyDefaultsToAllElements(&$this->DB, $path, 'defaults', true); //defaultsDefinitions
                    //var_dump($this->defaultsDefinitions);
                }
                
                LogCLI::MessageResult('Settings DB updated!', 5, LogCLI::INFO);
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
            LogCLI::Fatal("No such file: $file");
            if($createNewIfNonexistant === true)
            {
                LogCLI::Message('Creating a new empty file.', 0);
                try
                {
                    fclose(fopen($file, 'x'));
                    self::MergeFromYAML($file, $path, $addDefaults, $mergeDefaults, false);
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
    
    public function MergeFromYAMLs(array $files, $path = false, $addDefaults = false, $mergeDefaults = true, $createNewIfNonexistant = true)
    {
        foreach($files as $i => $file)
        {
            $this->MergeFromYAML($file, $path, $addDefaults, $mergeDefaults, $createNewIfNonexistant);
        }
    }
}