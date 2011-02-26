location ~ \.php$
{
    fastcgi_split_path_info ^(.+\.php)(.*)$;
    [[fastcgi_pass unix:%(socket)s;]]
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_param SERVER_NAME $http_host;

    [[fastcgi_connect_timeout %(conntimeout)s;]]
    [[fastcgi_send_timeout %(sendtimeout)s;]]
    [[fastcgi_read_timeout %(readtimeout)s;]]
    [[fastcgi_buffers %(buffer)s %(buffersize)sk;]]
    [[fastcgi_busy_buffers_size %(busybuffersize)sk;]]
    [[fastcgi_temp_file_write_size %(tempwritesize)sk;]]
    [[fastcgi_intercept_errors %(intercepterrors)s;]]

    [[include %(include_params)s;]]
    [[fastcgi_ignore_client_abort %(ignoreabort)s;]]
    [[fastcgi_param HTTPS %(https)s;]]
    [[fastcgi_index %(index)s]];
}