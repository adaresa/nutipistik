import urllib3
from serverSecret import *
from OutputFuncs import *

def switchOutput(state):
    print(state)
    url = getUpdateOutputURL(state)
    urllib3.PoolManager().request('GET', url)

def lambda_handler(x, y):
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
        elif i == 4:
            values["cheapest_hours"] = response[i]
            
    if values["control_type"] == "1": # Price limit
        print("Control type: Price Limit")
        if PriceLimitOutput(values["current_price"], values["price_limit"]):
            switchOutput(1)
        else:
            switchOutput(0)
            
    elif values["control_type"] == "2": # Manual control
        print("Control type: Manual")
        if SwitchOutput(values["switch_state"]):
            switchOutput(1)
        else:
            switchOutput(0)
            
    elif values["control_type"] == "3": # Cheapest hours
        print("Control type: Cheapest hours")
        if CheapestHoursOutput(int(values["cheapest_hours"])):
            switchOutput(1)
        else:
            switchOutput(0)
