#!/usr/bin/env bash

add-apt-repository ppa:certbot/certbot

apt-get update

debconf-set-selections <<< 'mysql-server mysql-server/root_password password swordfish'

debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password swordfish'

apt-get -y install fail2ban firewalld mysql-server nginx ntp python-certbot-nginx php-fpm php-mysql php-curl php-gd php-mbstring php-mcrypt php-xml php-xmlrpc php7.0-fpm php7.0-mysql php7.0-curl php7.0-gd php7.0-json php7.0-mcrypt php7.0-opcache php7.0-xml php7.0-curl php7.0-gd php7.0-mbstring php7.0-mcrypt php7.0-xml php7.0-xmlrpc

# mysql_secure_installation

exit