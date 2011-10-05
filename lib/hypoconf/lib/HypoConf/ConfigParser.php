<?php

namespace HypoConf;

use HypoConf;
use Tools\LogCLI;
use Tools\ParseTools;
use Tools\ArrayTools;
use Tools\StringTools;
use PEAR2\Console\CommandLine;

class ConfigParser extends CommandLine
{
    public $template;
    public $configuration = array();
    public $foreignSettings = array();

    /**
     * Array of options that must be dispatched at the end.
     *
     * @var array $_dispatchLater Options to be dispatched
     */
    private $_dispatchLater = array();
    
    /**
     * Array of valid actions for an option, this array will also store user 
     * registered actions.
     *
     * The array format is:
     * <pre>
     * array(
     *     <ActionName:string> => array(<ActionClass:string>, <builtin:bool>)
     * )
     * </pre>
     *
     * @var array $actions List of valid actions
     */
    public static $actions = array(
        'IPPort'              => array('HypoConf\\ConfigParser\\Action\\IPPort', true),
        'StoreOnOff'          => array('HypoConf\\ConfigParser\\Action\\StoreOnOff', true),
        'StoreStemOrFalse'    => array('HypoConf\\ConfigParser\\Action\\StoreStemOrFalse', true),
        'StoreStringOrFalse'  => array('HypoConf\\ConfigParser\\Action\\StoreStringOrFalse', true)
    );
    
    public function __construct(array $params = array()) 
    {
        if (isset($params['name'])) {
            $this->name = $params['name'];
        }
        if (isset($params['description'])) {
            $this->description = $params['description'];
        }
        if (isset($params['version'])) {
            $this->version = $params['version'];
        }
        if (isset($params['template'])) {
            // cutting out the @@ from the dynamically loaded elements
            $this->template = preg_replace('/@@(\w+)@@/', '${1}', $params['template']);
            //$this->template = preg_replace('/@!@(\w+)@!@/', '${1}', $this->template);
        }
        if (isset($params['configuration'])) {
            $this->configuration = $params['configuration'];
        }
        if (isset($params['foreignSettings'])) {
            $this->foreignSettings = $params['foreignSettings'];
        }
        
        //parent::__construct();
        $this->add_help_option = false;
        $this->add_version_option = false;
        
        // set default instances
        $this->renderer         = new CommandLine\Renderer_Default($this);
        $this->outputter        = new CommandLine\Outputter_Default();
        $this->message_provider = new CommandLine\MessageProvider_Default();
    }

    public function loadConfiguration(&$configuration, $path = null, $iterativeScope = null)
    {
        if(!is_array($configuration)) $configuration = (array) $configuration;
        if(!empty($configuration))
        {
            LogCLI::MessageResult("Mapping configuration to: ".LogCLI::BLUE."$path".LogCLI::RESET, LogCLI::INFO);
            $this->configuration = empty($path) ? $configuration : ArrayTools::accessArrayElementByPath($configuration, $path);
            /*
            if(!empty($iterativeScope) && !ArrayTools::isIterativeScope($this->configuration))
            {
                LogCLI::MessageResult('Non-iterative format, translating...', LogCLI::INFO);
                $this->configuration = ArrayTools::translateToIterativeScope($iterativeScope, $this->configuration);
            }
            */

            if(!empty($this->foreignSettings))
            {
                foreach($this->foreignSettings as $foreignSetting)
                {
                    $foreignPath = StringTools::DropLastBit($path, $foreignSetting[0]); //.'/'.$foreignSetting[1]
                    $foreignValue = ArrayTools::accessArrayElementByPath(&$configuration, $foreignPath.'/'.$foreignSetting[1]);
                    $this->configuration[$foreignSetting[2]] = $foreignValue;
                }
            }
            return $this->configuration;
        }
        return false;
        //return $this->configuration = self::makeConfiguration(&$configuration, $path, $iterativeScope);
    }

    /*
    public static function makeConfiguration(&$configuration, $path = null, $iterativeScope = null)
    {
        if(is_array($configuration) && !empty($configuration))
        {
            $outConfiguration = empty($path) ? $configuration : ArrayTools::accessArrayElementByPath($configuration, $path);
            if(!empty($iterativeScope) && !ArrayTools::isIterativeScope($outConfiguration))
            {
                LogCLI::MessageResult('Non-iterative format, translating...', LogCLI::INFO);
                $outConfiguration = ArrayTools::translateToIterativeScope($iterativeScope, $outConfiguration);
            }
            return $outConfiguration;
        }
        return false;
    }
    */

    public function getParsed()
    {
        return trim($this->parse()->parsed);
    }

    // }}}
    // addSetting() {{{
    
