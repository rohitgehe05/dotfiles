#!/usr/bin/env python3

import fileinput
import os


def configure_fail2ban(email, host, hostname, username, password, port):
    for line in fileinput.input(['configure/fail2ban.sh'], inplace=True, backup='.bak'):
        print(line.replace('<email_addr>', '{email}'.format(email=email)), end='')
        print(line.replace('<vps_ip_addr>', '{host}'.format(host=host)), end='')
        print(line.replace('<vps_name>', '{name}'.format(name=hostname)), end='')
        print(line.replace('<os_username>', '{username}'.format(username=username)), end='')
        print(line.replace('<os_password>', '{password}'.format(email=password)), end='')
        print(line.replace('<port>', '{port}'.format(port=port)), end='')
    return True

def configure_firewalld(email, host, hostname, username, password, port):
    for line in fileinput.input(['configure/firewalld.sh'], inplace=True, backup='.bak'):
        print(line.replace('<email_addr>', '{email}'.format(email=email)), end='')
        print(line.replace('<vps_ip_addr>', '{host}'.format(host=host)), end='')
        print(line.replace('<vps_name>', '{name}'.format(name=hostname)), end='')
        print(line.replace('<os_username>', '{username}'.format(username=username)), end='')
        print(line.replace('<os_password>', '{password}'.format(email=password)), end='')
        print(line.replace('<port>', '{port}'.format(port=port)), end='')
    return True

def configure_kswapd(email, host, hostname, username, password, port):
    for line in fileinput.input(['configure/kswapd.sh'], inplace=True, backup='.bak'):
        print(line.replace('<email_addr>', '{email}'.format(email=email)), end='')
        print(line.replace('<vps_ip_addr>', '{host}'.format(host=host)), end='')
        print(line.replace('<vps_name>', '{name}'.format(name=hostname)), end='')
        print(line.replace('<os_username>', '{username}'.format(username=username)), end='')
        print(line.replace('<os_password>', '{password}'.format(email=password)), end='')
        print(line.replace('<port>', '{port}'.format(port=port)), end='')
    return True

def configure_nano(email, host, hostname, username, password, port):
    for line in fileinput.input(['configure/nano.sh'], inplace=True, backup='.bak'):
        print(line.replace('<email_addr>', '{email}'.format(email=email)), end='')
        print(line.replace('<vps_ip_addr>', '{host}'.format(host=host)), end='')
        print(line.replace('<vps_name>', '{name}'.format(name=hostname)), end='')
        print(line.replace('<os_username>', '{username}'.format(username=username)), end='')
        print(line.replace('<os_password>', '{password}'.format(email=password)), end='')
        print(line.replace('<port>', '{port}'.format(port=port)), end='')
    return True

def configure_nginx(email, host, hostname, username, password, port):
    for line in fileinput.input(['configure/nginx.sh'], inplace=True, backup='.bak'):
        print(line.replace('<email_addr>', '{email}'.format(email=email)), end='')
        print(line.replace('<vps_ip_addr>', '{host}'.format(host=host)), end='')
        print(line.replace('<vps_name>', '{name}'.format(name=hostname)), end='')
        print(line.replace('<os_username>', '{username}'.format(username=username)), end='')
        print(line.replace('<os_password>', '{password}'.format(email=password)), end='')
        print(line.replace('<port>', '{port}'.format(port=port)), end='')
    return True

def configure_ntpd(email, host, hostname, username, password, port):
    for line in fileinput.input(['configure/ntpd.sh'], inplace=True, backup='.bak'):
        print(line.replace('<email_addr>', '{email}'.format(email=email)), end='')
        print(line.replace('<vps_ip_addr>', '{host}'.format(host=host)), end='')
        print(line.replace('<vps_name>', '{name}'.format(name=hostname)), end='')
        print(line.replace('<os_username>', '{username}'.format(username=username)), end='')
        print(line.replace('<os_password>', '{password}'.format(email=password)), end='')
        print(line.replace('<port>', '{port}'.format(port=port)), end='')
    return True

def configure_sshd(email, host, hostname, username, password, port):
    for line in fileinput.input(['configure/sshd.sh'], inplace=True, backup='.bak'):
        print(line.replace('<email_addr>', '{email}'.format(email=email)), end='')
        print(line.replace('<vps_ip_addr>', '{host}'.format(host=host)), end='')
        print(line.replace('<vps_name>', '{name}'.format(name=hostname)), end='')
        print(line.replace('<os_username>', '{username}'.format(username=username)), end='')
        print(line.replace('<os_password>', '{password}'.format(email=password)), end='')
        print(line.replace('<port>', '{port}'.format(port=port)), end='')
    return True

