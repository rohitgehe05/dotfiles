#!/usr/bin/env bash

add-apt-repository ppa:certbot/certbot

apt-get update

apt-get -y install fail2ban firewalld nginx ntp tree python-certbot-nginx python3.6 python3-pip python3-dev ipython3 ipython3-notebook

pip3 install --upgrade pip # FIXME

pip3 install jupyter matplotlib numpy pandas 

exit