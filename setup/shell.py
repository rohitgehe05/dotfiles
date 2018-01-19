#!/usr/bin/env python3

import json
import os
import sys
import time

from wrappers import digitalocean


# TODO 1: Write a module for AWS Lightsail.
# TODO 2: Write an error handler.

def main():
    timestamp_utc = time.time()
    writeout_file = 'logs/build-{timestamp_utc}.json'.format(timestamp_utc=timestamp_utc)
    aws_lightsail = ['awsl', 'aws lightsail']
    digital_ocean = ['do', 'digital ocean']
    iaas_platform = aws_lightsail + digital_ocean
    vendor_choice = 'do' # FIXME Parameter hard-coded to expedite testing.
    if vendor_choice in iaas_platform:
        if vendor_choice in aws_lightsail:
            pass # TODO 1
        elif vendor_choice in digital_ocean:
            os.system('{unix_command} > {writeout_file}'             \
                        .format(unix_command=digitalocean.spin_up(), \
                                writeout_file=writeout_file))
            sys.exit(0)
    else:
        pass # TODO 2


if __name__ == '__main__':
    main()