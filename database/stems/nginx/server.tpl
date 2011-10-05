[[# generated from: %(filename) #]]
server[[ #%(comment)]]
{
    <!<listen>!>
    [[server_name %(domain);]]

    [[root %(root);]]

    [[access_log %(accesslog)[[ %(accesslogstyle)]];]]
    [[error_log %(errorlog)[[ %(errorlogstyle)]];]]
    [[access_log %(accesslogprefix)%(filename).access.log[[ %(accesslogstyle)]];]]
    [[error_log %(errorlogprefix)%(filename).error.log[[ %(accesslogstyle)]];]]

    [[rewrite ^ %(redirect)$uri permanent;]]

    [[index %(index);]]

    [[client_max_body_size %(maxbodysize);]]

    [[%(@@simplerewrite@@)]]

    [[%(@@gzip@@)]]

    [[%(@@ssl@@)]]
    [[%(@@drop@@)]]
    [[%(@@staticexpire@@)]]
    [[%(@@deny@@)]]
    [[%(@@php@@)]]
    [[%(custom)]]
}
