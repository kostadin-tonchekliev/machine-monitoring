#!/bin/bash

counter=0

function initializeMachine {
	leds=$(mysql -u admin -padminpassword123 monitoring_app --skip-column-names -e "SELECT onPin, offPin FROM gpioPins WHERE id = $1;")
	for led in $leds; do
		sudo raspi-gpio set $led op
		sudo raspi-gpio set $led dh
		sleep 1
	done
	
	for led in $leds; do
		sudo raspi-gpio set $led dl
	done
}

localIp=$(hostname -I | cut -d '.' -f1-3 | sed 's/ //g')
while [[ -z $localIp ]]; do
	localIp=$(hostname -I | cut -d '.' -f1-3 | sed 's/ //g')
done

echo "Found local IP: $localIp"

#ips=$(nmap -n -sn --open "$localIp.*" -oG - | awk '/Up$/{print $2}')
ips=$(nmap -p 83 -n --open "$localIp.*" -oG - | awk '/Up$/{print $2}' | uniq)
while [[ -z $ips || $counter == 10 ]]; do
	ips=$(nmap -p 83 -n --open "$localIp.*" -oG - | awk '/Up$/{print $2}' | uniq)
	counter=$((counter+1))
done

for ip in $ips; do
	response=$(curl --silent --http0.9 --connect-timeout 5 -X GET -d 'Marco' $ip:83)
	if [[ -n $response ]]; then
		responseKey=$(awk '{print $1}' <<< $response)
		machineId=$(awk '{print $2}' <<< $response)
		if [[ $responseKey == 'Polo' ]]; then
			echo "Found device: $ip: $machineId"
			initializeMachine $machineId
		fi
	fi
done

