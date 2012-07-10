<?php
/**
 * User: NIXin
 * Date: 09.07.2012
 * Time: 00:07
 */

namespace NginxParser\ConsoleCommands;

use Symfony\Component\Console as Console;
use Tools\LogCLI;
use Tools\ParseNginxConfig;

class ParseNginx extends Console\Command\Command
{
    protected function configure()
    {
        $this
            ->setName('parse')
            //->setAliases(array('l'))
            ->setDescription('Parses a nginx configuration file')
            ->setHelp('Parses a nginx configuration file.')
            ->addArgument('filename', Console\Input\InputArgument::REQUIRED, 'File to parse');

    }
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        $filename = $input->getArgument('filename');
        $contents =
'location / {
   if (GET) {
      echo_exec @memcache;
   }
   if (POST) {
      echo_exec @application;
   }
}

location @memcache {
   if (notfound) {
       echo_exec @application;
   }
}
location @application {
   # forwarding request to application
}
';
//        $output->writeln('Hello World!');
        LogCLI::Message('Listing available settings: ', 0);
        var_dump(ParseNginxConfig::doParse($contents));
    }
}

// it is about unveiling of the secrets of new generation of human beings, which is conceiled in the second part of the dresden codex.
// The mystery, which was left by the Mayans and for which the humanity is not ready yet. It's fight between the good and evil.