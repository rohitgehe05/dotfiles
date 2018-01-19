#!/usr/bin/env python3

import json
import re
import socket

import requests


# TODO 1: Reduce the redundancy across variables `a_header`, `c_header`, and `headers`.
# TODO 2: Port the functionality of the `curl` command to the `requests.post()` function.

def spin_up():
    endpoint = 'https://api.digitalocean.com/v2/droplets'
    hostname = 'worker'
    payload = {}
    # pat_path = input('pat_path: ')                                         # FIXME Parameter hard-coded to expedite testing.
    # pa_token = open('{pat_path}'.format(pat_path=pat_path)).read().strip() # FIXME Parameter hard-coded to expedite testing.
    pa_token = open('/home/kenso/.pat/.digitalocean').read().strip()         # FIXME Parameter hard-coded to expedite testing.
    a_header = 'Authorization: Bearer {pa_token}'.format(pa_token=pa_token) # TODO 1
    c_header = 'Content-Type: application/json'                             # TODO 1
    vm_count = int(input('vm_count: '))
    if vm_count < 1:
        print('Error: You cannot spin up less than one server.')
        create_digital_ocean_vps()
    elif vm_count < 2:
        payload['name'] = hostname
    else:
        payload['names'] = ['{hostname} {_}'               \
                                .format(hostname=hostname, \
                                        _=_)               \
                                .replace(' ', '-')         \
                                for _ in range(vm_count)]
    payload['region'] = 'nyc3'
    payload['size']   = '1gb'
    payload['image']  = 'ubuntu-16-04-x64'
    headers = {}                                                             # TODO 1
    headers['Authorization'] = 'Bearer {pa_token}'.format(pa_token=pa_token) # TODO 1
    headers['Content-Type'] = 'application/json'                             # TODO 1
    keys = json.loads(requests.get('https://api.digitalocean.com/v2/account/keys', headers=headers).text)['ssh_keys']
    payload['ssh_keys'] = [str(key['id']) for key in keys if key['name']==socket.gethostname()]
    payload['tags'] = ['test']
    endstate = 'curl -X POST "{endpoint}"            \
                -d \'{payload}\'                     \
                -H "{a_header}"                      \
                -H "{c_header}"'                     \
                .format(endpoint=endpoint,           \
                        payload=json.dumps(payload), \
                        a_header=a_header.strip(),   \
                        c_header=c_header) # TODO 2
    return re.sub(' +', ' ', endstate)     # TODO 2