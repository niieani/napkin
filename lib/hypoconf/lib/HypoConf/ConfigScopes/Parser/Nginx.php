<?php
namespace HypoConf\ConfigScopes\Parser;

use HypoConf\ConfigScopes;
use HypoConf\ConfigParser;
use Tools\StringTools;

class Nginx extends ConfigScopes\Parser
{
    public function __construct(array &$templates)
    {
        /*
         * wrzucić całość do jednej klasy a potem zrobić autoloader na zasadzie CommandLine
         * i tak do każdej aplikacji
         */
        // http://kbeezie.com/view/nginx-configuration-examples/
        
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
        $this->parsers['nginx']->addSetting('processes', array(
            'path'        => 'processes',
            'action'      => 'StoreInt',
            'default'     => 4
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

        /*
         * NGINX HTTP SCOPE PARSER
         */
        $this->parsers['nginx']->addSetting('errorlog', array(
            'path'        => 'errorlog/file',
            'action'      => 'StoreStringOrFalse'
        ));
        $this->parsers['nginx']->addSetting('errorlogstyle', array(
            'path'        => 'errorlog/style',
            'action'      => 'StoreStringOrFalse'
        ));
        $this->parsers['nginx']->addSetting('mimeinclude', array(
            'path'        => 'mimeinclude',
            'action'      => 'StoreStringOrFalse'
        ));
        $this->parsers['nginx']->addSetting('sendfile', array(
            'path'        => 'sendfile',
            'action'      => 'StoreOnOff'
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
            'path'        => 'katimeout',
            'action'      => 'StoreInt',
            'default'     => '60'
        ));
        $this->parsers['nginx']->addSetting('ignore_invalid_headers', array(
            'path'        => 'http/ignore_invalid_headers',
            'action'      => 'StoreOnOff'
        ));
        $this->parsers['nginx']->addSetting('max_body', array(
            'path'        => 'http/max_body',
            'action'      => 'StoreStringOrFalse'
        ));
        $this->parsers['nginx']->addSetting('tokens', array(
            'path'        => 'http/tokens',
            'action'      => 'StoreStringOrFalse'
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
         *   NGINX SERVER SCOPE PARSER [ITERATIVE]
         * ITERATIVE SCOPES HAVE RELATIVE YML PATHS!
         */
        $this->parsers['server'] = new ConfigParser(array(
            'name'        => 'nginx_server',
            'description' => 'nginx server',
            'version'     => '0.9',
            'template'    => &$templates['server']
        ));

        $this->parsers['server']->addSetting('comment', array(
            'path'        => 'comment',
            'action'      => 'StoreStringOrFalse'
        ));


        /*
         *          NGINX SERVER/LISTEN SCOPE PARSER
         * SCOPES WITH ITERATIVE PARENTS HAVE RELATIVE YML PATHS!
         */
        $this->parsers['listen'] = new ConfigParser(array(
            'name'        => 'nginx_listen',
            'description' => 'nginx listen',
            'version'     => '0.9',
            'template'    => &$templates['listen'],
            'foreignSettings' => array(array(2, 'support/ssl', 'ssl'))
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
         * this is intelligent
         */
        $this->parsers['listen']->addSetting('ssl', array(
            'path'        => 'ssl',
            'action'      => 'StoreOnOff',
            'action_params' => array('onValue' => 'ssl', 'offValue' => ''),
            'settable'    => false //TODO
        ));

        
        $this->parsers['server']->addSetting('domain', array(
            'path'        => 'domain',
            'action'      => 'StoreStringOrFalse'
        ));
        $this->parsers['server']->addSetting('root', array(
            'path'        => 'root',
            'action'      => 'StoreStringOrFalse'
        ));
        $this->parsers['server']->addSetting('accesslog', array(
            'path'        => 'logs/accesslog',
            'action'      => 'StoreStringOrFalse'
        ));
        $this->parsers['server']->addSetting('accesslogprefix', array(
            'path'        => 'logs/accesslogprefix',
            'action'      => 'StoreStringOrFalse'
        ));
        $this->parsers['server']->addSetting('accessstyle', array(
            'path'        => 'logs/accessstyle',
            'action'      => 'StoreStringOrFalse'
        ));
        $this->parsers['server']->addSetting('errorlog', array(
            'path'        => 'logs/errorlog',
            'action'      => 'StoreStringOrFalse'
        ));
        $this->parsers['server']->addSetting('errorlogprefix', array(
            'path'        => 'logs/errorlogprefix',
            'action'      => 'StoreStringOrFalse'
        ));
        $this->parsers['server']->addSetting('errorstyle', array(
            'path'        => 'logs/errorstyle',
            'action'      => 'StoreStringOrFalse'
        ));
        $this->parsers['server']->addSetting('redirect', array(
            'path'        => 'redirect',
            'action'      => 'StoreStringOrFalse'
        ));
        $this->parsers['server']->addSetting('index', array(
            'path'        => 'index',
            'action'      => 'StoreStringOrFalse'
        ));
        $this->parsers['server']->addSetting('maxbodysize', array(
            'path'        => 'maxbodysize',
            'action'      => 'StoreStringOrFalse'
            // eg. 1248m
        ));

        $this->parsers['simplerewrite'] = new ConfigParser(array(
            'name'        => 'nginx_simplerewrite',
            'description' => 'nginx simplerewrite',
            'version'     => '0.9',
            'template'    => &$templates['simplerewrite']
        ));
        $this->parsers['simplerewrite']->addSetting('rewrite', array(
            'path'        => 'rewrite'
        ));

        /*
         *          NGINX SERVER/GZIP SCOPE PARSER
         * SCOPES WITH ITERATIVE PARENTS HAVE RELATIVE YML PATHS!
         */
        $this->parsers['gzip'] = new ConfigParser(array(
            'name'        => 'nginx_gzip',
            'description' => 'nginx gzip',
            'version'     => '0.9',
            'template'    => &$templates['gzip']
        ));
        $this->parsers['gzip']->addSetting('gzip_disable', array(
            'path'        => 'disable'
        ));
        $this->parsers['gzip']->addSetting('gzip_min', array(
            'path'        => 'min'
        ));
        $this->parsers['gzip']->addSetting('gzip_comp_level', array(
            'path'        => 'level',
            'action'      => 'StoreInt',
            'default'     => '6'
        ));
        $this->parsers['gzip']->addSetting('gzip_proxied', array(
            'path'        => 'proxied'
        ));
        $this->parsers['gzip']->addSetting('gzip_buffers_num', array(
            'path'        => 'buffers',
            'action'      => 'StoreInt',
            'default'     => '16'
        ));
        $this->parsers['gzip']->addSetting('gzip_buffers_size', array(
            'path'        => 'buffer_size'
        ));
        $this->parsers['gzip']->addSetting('gzip_types', array(
            'path'        => 'types'
        ));
        $this->parsers['gzip']->addSetting('gzip_vary', array(
            'path'        => 'vary',
            'action'      => 'StoreOnOff'
        ));


        $this->parsers['ssl'] = new ConfigParser(array(
            'name'        => 'nginx_ssl',
            'description' => 'nginx ssl',
            'version'     => '0.9',
            'template'    => &$templates['ssl']
        ));
        $this->parsers['ssl']->addSetting('cert', array(
            'path'        => 'cert'
        ));
        $this->parsers['ssl']->addSetting('key', array(
            'path'        => 'key'
        ));
        $this->parsers['ssl']->addSetting('timeout', array(
            'path'        => 'timeout'
        ));
        $this->parsers['ssl']->addSetting('openssl_cipherlist_spec', array(
            'path'        => 'cyphers'
        ));
        $this->parsers['ssl']->addSetting('protocols', array(
            'path'        => 'protocols'
        ));

        $this->parsers['drop'] = new ConfigParser(array(
            'name'        => 'nginx_drop',
            'description' => 'nginx drop',
            'version'     => '0.9',
            'template'    => &$templates['drop']
        ));
        $this->parsers['staticexpire'] = new ConfigParser(array(
            'name'        => 'nginx_staticexpire',
            'description' => 'nginx staticexpire',
            'version'     => '0.9',
            'template'    => &$templates['staticexpire']
        ));

        $this->parsers['deny'] = new ConfigParser(array(
            'name'        => 'nginx_deny',
            'description' => 'nginx deny',
            'template'    => &$templates['deny']
        ));
        $this->parsers['deny']->addSetting('deny', array(
            'path'        => 'deny'
            /**
             *  location [[content]]
             *  {
             *      deny all;
             *  }
             */
        ));


        $this->parsers['server']->addSetting('parent', array(
            'path'        => 'parent',
            'action'      => 'StoreStringOrFalse'
        ));

        $this->parsers['server']->addSetting('filename', array(
            'path'        => 'filename',
            'action'      => 'StoreStringOrFalse'
        ));



        $this->parsers['server']->addSetting('ssl', array(
            'path'        => 'support/ssl',
            'action'      => 'StoreStemOrFalse',
            'action_params' => array('template' => 'ssl')
        ));
        $this->parsers['server']->addSetting('php', array(
            'path'        => 'support/php',
            'action'      => 'StoreStemOrFalse',
            'action_params' => array('template' => 'php')
        ));
        $this->parsers['server']->addSetting('deny', array(
            'path'        => 'support/deny',
            'action'      => 'StoreStemOrFalse',
            'action_params' => array('template' => 'deny', 'iterative' => true)
        ));
        $this->parsers['server']->addSetting('gzip', array(
            'path'        => 'support/gzip',
            'action'      => 'StoreStemOrFalse',
            'action_params' => array('template' => 'gzip')
        ));
        $this->parsers['server']->addSetting('drop', array(
            'path'        => 'support/drop',
            'action'      => 'StoreStemOrFalse',
            'action_params' => array('template' => 'drop')
        ));
        $this->parsers['server']->addSetting('staticexpire', array(
            'path'        => 'support/staticexpire',
            'action'      => 'StoreStemOrFalse',
            'action_params' => array('template' => 'staticexpire')
        ));
        $this->parsers['server']->addSetting('simplerewrite', array(
            'path'        => 'support/simplerewrite',
            'action'      => 'StoreStemOrFalse',
            'action_params' => array('template' => 'simplerewrite')
        ));



        $this->parsers['php'] = new ConfigParser(array(
            'name'        => 'nginx_php',
            'description' => 'nginx php',
            'version'     => '0.9',
            'template'    => &$templates['php'],
            'foreignSettings' => array(array(1, 'filename', 'filename'), array(1, 'support/ssl', 'ssl'))
            //one level higher, filename // TODO: reimplement with ../../ paths
            /* array content: how many level higher, name, localy available name */
        ));
        $this->parsers['php']->addSetting('socketprefix', array(
            'path'        => 'socketprefix'
        ));
        $this->parsers['php']->addSetting('socket', array(
            'path'        => 'socket'
        ));
        $this->parsers['php']->addSetting('index', array(
            'path'        => 'index',
            'description' => 'php index file'
        ));
        $this->parsers['php']->addSetting('https', array(
            'path'        => 'ssl',
            'action'      => 'StoreOnOff',
            'settable'    => false //TODO
        ));
        $this->parsers['php']->addSetting('filename', array(
            'path'        => 'filename',
            'settable'    => false //TODO
        ));
        
        /*
         * COMMON 'CUSTOM' SETTING FOR INSERTING CUSTOM CODE
         * Available in each configuration segment
         */
        foreach($this->parsers as &$parser)
        {
            $parser->addSetting('custom', array(
                'path'        => 'custom',
                'action'      => 'StoreStringOrFalse',
                'description' => 'custom config',
                'divideBy'    => PHP_EOL
            ));
        }
    }
    //public function GetParser();
}