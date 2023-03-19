import urllib3
from serverSecret import *
from OutputFuncs import *
import time

def switchOutput(id, password, state):
    print(state)
    url = f"https://nutipistik.fun/TX.php?id={id}&pw={password}&out={state}"
    urllib3.PoolManager().request('GET', url)

def lambda_handler(x, y):
    # time.sleep(10)
    for id, password in SERVER_ID_AND_PASS.items():
        url = getServerValueURL(id, password)
        values = getServerValues(url)

        if values["control_type"] == "1":  # Price limit
            print("Control type: Price Limit")
            if PriceLimitOutput(values["current_price"], values["price_limit"]):
                switchOutput(id, password, 1)
            else:
                switchOutput(id, password, 0)

        elif values["control_type"] == "2":  # Manual control
            print("Control type: Manual")
            if SwitchOutput(values["switch_state"]):
                switchOutput(id, password, 1)
            else:
                switchOutput(id, password, 0)

        elif values["control_type"] == "3":  # Cheapest hours
            print("Control type: Cheapest hours")
            if CheapestHoursOutput(int(values["cheapest_hours"])):
                switchOutput(id, password, 1)
            else:
                switchOutput(id, password, 0)

        elif values["control_type"] == "4":  # Selected hours
            print("Control type: Selected hours")
            if SwitchOutput(values["selected_hour"]):
                switchOutput(id, password, 1)
            else:
                switchOutput(id, password, 0)
