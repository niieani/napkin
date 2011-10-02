location ~ \.php$
{
    fastcgi_split_path_info ^(.+\.php)(.*)$;
    [[fastcgi_pass unix:%(socket)s;]]
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_param SERVER_NAME $http_host;

    [[fastcgi_connect_timeout %(conntimeout);]]
    [[fastcgi_send_timeout %(sendtimeout);]]
    [[fastcgi_read_timeout %(readtimeout);]]
    [[fastcgi_buffers %(buffer)s %(buffersize)k;]]
    [[fastcgi_busy_buffers_size %(busybuffersize)k;]]
    [[fastcgi_temp_file_write_size %(tempwritesize)k;]]
    [[fastcgi_intercept_errors %(intercepterrors);]]

    [[include %(include_params);]]
    [[fastcgi_ignore_client_abort %(ignoreabort);]]
    [[fastcgi_param HTTPS %(https);]]
    [[fastcgi_index %(index);]]
}