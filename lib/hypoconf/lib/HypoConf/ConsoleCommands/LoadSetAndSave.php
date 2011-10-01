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
use HypoConf\Commands\Helpers;

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
            ->addArgument('chain', Console\Input\InputArgument::REQUIRED, 'Configuration chain (eg. nginx/php)')
            ->addArgument('values', Console\Input\InputArgument::REQUIRED + Console\Input\InputArgument::IS_ARRAY, 'Values to set (can be multiple)');
//        $this->addOption('more', 'm', Console\Input\InputOption::VALUE_NONE, 'Tell me more');

    }
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $chain = $input->getArgument('chain');
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
                $file = Paths::GetFullPath($argument['text']);

                if($file !== false)
                {
                    $application = 'nginx';
                    $basicScope = 'server';
//                    $basicScope = false;

                    $settings = ApplicationsDB::GetSettingsList($application, $basicScope);
//                    $iterativeSetting = 0;
                    var_dump($settings);

                    $value = ArrayTools::dearraizeIfNotRequired($values);

                    $chain = StringTools::Delimit($chain, '.');

                    $settingPath = implode('/', $chain);

                    LogCLI::MessageResult('Path of the setting: '.$settingPath, 4, LogCLI::INFO);

                    if ($path = Helpers::SearchConfigs(&$settings, $settingPath, $application, $basicScope))
                    {
                        $settingsDB = new ConfigScopes\SettingsDB();

                        // load the original file first
                        $settingsDB->MergeFromYAML($file, false, false, false); //true for compilation

                        $currentSetting = $settingsDB->ReturnOneByPath($settingPath);

                        /**
                         * if there is a difference
                         * TODO: probably dearraize not required here
                         */
                        if(ArrayTools::dearraizeIfNotRequired($currentSetting) != $value)
                        {
//                            $this->setHelperSet(new Console\Helper\HelperSet(
//                                                    array(
//                                                        new Console\Helper\FormatterHelper(),
//                                                        new Console\Helper\DialogHelper(),
//                                                        new XFormatterHelper()
//                                                        )));

                            $formatter = $this->getHelperSet()->get('xformatter');

                            $toFormat = array(
                                array('messages' => (array) $currentSetting, 'style' => 'error'),
                                array('messages' => array('> >'), 'style' => 'fg=yellow;bg=black;other=blink;other=bold', 'large' => false),
                                array('messages' => (array) $values, 'style' => 'fg=black;bg=yellow;other=bold')
                                );

                            //array_merge(array('With the following data:'), (array) $values)

                            $output->writeln($formatter->formatMultipleBlocks($toFormat, ' ', true));

                            $dialog = $this->getHelperSet()->get('dialog');
                            if (!$dialog->askConfirmation($output, 'Are you sure that you want to make this change? (type "y" to confirm) ', false)) {
                                return;
                            }

                            // make the tree
                            $setting = Tree::addToTreeSet(explode('/', $path), $value, 1);

                            // add/replace the setting
                            $settingsDB->MergeFromArray($setting, false, false);

                            // save the file with the new setting
                            $settingsDB->ReturnYAML($file);
                        }
                        /**
                         * nothing to do, it's all the same
                         */
                        else
                        {
                            $output->writeln('<fg=yellow;other=bold>No need to change, the values are already identical!</fg=yellow;other=bold>');
                        }
                    }
                    else
                    {

                    }
                }
                else
                {
                    LogCLI::Fail('Sorry, no site by name: '.$argument['text']);
                }
            }
        }
    }

}
