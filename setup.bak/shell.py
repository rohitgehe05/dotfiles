#!/usr/bin/env python3

import json
import os
import time

from wizard.core.wrappers import digitalocean


def test_build():
    timestamp_utc = time.time()
    writeout_file = 'build-{timestamp_utc}.json'.format(timestamp_utc=timestamp_utc)
    aws_lightsail = ['awsl', 'aws lightsail']
    digital_ocean = ['do', 'digital ocean']
    iaas_platform = aws_lightsail + digital_ocean
    vendor_choice = 'do'
    if vendor_choice in iaas_platform:
        if vendor_choice in aws_lightsail:
            pass                                                                 # FIXME
        elif vendor_choice in digital_ocean:
            os.system('{unix_command} > {writeout_file}'         \
                        .format(unix_command=digitalocean.create_command(), \
                                writeout_file=writeout_file))
            time.sleep(90)
            errhandler = test_setup(writeout_file, timestamp_utc)
            print(errhandler)
    else:
        pass                                                                     # FIXME

def test_setup(writeout_file, timestamp_utc):
    x = json.load(open(writeout_file)) # first json file written to
    y = x['droplet']['id'] # droplet ID
    c = digitalocean.get_ip_addresses(y, timestamp_utc) # IP address
    for d in c:
        os.system('ssh -o "StrictHostKeyChecking no" root@{d} \'bash -s\' < chopshop/remote0.sh'.format(d=d))
        os.system('ssh -o "StrictHostKeyChecking no" root@{d} \'bash -s\' < chopshop/remote1.sh'.format(d=d))
        os.system('scp /home/kenso/.ssh/id_rsa.pub root@{d}:/etc/ssh/kensotrabing/authorized_keys'.format(d=d))
        os.system('sh -c \'echo "kensotrabing:swordfish" > /home/kenso/Projects/setup/.credentials\'')
        os.system('scp /home/kenso/Projects/setup/.credentials root@{d}:/home/kensotrabing/'.format(d=d))
        os.system('ssh -o "StrictHostKeyChecking no" root@{d} \'bash -s\' < chopshop/remote2.sh'.format(d=d))
    return True


# def run():
#     aws_lightsail = ['awsl', 'aws lightsail']
#     digital_ocean = ['do', 'digital ocean']
#     iaas_platform = aws_lightsail + digital_ocean
#     vendor_choice = input('Vendor: ').lower()
#     if vendor_choice in iaas_platform:
#         if vendor_choice in aws_lightsail:
#             pass                                                                 # FIXME
#         elif vendor_choice in digital_ocean:
#             os.system('{unix_command} > build-{timestamp_utc}.json'
#                         .format(unix_command=digitalocean.create_cluster(), \
#                                 timestamp_utc=time.time()))
#     else:
#         pass                                                                     # FIXME


if __name__ == '__main__':
    test_build()
