[[# generated from: %(filename) #]]
server[[ #%(comment)]]
{
    <!<listen>!>

    [[server_name %(domain);]]

    [[root %(root);]]

    [[access_log %(accesslog)[[ %(accesslogstyle)]];]]
    [[error_log %(errorlog)[[ %(errorlogstyle)]];]]

    [[rewrite ^ %(redirect)$uri permanent;]]

    [[index %(index);]]

    [[client_max_body_size %(maxbodysize);]]

    [[%(@@gzip@@)]]

    [[%(@@ssl@@)]]
    [[%(@@drop@@)]]
    [[%(@@staticexpire@@)]]
    [[%(@@deny@@)]]
    [[%(@@php@@)]]
    [[%(custom)]]
}
