#!/bin/bash

function spacer {
	echo "---------------------------------"
}

function startServices {
	if [[ "$(systemctl is-active apache2.service)" != 'active' ]]; then
        	sudo systemctl start apache2.service
        	echo -e "\t[+] Apache succesfully started"
	else
        	echo -e "\t[-] Apache is already active"
	fi

	if [[ "$(systemctl is-active mariadb.service)" != 'active' ]]; then
        	sudo systemctl start mariadb.service
        	echo -e "\t[+] Mysql succesfully started"
	else
        	echo -e "\t[-] Mysql is already active"
	fi
}

function setupLeds {
	leds=$(mysql -u admin -padminpassword123 monitoring_app --skip-column-names -e "SELECT onPin, offPin FROM gpioPins ;")
	for led in $leds; do
		raspi-gpio set $led op
	done
	echo -e "\t[+] Leds initialized"
}

function initializeMachine {
        leds=$(mysql -u admin -padminpassword123 monitoring_app --skip-column-names -e "SELECT onPin, offPin FROM gpioPins WHERE id = $1;")
        for led in $leds; do
                raspi-gpio set $led dh
                sleep 1
        done

        for led in $leds; do
                raspi-gpio set $led dl
        done
}

function hostMatching {
	declare -a matchedHosts
	localIp=$(hostname -I)
	localIpCut=$(hostname -I | cut -d '.' -f1-3 | sed 's/ //g')
	while [[ -z $localIpCut && -z $localIp ]]; do
		localIp=$(hostname -I)
        	localIpCut=$(hostname -I | cut -d '.' -f1-3 | sed 's/ //g')
	done
	echo -e "\t[+] Found local IP: $localIp"
	echo -e "\t[!] Starting infinite loop"
	
	while true; do
		ips=$(nmap -p 83 -n --open "$localIpCut.*" -oG - | awk '/Up$/{print $2}' | uniq)
		while [[ -z $ips ]]; do
			ips=$(nmap -p 83 -n --open "$localIpCut.*" -oG - | awk '/Up$/{print $2}' | uniq)
		done

		if [[ $(wc -l <<< $ips) -gt 1 ]]; then
			echo -e "\t[+] Found IP's: $(echo $ips | tr '\n' ' ')"
		else
			echo -e "\t[+] Found IP: $ips"
		fi

		for ip in $ips; do
			if [[ -z $(echo ${matchedHosts[@]} | grep $ip) ]]; then
				response=$(curl --silent --http0.9 --connect-timeout 5 -X GET -d 'Marco' $ip:83)
				if [[ -n $response ]]; then
					responseKey=$(awk '{print $1}' <<< $response)
					machineId=$(awk '{print $2}' <<< $response)
					if [[ $responseKey == 'Polo' ]]; then
						echo -e "\t[+] Found device: $ip: $machineId"
						initializeMachine $machineId
						matchedHosts[${#matchedHosts[@]}]=$ip
					fi
				fi
			fi
		done
	done
} 

echo "[*] Starting boot sequence"

raspi-gpio set 21 op
raspi-gpio set 21 dh
startServices
setupLeds

echo "[+] Boot sequence finished"
spacer
echo "[*] Starting host matching"
hostMatching
