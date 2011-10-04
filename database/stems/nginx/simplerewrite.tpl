location /
{
    [[try_files $uri $uri/ %(rewrite);]]
}