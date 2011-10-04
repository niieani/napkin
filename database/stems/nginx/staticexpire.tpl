## Static content expire to max
location ~* \.(?:ico|css|js|gif|jpe?g|png)$
{
    expires max;
    add_header Pragma public;
    add_header Cache-Control "public, must-revalidate, proxy-revalidate";
}