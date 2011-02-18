<?php
namespace Nginx;

use \ConfigStyles\BracketConfig\NginxConfig;
use \Applications\Nginx;
    
$nginx['user'] = 
        new NginxConfig('user %(user)s %(group)s');
        
$nginx['events']['connections'] = 
        new NginxConfig('worker_connections %(connections)s');
        
$nginx['events']['multi_accept'] = 
        new NginxConfig('multi_accept %(multi_accept)s');
        
$nginx['pid'] = 
        new NginxConfig('pid %(pid)s');
        
$nginx['sites']['listen'] = 
        new NginxConfig('listen %(port)s %(options)s', 'server', 1);
        
$nginx['sites']['domain'] = 
        new NginxConfig('server_name %(domain)s', 'server', 1);
