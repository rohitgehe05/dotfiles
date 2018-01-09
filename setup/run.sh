#!/usr/bin/env bash

python3 shell.py
find . -name __pycache__ -exec rm -rf {} \; > /dev/null 2>&1
