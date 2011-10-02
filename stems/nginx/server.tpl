[[# generated from: %(filename) #]]
server[[ #%(comment)]]
{
    [[server_name %(domain);]]
    <!<listen>!>
    [[root %(dir);]]
    [[index %(index);]]
    [[access_log %(logaccessfile) %(logaccessstyle);]]
    [[error_log %(logerrorfile) %(logerrorstyle);]]
    [[%(@@ssl@@)]]
    [[%(@@faviconfix@@)]]
    [[%(@@php@@)]]
    [[%(@@deny@@)]]
    [[%(custom)]]
}
