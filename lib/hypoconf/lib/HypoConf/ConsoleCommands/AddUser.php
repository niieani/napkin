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

class AddUser extends Console\Command\Command
{
    protected function configure()
    {
        $this
            ->setName('adduser')
            //->setAliases(array('useradd'))
            ->setDescription('Adds a new user')
            ->setHelp('Adds a new user.')
            ->addArgument('name', Console\Input\InputArgument::REQUIRED, 'Name(s)')
            ->addArgument('group', Console\Input\InputArgument::OPTIONAL, 'Group', Paths::$defaultGroup);
    }
    
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        //var_dump($input->getArgument('name'));
        $names = StringTools::Delimit($input->getArgument('name'), ',');
        $group = $input->getArgument('group');

        foreach($names as $username)
        {
            // adding user
            //LogCLI::MessageResult('Exclamation: '.$name['exclamation'], 2, LogCLI::INFO);

            //$username = $name['text'];
            LogCLI::Message('Adding user: '.$username, 0);
            $structure = Paths::$db.Paths::$separator.$group.Paths::$separator.$username;

            LogCLI::MessageResult('Creating directory: '.$structure, 2, LogCLI::INFO);

            if(@mkdir($structure, 0755, true))
            {
                LogCLI::Result(LogCLI::OK);
            }
            else
            {
                LogCLI::Fail('User '.$username.' already exists!');
                LogCLI::Result(LogCLI::FAIL);
            }
        }
    }

}
