import urllib3
from serverSecret import *
from OutputFuncs import *
import time

def switchOutput(state):
    print(state)
    url = getUpdateOutputURL(state)
    urllib3.PoolManager().request('GET', url)

def lambda_handler(x, y):
    time.sleep(10)
    url = getServerValueURL()
    values = getServerValues(url)
    
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
            
    elif values["control_type"] == "4": # Selected hours
        print("Control type: Selected hours")
        if SwitchOutput(values["selected_hour"]):
            switchOutput(1)
        else:
            switchOutput(0)
