#!/bin/bash

if [[ "$(systemctl is-active apache2.service)" != 'active' ]]; then
	sudo systemctl start apache2.service
	echo '[+] Apache succesfully started'
else
	echo '[+] Apache is already active'
fi

if [[ "$(systemctl is-active mariadb.service)" != 'active' ]]; then
	sudo systemctl start mariadb.service
	echo '[+] Mysql succesfully started'
else
	echo '[+] Mysql is already active'
fi