def configure_systemd(email, host, hostname, username, password, port):
    for line in fileinput.input(['configure/systemd.sh'], inplace=True, backup='.bak'):
        print(line.replace('<email_addr>', '{email}'.format(email=email)), end='')
        print(line.replace('<vps_ip_addr>', '{host}'.format(host=host)), end='')
        print(line.replace('<vps_name>', '{name}'.format(name=hostname)), end='')
        print(line.replace('<os_username>', '{username}'.format(username=username)), end='')
        print(line.replace('<os_password>', '{password}'.format(email=password)), end='')
        print(line.replace('<port>', '{port}'.format(port=port)), end='')
    return True

def configure_timesyncd(email, host, hostname, username, password, port):
    for line in fileinput.input(['configure/timesyncd.sh'], inplace=True, backup='.bak'):
        print(line.replace('<email_addr>', '{email}'.format(email=email)), end='')
        print(line.replace('<vps_ip_addr>', '{host}'.format(host=host)), end='')
        print(line.replace('<vps_name>', '{name}'.format(name=hostname)), end='')
        print(line.replace('<os_username>', '{username}'.format(username=username)), end='')
        print(line.replace('<os_password>', '{password}'.format(email=password)), end='')
        print(line.replace('<port>', '{port}'.format(port=port)), end='')
    return True


###
###
### FIXME
###
###
def upload_key(host, username):
    os.system('scp ~/.ssh/id_rsa.pub root@{host}:/etc/ssh/{username}/authorized_keys'.format(host=host, username=username))
###
###
###
###
###

###
###
### FIXME
###
###
def spinup_server(email, host, hostname, username, password, port):
    if os.path.exists('~/.ssh/id_rsa.pub') and os.path.exists('~/.ssh/id_rsa'):
        os.system('scp ~/.ssh/id_rsa.pub root@{host}:/etc/ssh/{username}/authorized_keys'.format(host=host, username=username))

        if os.path.exists('instance/credentials.dsv'):
            submitted_credentials = '{username}:{password}'.format(username=username, password=password)
            with open('instance/credentials.dsv', "r") as f:
                stored_credentials = f.read()

                if submitted_credentials == stored_credentials:
                    os.system('scp instance/credentials.dsv root@{host}:/home/{username}/'.format(host=host, username=username))

                else:
                    acceptable_inputs = ['1', "0", 'y', "n", 'yes', "no", 'True', "False"]
                    use_submitted_credentials = input('The credentials that you \
                        submitted don\'t match the credentials that\'re stored. \
                        Do you want to continue with the credentials that you  \
                        submitted? Yes or no? ')

                    if str(use_submitted_credentials.lower()) is in acceptable_inputs:

                        if str(use_submitted_credentials.lower()) == '1' or "y" or 'yes' or "True":
                            os.system('scp instance/credentials.dsv root@{host}:/home/{username}/'.format(host=host, username=username))

                        else:
                            overwrite_stored_credentials == input('Do you want to \
                                overwrite with the credentials that\'re stored? \
                                Yes or no? ')

                            if str(overwrite_stored_credentials.lower()) is in acceptable_inputs:

                                if str(overwrite_stored_credentials.lower()) == '1' or "y" or 'yes' or "True":
                                    with open('instance/credentials.dsv', "w") as f:
                                        f.write('{username}:{password}'.format(username=username, password=password))
                                        os.system('scp instance/credentials.dsv root@{host}:/home/{username}/'.format(host=host, username=username))

                                else:
                                    # FIXME back-up file or save the submitted credentials to a new namespace
                                    pass

                            else:
                                # FIXME not acceptable input
                                pass

                    else:
                        # FIXME not acceptable input
                        pass

        else:
            with open('instance/credentials.dsv', "w") as f:
                f.write('{username}:{password}'.format(username=username, password=password))
                os.system('scp instance/credentials.dsv root@{host}:/home/{username}/'.format(host=host, username=username))

    elif os.path.exists('~/.ssh/id_rsa.pub'):
        # FIXME finish this message
        a = input('That\'s weird. You have a public key, but you don\'t have a \
            private key. ')
        # FIXME finish control flow for this anomaly
        pass

    elif os.path.exists('~/.ssh/id_rsa'):
        # FIXME finish this message
        b = input('That\'s weird. You have a private key, but you don\'t have a \
            public key occupying the default namespace, for one. ')
        # FIXME finish control flow for this anomaly
        pass

    elif os.path.exists('~/.ssh/'):
        # FIXME finish this message
        c = input('That\'s weird. You have a directory named .ssh, but you don\'t \
            have a public or private key occupying the default namespace. ')
        # FIXME finish control flow for this anomaly
        pass

    else:
        # Will the following command behave interactively?
        os.system('ssh-keygen -t rsa')
        pass
###
###
###
###
###


if __name__ == '__main___':

    # TODO figure out how many servers the user wants to deploy
    #      and if they want to use the same settings for each node
    # TODO sanitize inputs
    email     = input('\nEnter your e-mail address: ')
    host      = input('Enter your server\'s IP address: ')
    hostname  = input('Enter your server\'s name: ')
    username  = input('Enter a username: ')
    password  = input('Enter a password: ')
    port      = input('Enter a number between 49151 and 65536: ')

    spinup_server(email, host, hostname, username, password, port)

