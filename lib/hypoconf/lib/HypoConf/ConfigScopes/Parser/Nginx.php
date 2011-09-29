<?php
namespace HypoConf\ConfigScopes\Parser;

use HypoConf\ConfigScopes;
use HypoConf\ConfigParser;
use Tools\StringTools;

class Nginx extends ConfigScopes\Parser
{
    /*
    public function FixPath($path, $iterativeSetting = 0)
    {
        if(is_string($iterativeSetting))
        {
            if(($pos = strpos($path, 'nginx/server')) !== false && $pos === 0)
                $path = substr_replace($path, 'server/'.$iterativeSetting, 0, strlen('nginx/server'));
        }
        elseif(($pos = strpos($path, 'server/')) !== false && $pos === 0)
        {
            $path = StringTools::DropLastBit($path, -1);

            if(($pos = strpos($path, 'listen/')) !== false && $pos === 0)
            {
//                $last = StringTools::ReturnLastBit($path);
                $path = StringTools::DropLastBit($path);
//                $path .= '/'.$last;
//                $path .= '/'.$iterativeSetting.'/'.$last;
            }
        }
        //    $path = substr_replace($path, 'server/'.$iterativeSetting.'/', 0, strlen('nginx/server'));
            
        return $path;
    }
    */
    
    public function __construct(array &$templates)
    {
        /*
        
            wrzucić całość do jednej klasy a potem zrobić autoloader na zasadzie CommandLine
            i tak do każdej aplikacji
        
        */
        /*
         * NGINX ROOT SCOPE PARSER
         */
        $this->parsers['nginx'] = new ConfigParser(array(
            'name'        => 'nginx_root',
            'description' => 'nginx root',
            'version'     => '0.9',
            'template'    => &$templates['nginx']
        ));
        
        $this->parsers['nginx']->addSetting('user', array(
            'path'        => 'user',
            'action'      => 'StoreStringOrFalse',
            'default'     => 'www-data',
            'description' => 'user that runs nginx'
        ));
        
        $this->parsers['nginx']->addSetting('group', array(
            'path'        => 'group',
            'action'      => 'StoreStringOrFalse',
            'default'     => 'www-data',
            'description' => 'group that runs nginx'
        ));

        /*
         * NGINX EVENTS SCOPE PARSER
         */
        $this->parsers['nginx']->addSetting('connections', array(
            'path'        => 'connections',
            'action'      => 'StoreInt',
            'default'     => 4096
        ));
        $this->parsers['nginx']->addSetting('use', array(
            'path'        => 'use',
            'action'      => 'StoreStringOrFalse',
            'default'     => 'epoll'
        ));
        $this->parsers['nginx']->addSetting('multi_accept', array(
            'path'        => 'multi_accept',
            'action'      => 'StoreOnOff'
        ));


        $this->parsers['nginx']->addSetting('errorlog', array(
            'path'        => 'errorlog/file'
        ));
        $this->parsers['nginx']->addSetting('errorlogstyle', array(
            'path'        => 'errorlog/style'
        ));
        $this->parsers['nginx']->addSetting('mimepath', array(
            'path'        => 'mimepath'
        ));
        $this->parsers['nginx']->addSetting('sendfile', array(
            'path'        => 'sendfile'
        ));
        $this->parsers['nginx']->addSetting('nopush', array(
            'path'        => 'tcp/nopush',
            'action'      => 'StoreOnOff'
        ));
        $this->parsers['nginx']->addSetting('nodelay', array(
            'path'        => 'tcp/nodelay',
            'action'      => 'StoreOnOff'
        ));
        $this->parsers['nginx']->addSetting('keepalivetimeout', array(
            'path'        => 'keepalive/timeout',
            'action'      => 'StoreInt',
            'default'     => '60'
        ));
        $this->parsers['nginx']->addSetting('max_body', array(
            'path'        => 'max_body'
        ));
        $this->parsers['nginx']->addSetting('ignore_invalid_headers', array(
            'path'        => 'http/ignore_invalid_headers',
            'action'      => 'StoreOnOff'
        ));
        $this->parsers['nginx']->addSetting('tokens', array(
            'path'        => 'http/tokens',
            'action'      => 'StoreOnOff'
        ));


        $this->parsers['nginx']->addSetting('gzip', array(
            'path'        => 'gzip/gzip',
            'action'      => 'StoreOnOff'
        ));
        $this->parsers['nginx']->addSetting('gzip_disable', array(
            'path'        => 'gzip/disable'
        ));
        $this->parsers['nginx']->addSetting('gzip_min', array(
            'path'        => 'gzip/min'
        ));
        $this->parsers['nginx']->addSetting('gzip_comp_level', array(
            'path'        => 'gzip/level',
            'action'      => 'StoreInt',
            'default'     => '6'
        ));
        $this->parsers['nginx']->addSetting('gzip_proxied', array(
            'path'        => 'gzip/proxied'
        ));
        $this->parsers['nginx']->addSetting('gzip_buffers_num', array(
            'path'        => 'gzip/buffers',
            'action'      => 'StoreInt',
            'default'     => '16'
        ));
        $this->parsers['nginx']->addSetting('gzip_buffers_size', array(
            'path'        => 'gzip/buffer_size'
        ));
        $this->parsers['nginx']->addSetting('gzip_types', array(
            'path'        => 'gzip/types'
        ));

        /*
         *   NGINX SERVER SCOPE PARSER [ITERATIVE]
         * ITERATIVE SCOPES HAVE RELATIVE YML PATHS!
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
            'description' => 'listen options'
        ));
        
        $this->parsers['server']->addSetting('name_comment', array(
            'path'        => 'name_comment',
            'action'      => 'StoreStringOrFalse'
        ));

        $this->parsers['server']->addSetting('ssl', array(
            'path'        => 'support/ssl',
            'action'      => 'StoreStemOrFalse',
            'action_params' => array('template' => 'ssl')
        ));
        $this->parsers['server']->addSetting('faviconfix', array(
            'path'        => 'support/faviconfix',
            'action'      => 'StoreStemOrFalse',
            'action_params' => array('template' => 'faviconfix')
        ));
        $this->parsers['server']->addSetting('php', array(
            'path'        => 'support/php',
            'action'      => 'StoreStemOrFalse',
            'action_params' => array('template' => 'php')
        ));
        
        $this->parsers['php'] = new ConfigParser(array(
            'name'        => 'nginx_php',
            'description' => 'nginx php',
            'version'     => '0.9',
            'template'    => &$templates['php']
        ));
        $this->parsers['php']->addSetting('index', array(
            'path'        => 'index',
            'description' => 'php index file'
        ));

        $this->parsers['ssl'] = new ConfigParser(array(
            'name'        => 'nginx_ssl',
            'description' => 'nginx ssl',
            'version'     => '0.9',
            'template'    => &$templates['ssl']
        ));
        $this->parsers['ssl']->addSetting('timeout', array(
            'path'        => 'timeout'
        ));
        $this->parsers['ssl']->addSetting('cert', array(
            'path'        => 'cert'
        ));
        $this->parsers['ssl']->addSetting('key', array(
            'path'        => 'key'
        ));

        $this->parsers['faviconfix'] = new ConfigParser(array(
            'name'        => 'nginx_faviconfix',
            'description' => 'nginx no favicon fix',
            'version'     => '0.9',
            'template'    => &$templates['faviconfix']
        ));
        /*
         *          NGINX LOGFORMAT SCOPE PARSER
         * SCOPES WITH ITERATIVE PARENTS HAVE RELATIVE YML PATHS!
         */
        $this->parsers['logformat'] = new ConfigParser(array(
            'name'        => 'nginx_logformat',
            'description' => 'nginx log format',
            'version'     => '0.9',
            'template'    => &$templates['logformat']
        ));
        $this->parsers['logformat']->addSetting('name', array(
            'path'        => 'name'
        ));
        $this->parsers['logformat']->addSetting('format', array(
            'path'        => 'format'
        ));


        /*
         *          NGINX SERVER/LISTEN SCOPE PARSER
         * SCOPES WITH ITERATIVE PARENTS HAVE RELATIVE YML PATHS!
         */
        $this->parsers['listen'] = new ConfigParser(array(
            'name'        => 'nginx_listen',
            'description' => 'nginx listen',
            'version'     => '0.9',
            'template'    => &$templates['listen']
        ));
        /*
        $this->parsers['listen']->addSetting('listen', array(
            'path'        => array('ip'=>'ip','port'=>'port'),
            'default'     => array('ip'=>null,'port'=>'80'),
            'required_one'=> array('ip','port'),
            'action'      => 'IPPort',
            'description' => 'IP and port'
        ));
        */


        $this->parsers['listen']->addSetting('listen', array(
            'path'        => 'listen',
            'action'      => 'StoreStringOrFalse',
            'description' => 'listen (ip and/or port)'
        ));

        /*
        $this->parsers['listen']->addSetting('listen_options', array(
            'path'        => 'listen_options',
            'action'      => 'StoreStringOrFalse',
            'description' => 'listen options'
        ));
        */
        
        /*
         * COMMON 'CUSTOM' SETTING FOR INSERTING CUSTOM CODE
         * Available in each configuration segment
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