<?php
/**
 * User: NIXin
 * Date: 24.09.2011
 * Time: 00:07
 */

namespace HypoConf\ConsoleCommands;

use HypoConf\Paths;
use Symfony\Component\Console as Console;
use Tools\FileOperation;
use Tools\LogCLI;
use Tools\StringTools;

class AddSite extends Console\Command\Command
{
    protected function configure()
    {
        $this
            ->setName('addsite')
            //->setAliases(array('siteadd'))
            ->setDescription('Adds a new site')
            ->setHelp('Adds a new site.')
            ->addArgument('name', Console\Input\InputArgument::REQUIRED, 'Name(s)')
            ->addArgument('user', Console\Input\InputArgument::OPTIONAL, 'User', Paths::$defaultUser)
            ->addArgument('group', Console\Input\InputArgument::OPTIONAL, 'Group', Paths::$defaultGroup)
            ->addOption('template', 't', Console\Input\InputOption::VALUE_OPTIONAL, 'Template');
    }

    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        $names = StringTools::Delimit($input->getArgument('name'), ',');
        $user = $input->getArgument('user');
        $group = $input->getArgument('group');
        $template = $input->getOption('template');

        // TODO
        if(!empty($template)) var_dump($template);

        foreach($names as $website)
        {
            // adding website
            //$website = $name['text'];
            LogCLI::Message('Adding website: '.$website, 0);

            LogCLI::MessageResult('Group and user: '.$group.'/'.$user, 2, LogCLI::INFO);
            $path = Paths::$db.Paths::$separator.$group.Paths::$separator.$user.Paths::$separator;
            if(file_exists($path))
            {
                if(!file_exists($path.$website.'.yml') && Paths::getFullPath($website) === false)
                {
                    FileOperation::CreateEmptyFile($path.$website.'.yml');
                    LogCLI::Result(LogCLI::OK);
                }
                else
                {
                    LogCLI::Fail('Website '.$website.', under '.$group.'/'.$user.' already exists!');
                    LogCLI::Result(LogCLI::FAIL);
                }
            }
            else
            {
                LogCLI::Fail('Group and/or user '.$group.'/'.$user.' does not exist!');
                LogCLI::Result(LogCLI::FAIL);
            }
        }
    }

}
