<?php
/**
 * User: NIXin
 * Date: 24.09.2011
 * Time: 00:07
 */

namespace HypoConf\ConsoleCommands;

use Symfony\Component\Console as Console;
use Tools\FileOperation;
//use HypoConf\ConfigScopes;
//use HypoConf\ConfigScopes\ApplicationsDB;
use Tools\LogCLI;
use Tools\StringTools;
//use Tools\ArrayTools;
//use Tools\Tree;
//use HypoConf\ConfigScopes;
//use HypoConf\ConfigScopes\ApplicationsDB;
use HypoConf\Paths;
//use Tools\XFormatterHelper;
//use HypoConf\Commands\Helpers;

class Add extends Console\Command\Command
{
    protected function configure()
    {
        $this
            ->setName('add')
            //->setAliases(array('add'))
            ->setDescription('Adds a new site or user')
            ->setHelp('Adds a new site or user.')
            ->addArgument('name', Console\Input\InputArgument::REQUIRED, 'Name(s)')
            ->addArgument('userorgroup', Console\Input\InputArgument::OPTIONAL, 'User or Group', null);
//            ->addArgument('group', Console\Input\InputArgument::REQUIRED, 'Group');
//            ->addArgument('chain', Console\Input\InputArgument::REQUIRED, 'Configuration chain (eg. nginx/php)');
//            ->addArgument('names', Console\Input\InputArgument::REQUIRED + Console\Input\InputArgument::IS_ARRAY, 'Directories (can be multiple)');
//        $this->addOption('more', 'm', Console\Input\InputOption::VALUE_NONE, 'Tell me more');

        /**
         * TODO: final syntax of this should be:
         * addsite Domain.com,Other.net [user] [group]
         * adduser SomeUser,SomeOther [Group]
         * addgroup SomeGroup,Another
         * addtemplate developement 
         *
         * shorthand:
         * add domain.com@user
         *
         * parsing order:
         * 1. load which templates do we need from group/user/site.yml
         * 2. load to defaultsDB: _hypoconf/default/config.yml
         * 3. load the template(s) to defaultsDB from: templates/{template}.yml
         * 4. finally load the group/user/site.yml again, but this time apply it to the DB
         *
         *
         * templates defined in site like this (in order of overrides):
         * template:
         *   - someTemplate.yml
         *   - another.yml
         *
         * instead of templates, we can do parenting
         * and just have generate: false for that site
         *
         * perhaps config.yml should be renamed to defaults.yml (more intuitive)
         * and maybe we can have this defaults.yml in root directory of every group/user
         *
         * [idea: add console animations for visual merging of some configs, etc]
         * [eating, or destroying the blocks]
         */

    }
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        $names = StringTools::TypeList($input->getArgument('name'), '@', ',');
        $userorgroup = $input->getArgument('userorgroup');
//        $group = $input->getArgument('group');
//        $group =
//        $directories = $input->getArgument('directories');
//        $files = array();
        
        //ApplicationsDB::LoadAll();

        foreach($names as $name)
        {
            if ($name['exclamation'] !== false)
            {
                // adding user
                LogCLI::MessageResult('Exclamation: '.$name['exclamation'], 2, LogCLI::INFO);

                $username = $name['text'];
                LogCLI::Message('Adding user: '.$username, 0);
                $group = (!empty($userorgroup)) ? $userorgroup : Paths::$defaultGroup;
                $structure = Paths::$db.Paths::$separator.$group.Paths::$separator.$username;

                LogCLI::MessageResult('Creating directory: '.$structure, 2, LogCLI::INFO);

                if(@mkdir($structure, 0755, true))
                {
                    LogCLI::Result(LogCLI::OK);
                }
                else
                {
                    LogCLI::Result(LogCLI::FAIL);
                    LogCLI::Fail('User '.$username.' already exists!');
                    //LogCLI::Fail($e->getMessage());
                }
            }
            else
            {
                // adding website
                $website = $name['text'];
                LogCLI::Message('Adding website: '.$website, 0);

                $username = (!empty($userorgroup)) ? $userorgroup : Paths::$defaultUser;
                $group = Paths::$defaultGroup;

                LogCLI::MessageResult('User and group: '.$username.'/'.$group, 2, LogCLI::INFO);
                $path = Paths::$db.Paths::$separator.$group.Paths::$separator.$username.Paths::$separator;
                if(file_exists($path))
                {
                    if(!file_exists($path.$website.'.yml') && Paths::GetFullPath($website) === false)
                    {
                        FileOperation::CreateEmptyFile($path.$website.'.yml');
                        LogCLI::Result(LogCLI::OK);
                    }
                    else
                    {
                        LogCLI::Result(LogCLI::FAIL);
                        LogCLI::Fail('Website '.$website.', under '.$group.'/'.$username.' already exists!');
                    }
                }
                else
                {
                    LogCLI::Result(LogCLI::FAIL);
                    LogCLI::Fail('Group and/or user '.$group.'/'.$username.' does not exist!');
                }
            }
        }
    }

}
