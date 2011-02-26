[[user %(user)s[[ %(group)s]];]]
[[worker_processes %(processes)s;]]
events
{
    [[worker_connections %(connections)s;]]
    [[use %(use)s;]]
    [[multi_accept %(multi_accept)s;]]
}
http
{
    [[error_log %(errorlog)s]][[ %(errorlogstyle)s;]]
    [[include %(mimepath)s;]]
    [[sendfile %(sendfile)s;]]
    [[tcp_nopush %(nopush)s;]]
    [[tcp_nodelay %(nodelay)s;]]
    [[keepalive_timeout %(keepalive)s;]]
    [[client_max_body_size %(max_body)s;]]

    ## Gzip Compression
    [[gzip %(gzip)s;]]
    [[gzip_disable %(gzip_disable)s;]]
    [[gzip_min_length %(gzip_min)s;]]
    [[gzip_proxied %(gzip_proxied)s;]]
    [[gzip_types %(gzip_types)s;]]

    ## Log Format
    <<logformat>>

    ## Servers
    <!<server>!>
}
