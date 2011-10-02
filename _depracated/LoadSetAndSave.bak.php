<?php
/**
 * User: NIXin
 * Date: 24.09.2011
 * Time: 00:07
 */

namespace HypoConf\ConsoleCommands;

use Symfony\Component\Console as Console;
use Tools\LogCLI;
use Tools\StringTools;
use Tools\Tree;
use HypoConf\ConfigScopes;
use HypoConf\ConfigScopes\ApplicationsDB;
use HypoConf\Paths;
//use HypoConf\Commands;
use HypoConf\Commands\Helpers;

class LoadSetAndSave extends Console\Command\Command
{
    protected function configure()
    {
        $this
            ->setName('set')
            ->setAliases(array('s'))
            ->setDescription('Changes the value and updates the config file')
            ->setHelp('Changes the value and updates the config file.')
            ->addArgument('name', Console\Input\InputArgument::REQUIRED, 'Site, user (when prefixed with @) or template (when prefixed with +)')
            ->addArgument('chain', Console\Input\InputArgument::REQUIRED, 'Configuration chain (eg. nginx/php)')
            ->addArgument('values', Console\Input\InputArgument::REQUIRED + Console\Input\InputArgument::IS_ARRAY, 'Values to set (can be multiple)');
//        $this->addOption('more', 'm', Console\Input\InputOption::VALUE_NONE, 'Tell me more');

    }
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $chain = $input->getArgument('chain');
        $values = $input->getArgument('values');

//        foreach($values as $value)
//        {
//            $output->writeln($name.' <question>'.$value.'</question>');
//        }

        //self::$ApplicationsDB = new ConfigScopes\ApplicationsDB();
//        $configScopesNginx = ApplicationsDB::LoadApplication('nginx');

        ApplicationsDB::LoadAll();

        /*
         * There needs to be a check wheter the 'set' operation is a reference
         * to a custom action (like DB create), or just a normal setting.
         */

        foreach(StringTools::TypeList($name) as $argument)
        {
            if ($argument['exclamation'] !== false)
            {
                // TODO: actually handle the exclamators
                LogCLI::MessageResult('Exclamation: '.$argument['exclamation'], 4, LogCLI::INFO);
            }
            else
            {
                if($argument['text'] == 'config')
                {
                    $file = Paths::$db.Paths::$separator.Paths::$hypoconf.Paths::$separator.Paths::$defaultUser.Paths::$separator.Paths::$defaultConfig;
                    $this->LoadAndSave($chain, $file, $values, 'defaults', $output);
                }
                /*
                 * If it's only a site edit, allow only what's in the 'server' scope (Commands\Set\Site)
                 */
                else // opening a site or default site
                {
                    $file = Paths::getFullPath($argument['text']);

                    if($file !== false)
                        $this->LoadAndSave($chain, $file, $values, 'server', $output);
//                        Commands\Set\Site::LoadAndSave($values, $siteYML);
                    else
                    {
                        LogCLI::Fail('Sorry, no site by name: '.$argument['text']);
                    }
                }
                /* TODO: add notification if modified the file or added a new option (important)
                */
            }
        }

    }

    //SITE
    protected function LoadAndSave($chain, $file, $values, $scope = '', Console\Output\OutputInterface $output)
    {
        if(empty($scope))
        {
            $settings = ApplicationsDB::GetSettingsList('nginx');
            $iterativeSetting = 'defaults';
        }
        else
        {
            $settings = ApplicationsDB::GetSettingsList('nginx', $scope);
            $iterativeSetting = 0;
        }

        $value = ArrayTools::dearraizeIfNotRequired($values);

        $chain = StringTools::Delimit($chain, '.');

        $settingPath = implode('/', $chain);

        // are we adding a setting or replacing/merging ? TODO: add check if the setting is iterative at all
        $doNotReplace = Helpers::DoWeReplaceHelper($chain, $settingPath);

        if($doNotReplace === true)
            $settingPath = StringTools::RemoveExclamation($settingPath); //remove the + from the beginning


        if($path = SettingsDB::SearchConfigs(&$settings, $settingPath, $iterativeSetting))
        {
            $settingsDB = new ConfigScopes\SettingsDB();

            // load the original file first
            $settingsDB->mergeFromYAML($file, false, false, false); //true for compilation

            $currentSetting = $settingsDB->returnOneByPath($settingPath);
//            LogCLI::MessageResult('Replace? '.$currentSetting, 1, LogCLI::INFO);

            $dialog = $this->getHelperSet()->get('dialog');
            if (!$dialog->askConfirmation($output, '<question>Replace "'.$currentSetting.'" with "'.end($values).'"? (type "y" to confirm)</question> ', false)) {
                return;
            }

            //TODO: format array to human readable (if not array, pass) - see if I already implemented this

            //TODO: messages like: "modifying XX: old value - AA, new value - BB"

            if($doNotReplace === true)
            { //adding without removing
                // 1. cut the last part and store it $lastbit
                $lastbit = StringTools::ReturnLastBit($path);
                $secondlast = StringTools::ReturnLastBit($path, 2);

                if($secondlast !== false && is_numeric($secondlast))
                {
                    $path = StringTools::DropLastBit($path, 2); //droping the fixed 0 and the last key

                    // 2. arraize $value
                    $setting = array($lastbit => $value);
                }
                else
                {
                    $setting = $value;
                }

                // 3. add value
                $settingsDB->mergeOneIterativeByPath($path, $setting);
            }
            else
            {
                // make the tree
                $setting = Tree::addToTreeAndSet(explode('/', $path), $value, 1);

                // add/replace the setting
                $settingsDB->mergeFromArray($setting, false, false);
            }

            // save the file with the new setting
            $settingsDB->returnYAML($file);
        }
    }
}
