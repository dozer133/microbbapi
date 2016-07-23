# microbbapi
A very tiny REST API for phpBB

# restfulish nginx config

server {
 ...
 location /api/v1/ {
                try_files $uri $uri/ /api.php?$args;
        }
 ...
}
