#!/usr/bin/env bash

# No IP addresses in these scripts, for now, since we'll 
# ssh -p 6174 kensotrabing@159.203.109.145

mysql_secure_installation

swordfish # Enter password for user root:

y # Press y|Y for Yes, any other key for No:

2 # Please enter 0 = LOW, 1 = MEDIUM and 2 = STRONG:

n # Change the password for root ? ((Press y|Y for Yes, any other key for No) :

y # Remove anonymous users? (Press y|Y for Yes, any other key for No) :

y # Disallow root login remotely? (Press y|Y for Yes, any other key for No) :

y # Remove test database and access to it? (Press y|Y for Yes, any other key for No) :

y # Reload privilege tables now? (Press y|Y for Yes, any other key for No) :

sudo nano /etc/php/7.0/fpm/php.ini

swordfish

sudo sed -i -e '/^;cgi\.fix_pathinfo=1/s/^.*$/cgi\.fix_pathinfo=0/' /etc/php/7.0/fpm/php.ini

sudo sed -i -e '/^post_max_size = 8M/s/^.*$/post_max_size = 9999M/' /etc/php/7.0/fpm/php.ini

sudo sed -i -e '/^upload_max_filesize = 2M/s/^.*$/upload_max_filesize = 9999M/' /etc/php/7.0/fpm/php.ini

sudo systemctl restart php7.0-fpm

sudo sed -i '/^[ \t]*server_name _;/a     \}' /etc/nginx/sites-available/default
# sudo sed -i '/    server_name _;/a         log_not_found off;' /etc/nginx/sites-available/default
# sudo sed -i '/    server_name _;/a         expires max;' /etc/nginx/sites-available/default
# sudo sed -i '/    server_name _;/a     location ~\* \\.(css|gif|ico|jpeg|jpg|js|png)\$ \{' /etc/nginx/sites-available/default
# sudo sed -i '/    server_name _;/a     location = \/robots\.txt \{ log_not_found off; access_log off; allow all; \}' /etc/nginx/sites-available/default
# sudo sed -i '/    server_name _;/a     location = \/favicon\.ico \{ log_not_found off; access_log off; \}' /etc/nginx/sites-available/default
# sudo sed -i '/    server_name _;/a \
# ' /etc/nginx/sites-available/default


#    server_name _; # use sed to append to this file in this file: /etc/nginx/sites-available/default

###
### Also the default file might be overwritten when nginx updates
###




######## All IP addresses need to be replaced with dynamic inputs

sudo sed -i -e 's/^[ \t]*index index\.html index\.htm index\.nginx-debian\.html;/    index index\.php index\.html index\.htm index\.nginx-debian\.html;/g' /etc/nginx/sites-available/default

sudo sed -i -e 's/^[ \t]*server_name _;/    server_name 159\.203\.109\.145;/g' /etc/nginx/sites-available/default

sudo sed -i -e 's/^[ \t]*#location ~ \\.php\$ \{/    location ~ \\.php\$ \{/g' /etc/nginx/sites-available/default

sudo sed -i -e 's/^[ \t]*#[ \t]include snippets\/fastcgi-php\.conf;/        include snippets\/fastcgi-php\.conf;/g' /etc/nginx/sites-available/default

sudo sed -i -e 's/^[ \t]*#[ \t]fastcgi_pass unix:\/run\/php\/php7\.0-fpm\.sock;/        fastcgi_pass unix:\/run\/php\/php7\.0-fpm\.sock;/g' /etc/nginx/sites-available/default

sudo sed -i -e '0,/^[ \t]*#\}/s/^[ \t]*#\}/    \}/' /etc/nginx/sites-available/default

sudo sed -i -e 's/^[ \t]*#location ~ \/\\.ht {/    location ~ \/\\.ht {/g' /etc/nginx/sites-available/default

sudo sed -i -e 's/^[ \t]*#[ \t]deny all;/        deny all;/g' /etc/nginx/sites-available/default

sudo sed -i -e '0,/^[ \t]*#\}/s/^[ \t]*#\}/    \}/' /etc/nginx/sites-available/default

sudo nginx -t

sudo systemctl reload nginx

# Tests to make sure nginx is configured properly to PHP.
#
# sudo sh -c 'echo "<?php" >> /var/www/html/info.php'
#
# sudo sh -c 'echo "phpinfo();" >> /var/www/html/info.php'
#
# curl "http://159.203.109.145/info.php"
# 
# sudo rm /var/www/html/info.php

mysql -u root -p

CREATE DATABASE wordpress DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

GRANT ALL ON wordpress.* TO 'wordpressuser'@'localhost' IDENTIFIED BY 'password';

FLUSH PRIVILEGES;

EXIT;

sed -i '/# Default server configuration/a \}' /etc/nginx/sites-available/default





sudo add-apt-repository ppa:certbot/certbot

sudo apt-get update

sudo apt-get install python-certbot-nginx

