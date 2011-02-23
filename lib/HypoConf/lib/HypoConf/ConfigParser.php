<?php

namespace HypoConf;

use HypoConf;
//use HypoConf\ConfigParser;
use \Tools\LogCLI;
use \Tools\ParseTools;
use \Tools\ArrayTools;
use \Tools\StringTools;
use \PEAR2\Console\CommandLine;
//use ConfigParser\Setting as ConfigParser_Setting;

//require_once 'Console/CommandLine.php';


class ConfigParser extends CommandLine
{
    public $template;
    public $configuration = array();
    
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
            //$this->template = $params['template'];
        }
        if (isset($params['configuration'])) {
            $this->configuration = $params['configuration'];
        }
        
        //parent::__construct();
        $this->add_help_option = false;
        $this->add_version_option = false;
        
        // set default instances
        $this->renderer         = new CommandLine\Renderer_Default($this);
        $this->outputter        = new CommandLine\Outputter_Default();
        $this->message_provider = new CommandLine\MessageProvider_Default();
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
        if(isset($this->configuration[0]))
        {
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
        foreach ($this->options as $name=>$option) 
        {
            if(is_array($option->path))
            {
                foreach($option->path as $config => $path)
                {
                    //($setting = ArrayTools::accessArrayElementByPath($configuration, $path)) !== null ?: $setting = $option->default[$config];
                    $setting = ArrayTools::accessArrayElementByPath($configuration, $path);
                    if ($setting === null) $setting = $option->default[$config];
                    $values[$config] = $setting;
                }
                $this->_dispatchAction($option, $values, $result);
            }
            else
            {
                //($setting = ArrayTools::accessArrayElementByPath($configuration, $option->path)) !== null ?: $setting = $option->default;
                $setting = ArrayTools::accessArrayElementByPath($configuration, $option->path);
                //var_dump($setting);
                if ($setting === null) $setting = $option->default;
                $value = &$setting;
                $this->_dispatchAction($option, $value, $result);
            }
        }
        
        foreach(preg_split("/(\r?\n)/", $this->template) as $line)
        {
            $parsedline = ParseTools::sprintfn($line, $result->options);
            
            // if all we got is whitespace, don't add it
            if(strlen(rtrim($parsedline)) < 1) continue;
            
            // if we got a multiline responds we have to indent it
            if(count($lines = preg_split("/(\r?\n)/", $parsedline)) > 1)
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
