#!/usr/bin/env bash

sudo add-apt-repository ppa:libreoffice/ppa
sudo apt -y update
sudo apt -y dist-upgrade
sudo apt -y install libreoffice-gtk2 libreoffice-gnome
