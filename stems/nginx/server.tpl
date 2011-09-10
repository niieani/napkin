server[[ #%(name_comment)s]]
{
    <<listen>>
    [[server_name %(domain);]]
    [[root %(dir);]]
    [[index %(index);]]
    [[access_log %(logaccessfile) %(logaccessstyle);]]
    [[error_log %(logerrorfile) %(logerrorstyle);]]
    [[%(@@ssl@@)]]
    [[%(@@faviconfix@@)]]
    [[%(@@php@@)]]
    [[%(custom)]]
}
