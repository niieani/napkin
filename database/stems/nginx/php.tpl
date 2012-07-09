## PHP support
location ~ \.php
{
#    fastcgi_param  QUERY_STRING       $query_string;
#    fastcgi_param  REQUEST_METHOD     $request_method;
#    fastcgi_param  CONTENT_TYPE       $content_type;
#    fastcgi_param  CONTENT_LENGTH     $content_length;

#    fastcgi_param  SCRIPT_NAME        $fastcgi_script_name;
#    fastcgi_param  SCRIPT_FILENAME    $document_root$fastcgi_script_name;
#    fastcgi_param  REQUEST_URI        $request_uri;
#    fastcgi_param  DOCUMENT_URI       $document_uri;
#    fastcgi_param  DOCUMENT_ROOT      $document_root;
#    fastcgi_param  SERVER_PROTOCOL    $server_protocol;

#    fastcgi_param  GATEWAY_INTERFACE  CGI/1.1;
    fastcgi_param  SERVER_SOFTWARE    nginx;

#    fastcgi_param  REMOTE_ADDR        $remote_addr;
#    fastcgi_param  REMOTE_PORT        $remote_port;
#    fastcgi_param  SERVER_ADDR        $server_addr;
#    fastcgi_param  SERVER_PORT        $server_port;
#    # fastcgi_param  SERVER_NAME        $server_name;
#    fastcgi_param SERVER_NAME        $http_host;

    # Required for pathinfo only
#    fastcgi_split_path_info ^(.+\.php)(/.+)$;
#    fastcgi_param  PATH_INFO          $fastcgi_path_info;
#    fastcgi_param  PATH_TRANSLATED    $document_root$fastcgi_path_info;

    # Just to be safe, disable PHP pathinfo fix
    fastcgi_param PHP_ADMIN_VALUE     "cgi.fix_pathinfo=0";

    include fastcgi_params;

    [[fastcgi_pass unix:%(socketprefix)[[%(filename)]].sock;]]
    [[fastcgi_pass unix:%(socket);]]

    [[fastcgi_connect_timeout %(conntimeout);]]
    [[fastcgi_send_timeout %(sendtimeout);]]
    [[fastcgi_read_timeout %(readtimeout);]]
    [[fastcgi_buffers %(buffer)s %(buffersize)k;]]
    [[fastcgi_busy_buffers_size %(busybuffersize)k;]]
    [[fastcgi_temp_file_write_size %(tempwritesize)k;]]

    [[fastcgi_intercept_errors %(intercepterrors);]]

    [[fastcgi_index %(index);]]

    [[fastcgi_ignore_client_abort %(ignoreabort);]]

    [[fastcgi_param HTTPS %(https);]]
}
