server[[ #%(name_comment)s]]
{
    <<listen>>
    [[server_name %(domain)s;]]
    [[%(@@php@@)s]]
    [[%(custom)s]]
}
