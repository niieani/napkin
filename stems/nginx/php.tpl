location ~ \.php$
{
    fastcgi_split_path_info ^(.+\.php)(.*)$;
    fastcgi_index [[%(index)s]];
    fastcgi_pass unix:/var/run/php-1.sock;
    include nginx-vzcommon/fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_param SERVER_NAME $http_host;
    fastcgi_ignore_client_abort on;
    fastcgi_param HTTPS $https;
}