[[user %(user)[[ %(group)]];]]
[[worker_processes %(processes);]]
events
{
    [[worker_connections %(connections);]]
    [[use %(use);]]
    [[multi_accept %(multi_accept);]]
}
http
{
    [[error_log %(errorlog)[[ %(errorlogstyle);]]]]
    [[include %(mimeinclude);]]
    [[sendfile %(sendfile);]]
    [[tcp_nopush %(nopush);]]
    [[tcp_nodelay %(nodelay);]]
    [[keepalive_timeout %(keepalivetimeout);]]
    [[ignore_invalid_headers %(ignore_invalid_headers);]]
    [[client_max_body_size %(max_body);]]
    [[server_tokens %(tokens);]]

    ## Log Format
    <<logformat>>

    ## Servers
    <!<server>!>
}
