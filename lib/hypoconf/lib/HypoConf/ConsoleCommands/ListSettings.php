<?php
/**
 * User: NIXin
 * Date: 24.09.2011
 * Time: 00:07
 */

namespace HypoConf\ConsoleCommands;

use Symfony\Component\Console as Console;
use Tools\LogCLI;
use Tools\ArrayTools;
use HypoConf\ConfigScopes\ApplicationsDB;

class ListSettings extends Console\Command\Command
{
    protected function configure()
    {
        $this
            ->setName('setlist')
            //->setAliases(array('l'))
            ->setDescription('Lists all available settings')
            ->setHelp('Lists all available settings.')
            ->addArgument('application', Console\Input\InputArgument::OPTIONAL, 'List settings used by a specific application', 'nginx');
//        $this->addOption('more', 'm', Console\Input\InputOption::VALUE_NONE, 'Tell me more');

    }
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        $application = $input->getArgument('application');

//        $output->writeln('Hello World!');
        LogCLI::Message('Listing available settings: ', 0);
        $configScopesNginx = ApplicationsDB::LoadApplication($application);
//        $settingsNginx = ApplicationsDB::GetSettingsList('nginx', 'server');
        $settingsNginx = ApplicationsDB::GetSettingsList($application);
        $settings = ArrayTools::GetMultiDimentionalElementsWithChildren(&$settingsNginx);
        foreach($settings as $setting)
        {
            LogCLI::MessageResult(LogCLI::BLUE.$setting, 0, LogCLI::INFO);
        }
        LogCLI::Result(LogCLI::OK);
    }
}
