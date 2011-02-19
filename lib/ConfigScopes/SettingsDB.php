<?php
namespace ConfigScopes;

use \Tools\LogCLI;
use \Tools\FileOperation;
use \Tools\ArrayTools;
use \Tools\StringTools;
use \Symfony\Component\Yaml\Yaml;


class SettingsDB
{
    protected $settingsDB = array();
    protected $defaultsDefinitions;
    
    public function __construct(array $preliminary = null)
    {
        if($preliminary !== null)
        {
            $this->settingsDB = $preliminary;
        }
    }
    
    public function ReturnYAML($path = false)
    {
        if($path === false)
            FileOperation::ToYAMLFile($this->settingsDB, true);
        else
            FileOperation::ToYAMLFile($this->settingsDB, false, $path);
    }
    
    // useful for pre-compilation of config file
    public static function ApplyDefaultsToAllElements(array &$setting, $path, $nameOfObjectToApplyToAll = 'defaults')
    {
        $parent = StringTools::DropLastBit($path);
        $objectName = StringTools::DropLastBit($parent, -1);
        
        $parentConfig = ArrayTools::accessArrayElementByPath($setting, $parent);
        //var_dump($parentConfig);
        $defaults = $parentConfig[$nameOfObjectToApplyToAll];
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
        //var_dump($parentConfig);
    }
    
    public function SetSettingByPath($path, $setting)
    {
        ArrayTools::createArrayElementByPath($this->settingsDB, $path, $setting, 0); //skip any or not?
        var_dump($this->settingsDB);
    }
    
    public function MergeFromArray(array $settingsArray, $addDefaults = false)
    {
        $this->MergeDefaultsDB($settingsArray, $addDefaults);
        $this->settingsDB = ArrayTools::MergeArrays($this->settingsDB, $settingsArray);
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
                
                // optionally apply them
                if($addDefaults === true)
                {
                    self::ApplyDefaultsToAllElements(&$settingsArray, $defaultsPath);
                }
            }
        }
    }
    
    public function MergeFromYAML($file, $addDefaults = false)
    {
        LogCLI::Message('Parsing YAML file: '.LogCLI::BLUE.$file.LogCLI::RESET, 1);
        try
        {
            $config = YAML::load($file);
            LogCLI::Result(LogCLI::OK);
            
            $this->MergeDefaultsDB($config, $addDefaults);
            
            $this->settingsDB = ArrayTools::MergeArrays($this->settingsDB, $config);
            
            LogCLI::MessageResult('Settings DB updated!', 5, LogCLI::INFO);
        }
        catch (Exception $e)
        {
            LogCLI::Result(LogCLI::FAIL);
            LogCLI::Fail($e->getMessage());
        }
    }
    
    public function MergeFromYAMLs(array $files, $addDefaults = false)
    {
        //$last = false;
        //$total = count($files) - 1;
        //$sites_defaults = array();
        foreach($files as $i => $file)
        {
            //if ($total == $i) $last = true;
            
            LogCLI::Message('Loading file: '.LogCLI::BLUE.$file.LogCLI::RESET, 1);
            if (file_exists($file))
            {
                LogCLI::Result(LogCLI::OK);
                $this->MergeFromYAML($file, $addDefaults);
            }
            else 
            {
                LogCLI::Result(LogCLI::FAIL);
                LogCLI::Fatal("No such file: $file");
            }
        }
    }
}