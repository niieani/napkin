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
use Tools\ArrayTools;
use Tools\Tree;
use HypoConf\ConfigScopes;
use HypoConf\ConfigScopes\ApplicationsDB;
use HypoConf\Paths;
use Tools\XFormatterHelper;
//use HypoConf\Commands;
use HypoConf\ConfigScopes\SettingsDB;

class LoadSetAndSave extends Console\Command\Command
{
    protected function configure()
    {
        $this
            ->setName('set')
            //->setAliases(array('s'))
            ->setDescription('Changes the value and updates the config file')
            ->setHelp('Changes the value and updates the config file.')
            ->addArgument('name', Console\Input\InputArgument::REQUIRED, 'Site, user (when prefixed with @) or template (when prefixed with +)')
            ->addArgument('path', Console\Input\InputArgument::REQUIRED, 'Configuration chain (eg. nginx/php)')
            ->addArgument('values', Console\Input\InputArgument::REQUIRED + Console\Input\InputArgument::IS_ARRAY, 'Values to set (can be multiple)');
//        $this->addOption('more', 'm', Console\Input\InputOption::VALUE_NONE, 'Tell me more');

    }
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $settingPath = $input->getArgument('path');
        $values = $input->getArgument('values');

        ApplicationsDB::LoadAll();

        foreach(StringTools::TypeList($name) as $argument)
        {
            if ($argument['exclamation'] !== false)
            {
                // TODO: actually handle the exclamators
                LogCLI::MessageResult('Exclamation: '.$argument['exclamation'], 4, LogCLI::INFO);
            }
            else
            {
                //siteYML
                $file = Paths::getFullPath($argument['text']);

                if($file !== false)
                {
                    $application = 'nginx';
                    $basicScope = 'server';

                    $settings = ApplicationsDB::GetSettingsList($application, $basicScope);

                    //var_dump($settings);

                    $value = ArrayTools::dearraizeIfNotRequired($values);

                    //$chain = StringTools::Delimit($chain, '.');
                    //$settingPath = implode('/', $chain);

                    LogCLI::MessageResult('Path of the setting: '.$settingPath, 4, LogCLI::INFO);

                    if ($path = SettingsDB::findPathForSetting(&$settings, $settingPath, $basicScope))
                    {
                        $settingsDB = new ConfigScopes\SettingsDB();

                        // load the original file first
                        $settingsDB->mergeFromYAML($file);

                        $currentSetting = $settingsDB->returnOneByPath($settingPath);

                        /**
                         * if there is a difference
                         * TODO: probably dearraize not required here
                         */
                        if(ArrayTools::dearraizeIfNotRequired($currentSetting) != $value)
                        {
                            $formatter = $this->getHelperSet()->get('xformatter');

                            $toFormat = array(
                                array('messages' => (array) $currentSetting, 'style' => 'error'),
                                array('messages' => array('> >'), 'style' => 'fg=yellow;bg=black;other=blink;other=bold', 'large' => false),
                                array('messages' => (array) $values, 'style' => 'fg=black;bg=yellow;other=bold')
                                );

                            //array_merge(array('With the following data:'), (array) $values)

                            $output->writeln($formatter->formatMultipleBlocks($toFormat, ' ', true));

                            $dialog = $this->getHelperSet()->get('dialog');
                            if (!$dialog->askConfirmation($output, 'Are you sure that you want to make this change? (type "y" to confirm) ', false))
                            {
                                return;
                            }

                            // make the tree
                            $setting = Tree::addToTreeAndSet(explode('/', $path), $value);

                            // add/replace the setting
                            $settingsDB->mergeFromArray($setting, false, false);

                            // save the file with the new setting
                            $settingsDB->returnYAML($file);
                        }
                        else
                        {
                            /**
                             * nothing to do, it's all the same
                             */
                            $output->writeln('<fg=yellow;other=bold>No need to change, the values are already identical!</fg=yellow;other=bold>');
                        }
                    }
                    else
                    {
                        // TODO
                    }
                }
                else
                {
                    LogCLI::Fail('Sorry, no site by name: '.$argument['text']);
                }
            }
        }
    }

    public static function doWeReplaceHelper(array $chain)
    {
        $testType = end(StringTools::TypeList(reset($chain), '+', false));

        if($testType['exclamation'] !== false)
        {
            return true;
        }
        else return false;
    }

}
