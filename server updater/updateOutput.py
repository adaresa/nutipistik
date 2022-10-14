import urllib3
from serverSecret import *

def switchOutput(state):
    url = getUpdateOutputURL(state)
    urllib3.PoolManager().request('GET', url)

url = getServerValueURL()

response = urllib3.PoolManager().request('GET', url)

values = {
    "control_type": "",
    "switch_state": "",
    "price_limit": "",
    "current_price": ""
}

response = response.data.decode('utf-8').strip().split(",")
for i in range(len(response)):
    if i == 0:
        values["control_type"] = response[i]
    elif i == 1:
        values["switch_state"] = response[i]
    elif i == 2:
        values["price_limit"] = response[i]
    elif i == 3:
        values["current_price"] = response[i]
        
if values["control_type"] == "1":
    print("Control type: Price Limit")
    if float(values["current_price"]) < float(values["price_limit"]):
        print("Current price is lower than price limit. Switch is ON")
        switchOutput(1)
    else:
        print("Current price is higher than price limit. Switch is OFF")
        switchOutput(0)
        
elif values["control_type"] == "2":
    print("Control type: Manual")
    if values["switch_state"] == "1":
        print("Switch is ON")
        switchOutput(1)
    elif values["switch_state"] == "0":
        print("Switch is OFF")
        switchOutput(0)