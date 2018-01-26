#!/usr/bin/env bash

sh -c 'echo "set const" >> .nanorc'

sh -c 'echo "set tabsize 4" >> .nanorc'

sh -c 'echo "set tabstospaces" >> .nanorc'

adduser --disabled-password --gecos "" rohitgehe05

usermod -aG sudo rohitgehe05

cp .nanorc /home/rohitgehe05/

mkdir -p /etc/ssh/rohitgehe05