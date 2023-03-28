import datetime
import json
import urllib3
    
def PriceLimitOutput(current_price, price_limit): # If current price is lower than price limit        
    if current_price <= price_limit:
        return True
    else:
        return False

def SwitchOutput(switch): # If switch is on
    if int(switch):
        return True
    else:
        return False

def CheapestHoursOutput(cheapest_hours, current_price): # If current time is in cheapest hours  
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


    
    price_dict = {}

    j = 0
    # get all from "data", "ee" in the json
    for i in data["data"]["ee"]:
        # populate price_dict with "timestamp": "price"
        price_dict[i["timestamp"]] = i["price"]

    # from price_dict, set the NUM_OF_CHEAPEST_HOURS'th cheapest hour as the price_limit
    price_limit = sorted(price_dict.values())[cheapest_hours-1]
    
    if current_price <= price_limit:
        return True
    else:
        return False
    
def SmartHoursOutput(chp_day_hours, exp_day_hours, chp_day_thold, exp_day_thold, current_price):
    print(f'chp_day_hours: {chp_day_hours}, exp_day_hours: {exp_day_hours}, chp_day_thold: {chp_day_thold}, exp_day_thold: {exp_day_thold}, current_price: {current_price}')
    # Get start time in UTC
    start = datetime.datetime.utcnow().replace(hour=0, minute=0, second=0, microsecond=0)
    start = start - datetime.timedelta(hours=2)
    start = start.strftime("%Y-%m-%dT%H%%3A%M%%3A%S.000Z")
    # Get end time in UTC
    end = datetime.datetime.utcnow().replace(hour=23, minute=59, second=59, microsecond=999)
    end = end - datetime.timedelta(hours=2)
    end = end.strftime("%Y-%m-%dT%H%%3A%M%%3A%S.999Z")
    
    # Make request to https://dashboard.elering.ee/api/nps/price?
    url = "https://dashboard.elering.ee/api/nps/price?start=" + start + "&end=" + end
    response = urllib3.PoolManager().request('GET', url)
    data = json.loads(response.data.decode('utf-8'))

    price_dict = {}
    # Get all from "data", "ee" in the json
    for i in data["data"]["ee"]:
        # Populate price_dict with "timestamp": "price"
        price_dict[i["timestamp"]] = i["price"]

    current_day_average_price = sum(price_dict.values()) / 24
    print(f'current_day_average_price: {current_day_average_price}')

    # Calculate the number of hours the plug run based on a linear interpolation
    if current_day_average_price <= chp_day_thold:
        threshold_hours = chp_day_hours
    elif current_day_average_price >= exp_day_thold:
        threshold_hours = exp_day_hours
    else:
        price_ratio = (current_day_average_price - chp_day_thold) / (exp_day_thold - chp_day_thold)
        threshold_hours = int(round(chp_day_hours + price_ratio * (exp_day_hours - chp_day_hours)))


    print(f'threshold_hours: {threshold_hours}')
    
    # Sort the price_dict based on price values
    sorted_prices = sorted(price_dict.items(), key=lambda item: item[1])

    # Get the threshold price from the sorted prices
    threshold_price = sorted_prices[threshold_hours-1][1]
    
    print(threshold_price)
    
    print(current_price)
    
    # Check if the current price is less than or equal to the threshold price
    if current_price <= threshold_price:
        return True
    else:
        return False
