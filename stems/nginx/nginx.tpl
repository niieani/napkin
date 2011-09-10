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
    [[error_log %(errorlog)]][[ %(errorlogstyle);]]
    [[include %(mimepath);]]
    [[sendfile %(sendfile);]]
    [[tcp_nopush %(nopush);]]
    [[tcp_nodelay %(nodelay);]]
    [[keepalive_timeout %(keepalivetimeout);]]
    [[client_max_body_size %(max_body);]]

    ## Gzip Compression
    [[gzip %(gzip);]]
    [[gzip_disable %(gzip_disable);]]
    [[gzip_min_length %(gzip_min);]]
    [[gzip_comp_level %(gzip_comp_level);]]
    [[gzip_proxied %(gzip_proxied);]]
    [[gzip_buffers %(gzip_buffers_num)s %(gzip_buffers_size)k;]]
    [[gzip_types %(gzip_types);]]

    ## Log Format
    <<logformat>>

    ## Servers
    <!<server>!>
}