    /**
     * Adds a setting
     *
     * @param mixed $name   A string containing the option name or an
     *                      instance of ConfigParser_Option
     * @param array $params An array containing the option attributes
     *
     * @return ConfigParser_Option The added option
     * @see    ConfigParser_Option
     */
    public function addSetting($name, $params = array())
    {
        //include_once 'Parser/ConfigParser/Option.php';
        if ($name instanceof Setting) {
            $opt = $name;
        } else {
            $opt = new ConfigParser\Setting($name, $params);
        }
        $opt->validate();
        if ($this->force_options_defaults) {
            $opt->setDefaults();
        }
        $this->options[$opt->name] = $opt;
        return $opt;
    }
    
    public function parseResult($userConfiguration = null)
    {
        $result = $this->parse($userConfiguration);
        return trim($result->parsed);
    }
    
    public function parse($userConfiguration = null)
    {
        $result = new CommandLine\Result();
        
        $output = null;

        //TODO: checking for is_array probably not required anymore
        if(is_array($this->configuration))
        {
            /*
             * checking if this is iterative
             */
            if(isset($this->configuration[0]) && is_array($this->configuration[0]))
            {
                //var_dump($this->configuration);
                foreach($this->configuration as $key => &$configuration)
                {
                    if(is_numeric($key))
                    {
                        $output .= $this->parseWithConfig($configuration, $result);
                    }
                    //else break; //after first non-numeric it will break the loop
                }
            }
            else
            {
                $output .= $this->parseWithConfig($this->configuration, $result);
            }
        }
        else
        {
            $output .= $this->parseWithConfig((array)$this->configuration, $result);
        }
        
        $result->parsed = $output;
        
        // dispatch deferred options
        foreach ($this->_dispatchLater as $optArray) {
            $optArray[0]->dispatchAction($optArray[1], $optArray[2], $this);
        }
        return $result;
    }
    
    
    private function parseWithConfig($configuration, $result)
    {
        $output = null;
        foreach ($this->options as $option)
        {
            /*
             * if there are multiple paths for one setting
             */
            if(is_array($option->path))
            {
                foreach($option->path as $config => $path)
                {
                    //($setting = ArrayTools::accessArrayElementByPath($configuration, $path)) !== null ?: $setting = $option->default[$config];
                    $setting = ArrayTools::accessArrayElementByPath($configuration, $path);
                    if ($setting === null)
                    {
                        if($option->default[$config] !== null)
                            LogCLI::MessageResult('Setting '.$config.' not set, defaulting to: '.$option->default[$config], 7, LogCLI::OK);
                        $setting = $option->default[$config];
                    }
                    $values[$config] = $setting;
                }
                $this->_dispatchAction($option, $values, $result);
            }
            else
            {
                //($setting = ArrayTools::accessArrayElementByPath($configuration, $option->path)) !== null ?: $setting = $option->default;
                $setting = ArrayTools::accessArrayElementByPath($configuration, $option->path);

                if ($setting === null)
                {
                    $option->setDefaults();
                    if($option->default !== null)
                        LogCLI::MessageResult('Setting '.$option->name.' not set, defaulting to: '.$option->default, 7, LogCLI::OK);
                    $setting = $option->default;
                }
                //var_dump($setting);

                /*
                 *  If we got an array, how do we divide it?
                 *  By default by ' ' (space), but sometimes we want eg. PHP_EOL, or comma.
                 */
                $value = StringTools::makeList(&$setting, $option->divideBy);

                $this->_dispatchAction($option, $value, $result);
            }
        }

        //foreach(preg_split("/(\r?\n)/", $this->template) as $line)
        foreach(explode(PHP_EOL, $this->template) as $line)
        {
            //$parsedline = ParseTools::sprintfn($line, $result->options);
            $parsedline = ParseTools::parseStringWithReplacementList($line, $result->options);
            
            // if all we got is whitespace, don't add it
            if(strlen(rtrim($parsedline)) < 1) continue;
            
            // if we got a multiline responds we have to indent it
            // TODO: maybe explode "\n" or PHP_EOL would be better?
//            if(count($lines = preg_split("/(\r?\n)/", $parsedline)) > 1)
            if(count($lines = explode(PHP_EOL, $parsedline)) > 1)
            {
                $indentedlines = array_shift($lines).PHP_EOL;
                foreach($lines as &$multiline)
                {
                    $indentedlines .= StringTools::indentLinesToMatchOther(trim($line), $line, $multiline).PHP_EOL;
                }
                $parsedline = rtrim($indentedlines);
            }
            $output .= $parsedline.PHP_EOL;
        }
        return $output;
    }
    
    // _dispatchAction() {{{
    
    /**
     * Dispatches the given option or store the option to dispatch it later.
     *
     * @param Console_CommandLine_Option $option The option instance
     * @param string                     $token  Command line token to parse
     * @param Console_CommandLine_Result $result The result instance
     *
     * @return void
     */
    private function _dispatchAction($option, $token, $result)
    {
        //var_dump($token);
        $option->dispatchAction($token, $result, $this);
    }
    // }}}
}
