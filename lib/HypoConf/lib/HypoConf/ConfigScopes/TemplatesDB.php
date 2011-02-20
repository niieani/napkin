<?php
namespace HypoConf\ConfigScopes;

use HypoConf;

use \Tools\FileOperation;
use \Tools\ArrayTools;
use \Tools\LogCLI;

class TemplatesDB
{
    public $DB = array();
    
    public function __construct(array $files = array())
    {
        if(!empty($files)) $this->AddFromFiles($files);
    }
    
    public function AddFromFile($file, $scopeUser = false)
    {
        LogCLI::Message('Adding template file: '.LogCLI::BLUE.$file.LogCLI::RESET, 4);
        try
        {
            $data = trim(file_get_contents($file));
            $scopeData = FileOperation::pathinfo_utf($file);
            $scopeName = ($scopeUser === false) ? $scopeData['filename'] : $scopeUser;
            
            $this->DB = ArrayTools::MergeArrays($this->DB, array($scopeName => $data));
            
            LogCLI::MessageResult('Templates DB updated with '.LogCLI::BLUE.$scopeName.LogCLI::RESET.' definition', 5, LogCLI::INFO);
            LogCLI::Result(LogCLI::OK);
        }
        catch (Exception $e)
        {
            LogCLI::Result(LogCLI::FAIL);
            LogCLI::Fail($e->getMessage());
        }
    }
    
    public function AddFromFiles(array $files)
    {
        foreach($files as $i => $file)
        {
            LogCLI::Message('Loading template file: '.LogCLI::BLUE.$file.LogCLI::RESET, 1);
            if (file_exists($file))
            {
                $this->AddFromFile($file);
                LogCLI::Result(LogCLI::OK);
            }
            else 
            {
                LogCLI::Result(LogCLI::FAIL);
                LogCLI::Fatal("No such file: $file");
            }
        }
    }
}