import datetime
import json
import urllib3
    
def PriceLimitOutput(current_price, price_limit, unit): # If current price is lower than price limit
    if unit == "kWh":
        price_limit = float(price_limit) * 1000
        # round current_price from, for example, 123.6 to 120.0
        current_price = round(float(current_price), -1)
        
    if float(current_price) <= float(price_limit):
        return True
    else:
        return False

def SwitchOutput(switch): # If switch is on
    if int(switch):
        return True
    else:
        return False

def CheapestHoursOutput(cheapest_hours): # If current time is in cheapest hours
    # if cheapest_hours is < 1, set it to 1, if it is > 24, set it to 24
    if cheapest_hours < 1:
        cheapest_hours = 1
    elif cheapest_hours > 24:
        cheapest_hours = 24
    
    
    # Get start time in UTC    
    start = datetime.datetime.utcnow().replace(hour=0, minute=0, second=0, microsecond=0)
    start = start - datetime.timedelta(hours=2)
    start = start.strftime("%Y-%m-%dT%H%%3A%M%%3A%S.000Z")
    # Get end time in UTC
    end = datetime.datetime.utcnow().replace(hour=23, minute=59, second=59, microsecond=999)
    end = end - datetime.timedelta(hours=2)
    end = end.strftime("%Y-%m-%dT%H%%3A%M%%3A%S.999Z")

    # make request to https://dashboard.elering.ee/api/nps/price?
    url = "https://dashboard.elering.ee/api/nps/price?start=" + start + "&end=" + end
    response = urllib3.PoolManager().request('GET', url)
    data = json.loads(response.data.decode('utf-8'))


    current_hour = datetime.datetime.utcnow().hour + 2
    
    price_dict = {}

    j = 0
    # get all from "data", "ee" in the json
    for i in data["data"]["ee"]:
        # populate price_dict with "timestamp": "price"
        price_dict[i["timestamp"]] = i["price"]
        if j == current_hour:
            current_price = i["price"]
        j += 1

    # from price_dict, set the NUM_OF_CHEAPEST_HOURS'th cheapest hour as the price_limit
    price_limit = sorted(price_dict.values())[cheapest_hours-1]
    
    if current_price <= price_limit:
        return True
    else:
        return False
    
