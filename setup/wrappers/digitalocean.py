#!/usr/local/bin/python3

import json
import os
import re
import socket
import time

import requests
from IPython import embed

# TODO 1: Reduce the redundancy across variables `a_header`, `c_header`, and `headers`.
# TODO 2: Port the functionality of the `curl` command to the
# `requests.post()` function.


def builder():
    endpoint = 'https://api.digitalocean.com/v2/droplets'
    # hostname = input('hostname: ') # FIXME Parameter hard-coded to expedite
    # testing.
    # FIXME Parameter hard-coded to expedite testing.
    hostname = 'node'
    payload = {}
    # pat_path = input('pat_path: ')            # FIXME Parameter hard-coded
    # to expedite testing.
    # FIXME Parameter hard-coded to expedite testing.
    pat_path = '/Users/rohitgehe05/.pat/.digitalocean'
    pa_token = open('{pat_path}'.format(pat_path=pat_path)).read().strip()
    a_header = 'Authorization: Bearer {pa_token}'.format(
        pa_token=pa_token)  # TODO 1
    c_header = 'Content-Type: application/json'                             # TODO 1
    vm_count = int(input('vm_count: '))
    if vm_count < 1:
        print('Error: You cannot spin up less than one server.')
        builder()
    elif vm_count < 2:
        payload['name'] = hostname
    else:
        payload['names'] = ['{hostname} {_}'
                            .format(hostname=hostname,
                                    _=_)
                            .replace(' ', '-')
                            for _ in range(vm_count)]
    payload['region'] = 'blr1'
    payload['size'] = '1gb'
    payload['image'] = 'ubuntu-16-04-x64'
    # TODO 1
    headers = {}
    headers['Authorization'] = 'Bearer {pa_token}'.format(
        pa_token=pa_token)  # TODO 1
    # TODO 1
    headers['Content-Type'] = 'application/json'
    keys = json.loads(requests.get(
        'https://api.digitalocean.com/v2/account/keys', headers=headers).text)['ssh_keys']
    payload['ssh_keys'] = [str(key['id']) for key in keys if key[
        'name'] == socket.gethostname()]
    # payload['tags'] = input('payload[\'tags\']: ') # FIXME Parameter
    # hard-coded to expedite testing.
    # FIXME Parameter hard-coded to expedite testing.
    payload['tags'] = ['test']
    endstate = 'curl -X POST "{endpoint}"            \
                -d \'{payload}\'                     \
                -H "{a_header}"                      \
                -H "{c_header}"'                     \
                .format(endpoint=endpoint,
                        payload=json.dumps(payload),
                        a_header=a_header.strip(),
                        c_header=c_header)  # TODO 2
    return re.sub(' +', ' ', endstate)     # TODO 2


def get_host(droplet_id, writeout_file):
    # FIXME redundant
    pa_token = open('/Users/rohitgehe05/.pat/.digitalocean').read().strip()
    writeout_file_i = writeout_file.split(
        '.')[0] + writeout_file.split('.')[1] + '-' + str(droplet_id) + '.json'
    # embed()
    os.system('curl -X GET "https://api.digitalocean.com/v2/droplets/{droplet_id}" -H "Content-Type: application/json" -H "Authorization: Bearer {pa_token}" > {writeout_file_i}'.format(
        droplet_id=droplet_id, pa_token=pa_token, writeout_file_i=writeout_file_i))
    time.sleep(5)
    payload = json.load(open(writeout_file_i))
    ip_address = payload['droplet']['networks']['v4'][0]['ip_address']
    # embed()
    return ip_address
