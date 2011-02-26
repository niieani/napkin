server[[ #%(name_comment)s]]
{
    <<listen>>
    [[server_name %(domain)s;]]
    [[root %(dir)s;]]
    [[index %(index)s;]]
    [[access_log %(logaccessfile)s %(logaccessstyle)s;]]
    [[error_log %(logerrorfile)s %(logerrorstyle)s;]]
    [[%(@@ssl@@)s]]
    [[%(@@faviconfix@@)s]]
    [[%(@@php@@)s]]
    [[%(custom)s]]
}
