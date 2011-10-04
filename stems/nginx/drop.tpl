## This prevents hidden files (beginning with a period) from being served
location ~ /\.          { access_log off; log_not_found off; deny all; }

location ~ ~$           { access_log off; log_not_found off; deny all; }

location = /robots.txt  { access_log off; log_not_found off; }
location = /favicon.ico { access_log off; log_not_found off; }