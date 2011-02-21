[[user %(user)s[[ %(group)s]];]]
events
{
    [[worker_connections %(connections)s;]]
    [[use %(use)s;]]
    [[multi_accept %(multi_accept)s;]]
}
http
{
    [[error_log %(errorlog)s]][[ %(errorlogstyle)s;]]
    <!<server>!>
}
