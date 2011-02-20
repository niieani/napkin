<?php
namespace HypoConf\ConfigScopes\Parser;

use HypoConf\ConfigScopes;
use HypoConf\ConfigParser;

class Nginx extends ConfigScopes\Parser
{
    public function __construct(array &$templates)
    {
        /*
        
            wrzucić całość do jednej klasy a potem zrobić autoloader na zasadzie CommandLine
            i tak do każdej aplikacji
        
        */
        /*
         * NGINX ROOT SCOPE PARSER
         */
        $this->parsers['root'] = new ConfigParser(array(
            'name'        => 'nginx_root',
            'description' => 'nginx root',
            'version'     => '0.9',
            'template'    => &$templates['root']
        ));
        
        $this->parsers['root']->addSetting('user', array(
            'path'        => 'user',
            'action'      => 'StoreStringOrFalse',
            'default'     => 'www-data',
            'description' => 'user that runs nginx'
        ));
        
        $this->parsers['root']->addSetting('group', array(
            'path'        => 'group',
            'action'      => 'StoreStringOrFalse',
            'default'     => 'www-data',
            'description' => 'group that runs nginx'
        ));
        
        
        /*
         * NGINX EVENTS SCOPE PARSER
         */
        $this->parsers['events'] = new ConfigParser(array(
            'name'        => 'nginx_events',
            'description' => 'nginx events',
            'version'     => '0.9',
            'template'    => &$templates['events']
        ));
        $this->parsers['events']->addSetting('connections', array(
            'path'        => 'connections',
            'action'      => 'StoreInt',
            'default'     => 4096
        ));
        $this->parsers['events']->addSetting('use', array(
            'path'        => 'use',
            'action'      => 'StoreStringOrFalse',
            'default'     => 'epoll'
        ));
        $this->parsers['events']->addSetting('multi_accept', array(
            'path'        => 'multi_accept',
            'action'      => 'StoreOnOff'
        ));
        
        /*
         * NGINX HTTP SCOPE PARSER
         */
        $this->parsers['http'] = new ConfigParser(array(
            'name'        => 'nginx_http',
            'description' => 'nginx http',
            'version'     => '0.9',
            'template'    => &$templates['http']
        ));
        
        /*
         * NGINX SERVER SCOPE PARSER
         */
        $this->parsers['server'] = new ConfigParser(array(
            'name'        => 'nginx_server',
            'description' => 'nginx server',
            'version'     => '0.9',
            'template'    => &$templates['server']
        ));
        
        $this->parsers['server']->addSetting('domain', array(
            'path'        => 'domain',
            'action'      => 'StoreStringOrFalse',
            'default'     => null,
            'description' => 'listen options'
        ));
        
        
        /*
         * NGINX SERVER/LISTEN SCOPE PARSER
         */
        $this->parsers['listen'] = new ConfigParser(array(
            'name'        => 'nginx_listen',
            'description' => 'nginx listen',
            'version'     => '0.9',
            'template'    => &$templates['listen']
        ));
        
        $this->parsers['listen']->addSetting('listen', array(
            'path'        => array('ip'=>'ip','port'=>'port'),
            'default'     => array('ip'=>null,'port'=>'80'),
            'required_one'=> array('ip','port'),
            'action'      => 'IPPort',
            'description' => 'IP and port'
        ));
        
        $this->parsers['listen']->addSetting('listen_options', array(
            'path'        => 'listen_options',
            'action'      => 'StoreStringOrFalse',
            'default'     => '',
            'description' => 'listen options'
        ));
        
        
        /*
         * COMMON 'CUSTOM' SETTING FOR INSERTING CUSTOM CODE
         */
        foreach($this->parsers as &$parser)
        {
            $parser->addSetting('custom', array(
                'path'        => 'custom',
                'action'      => 'StoreStringOrFalse',
                'description' => 'custom config'
            ));
        }
    }
    //public function GetParser();
}