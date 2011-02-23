server
{
    <<listen>>
    [[server_name %(domain)s;]]
    [[%(@@php@@)s]]
    [[%(custom)s]]
}
