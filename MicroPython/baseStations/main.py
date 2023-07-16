from machine import Pin, I2C
from ssd1306 import SSD1306_I2C
from neopixel import Neopixel
import urequests as requests
import socket
import network
import machine
import _thread
from time import sleep

#Program Variables
machineId = *
currentText = ''

#Neopixel Variables
pixels = Neopixel(1, 0, 0, "GRB")
pixels.brightness(10)

#Oled Variables
i2c=I2C(0,sda=Pin(16), scl=Pin(17), freq=200000)
oled = SSD1306_I2C(128, 64, i2c)

#Color Variables
white = (255, 255, 255)
red = (255, 0, 0)
green=(0, 255, 0)
blue = (0, 0, 255)
yellow = (255, 255, 0)

#Wifi Variables
ssid = "#########"
password = '#########'

#Controller Variables
button = machine.Pin(15, machine.Pin.IN, machine.Pin.PULL_UP)

def clearScreen():
    oled.fill_rect(0, 0, 128, 64, 0)
    oled.show()

def printText(text1, text2=None):
    global currentText
    if currentText != text1:
        clearScreen()
        if text1 and text2:
            oled.text(text1, 0, 10)
            oled.text(text2, 0, 23)
        else:
            oled.text(text1, 0, 10)
        oled.show()
        currentText = text1

def setColour(colour):
    pixels.set_pixel(0, colour)
    pixels.show()

def connect():
    setColour(yellow)
    wlan = network.WLAN(network.STA_IF)
    wlan.active(True)
    wlan.connect(ssid, password)
    while not wlan.isconnected():
        print('Izchakvane na svyrzvane...')
        printText('Izchakvane na', 'svyrzvane...')
        sleep(1)
    setColour(blue)
    ip = wlan.ifconfig()[0]
    print(f'Svyrzano na {ip}')
    printText('Svyrzano na:', ip)
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
            print(f"Nameren server: {masterIp}")
            printText("Nameren server:", masterIp)
            sleep(3)
            return masterIp

def setMachineStatus(ip):
    response = requests.get(f"http://{ip}/extraActions.php?machineid={machineId}&action=GetStatus")
    if response.text == 'online':
        setColour(green)
    elif response.text == 'offline':
        setColour(red)
    response.close()

def mainFunction(ip):
    while True:
        setMachineStatus(ip)
        printText('Izchakvane...')
        if not button.value():
            response = requests.get(f"http://{ip}/extraActions.php?machineid={machineId}&action=ChangeStatus")
            print(response.status_code)
            if response.status_code == 200:
                if response.text == 'online':
                    setColour(green)
                elif response.text == 'offline':
                    setColour(red)
                printText('Uspeshen','request!')
            else:
                setColour(red)
                printText('Neuspeshen','rekuest!')
            response.close()
            sleep(1)

if __name__ == '__main__':
    ip = connect()
    connection = openSocket(ip)
    masterIp = detectMaster(connection)
    mainFunction(masterIp)
