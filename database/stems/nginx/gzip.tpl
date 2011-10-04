## Gzip Compression
gzip on;
[[gzip_disable %(gzip_disable);]]
[[gzip_min_length %(gzip_min);]]
[[gzip_comp_level %(gzip_comp_level);]]
[[gzip_proxied %(gzip_proxied);]]
[[gzip_buffers %(gzip_buffers_num)s %(gzip_buffers_size)k;]]
[[gzip_types %(gzip_types);]]
# Set a vary header so downstream proxies don't send cached gzipped content to IE6
[[gzip_vary %(gzip_vary);]]