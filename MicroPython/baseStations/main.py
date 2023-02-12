from neopixel import Neopixel
import urequests as requests
import socket
import network
import machine
from time import sleep

#Program Variables
machineId = 1

#Neopixel Variables
pixels = Neopixel(1, 0, 0, "GRB")
pixels.brightness(10)

#Color Variables
white = (255, 255, 255)
red = (255, 0, 0)
green=(0, 255, 0)
blue = (0, 0, 255)
yellow = (255, 255, 0)

#Wifi Variables
ssid = "xxxxxxx"
password = 'xxxxxxx'

#Controller Variables
button = machine.Pin(15, machine.Pin.IN, machine.Pin.PULL_UP)

def setColour(colour):
    pixels.set_pixel(0, colour)
    pixels.show()

def connect():
    setColour(yellow)
    wlan = network.WLAN(network.STA_IF)
    wlan.active(True)
    wlan.connect(ssid, password)
    while not wlan.isconnected():
        print('Waiting for connection...')
        sleep(1)
    setColour(blue)
    ip = wlan.ifconfig()[0]
    print(f'Connected on {ip}')
    return ip
    
def openSocket(ip):
    address = (ip, 83)
    connection = socket.socket()
    connection.bind(address)
    connection.listen(1)
    return(connection)

def detectMaster(connection):
    while True:
        client, sender = connection.accept()
        request = str(client.recv(1024))
        client.sendto(bytes(f'Polo {machineId}', 'utf8'), sender)
        client.close()
        
        responseKey = str(request).split('\\n')
        responseKey = responseKey[-1].replace("'", "")
        
        if responseKey == "Marco":
            masterIp = sender[0]
            connection.close()
            print(f"Found master: {masterIp}")
            return masterIp
        
def mainFunction(ip):
    while True:
        setColour(white)
        if not button.value():
            url = f"http://{ip}/?machineid={machineId}&action=ChangeStatus"
            print(url)
            response = requests.get(url)
            print(response.status_code)
            if response.status_code == 200:
                setColour(green)
            else:
                setColour(red)
            sleep(2)
        
            
if __name__ == "__main__":
    ip = connect()
    connection = openSocket(ip)
    masterIp = detectMaster(connection)
    mainFunction(masterIp)
