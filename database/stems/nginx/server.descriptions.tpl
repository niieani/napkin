[[# generated from: %(filename) #]]
server[[ #%(comment)]]
{
    # This will listen on all interfaces, you can instead choose a specific IP
	# such as listen x.x.x.x:80;  Setting listen 80 default_server; will make
	# this server block the default one if no other blocks match the request
    <!<listen>!>

	# Here you can set a server name, you can use wildcards such as *.example.com
	# however remember if you use server_name *.example.com; You'll only match subdomains
	# to match both subdomains and the main domain use both example.com and *.example.com
    [[server_name %(domain);]]

    [[root %(root);]]

    [[access_log %(accesslog)[[ %(accesslogstyle)]];]]
    [[error_log %(errorlog)[[ %(errorlogstyle)]];]]

    [[rewrite ^ %(redirect)$uri permanent;]]

    [[index %(index);]]

    # Nginx default value was 1 MB and therefore all uploads exceeding 1 MB was
    # getting "413 Request Entity Too Large" error. Script default is 64 MB.
    # Remember to change the settings for upload size in php.ini as well.
    [[client_max_body_size %(maxbodysize);]]

    [[%(@@gzip@@)]]

    [[%(@@ssl@@)]]
    [[%(@@drop@@)]]
    [[%(@@staticexpire@@)]]
    [[%(@@deny@@)]]
    [[%(@@php@@)]]
    [[%(custom)]]
}
