from neopixel import Neopixel
import network

#Network Variables
ssid= 'xxxxxxxx'
password = 'xxxxxxx'

#Neopixel Variables
pixels = Neopixel(1, 0, 0, "GRB")
pixels.brightness(10)

#Colous
white = (255, 255, 255)
red = (255, 0, 0)
green=(0, 255, 0)

def setColour(colour):
    pixels.set_pixel(0, colour)
    pixels.show()
    
def createNetwork():
    print(white)
    setColour(white)
    accessPoint = network.WLAN(network.AP_IF)
    accessPoint.config(essid=ssid, password=password)
    accessPoint.active(True)
    print(red)
    setColour(red)
    while accessPoint.active() == False:
        pass


if __name__ == "__main__":
    print(green)
    createNetwork()
    setColour(green)
    while True:
        pass
