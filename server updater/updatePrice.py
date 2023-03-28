import urllib3
import json
import datetime
from serverSecret import *
import dateutil.tz

def lambda_handler(x, y):
    # Get start time in UTC
    start = datetime.datetime.utcnow()
    # shift 2 hours
    start = start.replace(hour=0, minute=0, second=0, microsecond=0)
    start = start + datetime.timedelta(hours=-2)
    start = start.strftime("%Y-%m-%dT%H%%3A%M%%3A%S.000Z")
    # Get end time in UTC
    end = datetime.datetime.utcnow()
    end = end + datetime.timedelta(days=1)
    end = end.replace(hour=23, minute=59, second=59, microsecond=999)
    end = end.strftime("%Y-%m-%dT%H%%3A%M%%3A%S.999Z")

    # make request to https://dashboard.elering.ee/api/nps/price?
    url = f"https://dashboard.elering.ee/api/nps/price?start={start}&end={end}"
    response = urllib3.PoolManager().request('GET', url)
    data = json.loads(response.data.decode('utf-8'))

    # print(url)
    price_dict = {}
    
    # Get the selected region
    url = getServerValueURL()

    # Get the values from the server
    values = getServerValues(url)

    # get all from "data", values[region] in the json
    for i in data["data"][values["region"]]:
        # populate price_dict with "timestamp": "price"
        price_dict[i["timestamp"]] = i["price"]

    # sort price_dict by key
    price_dict = dict(sorted(price_dict.items()))

    # print price_dict
    # i = 0
    # for key, value in price_dict.items():
    #     print(f"{i+1}. {key}, {value}")
    #     i+=1
        
    # to find current_price, make request to https://dashboard.elering.ee/api/nps/price/'values[region]'/current
    url = "https://dashboard.elering.ee/api/nps/price/" + values["region"] + "/current"
    response = urllib3.PoolManager().request('GET', url)
    data = json.loads(response.data.decode('utf-8'))
    current_price = data["data"][0]["price"]
    # make POST request to https://nutipistik.fun/
    url = getUpdateCurrentPriceURL(current_price)
    response = urllib3.PoolManager().request('GET', url)

    # get date in UTC
    date_utc = datetime.datetime.utcnow().date()

    # get date in Europe/Tallinn
    date_est = dateutil.tz.gettz('Europe/Tallinn')
    date_est = datetime.datetime.now(date_est).date()

    # for each hour in price_dict, make POST request to https://nutipistik.fun/
    i = 0
    if date_utc != date_est:
        for key, value in price_dict.items():
            if i < 24:
                url = getUpdateTomorrowPriceURL(i, 0)
                response = urllib3.PoolManager().request('GET', url)
                i+=1
            else:
                url = getUpdateTodayPriceURL(i-24, value)
                response = urllib3.PoolManager().request('GET', url)
                i+=1
                
    elif len(price_dict) == 24:
        for key, value in price_dict.items():
            url = getUpdateTodayPriceURL(i, value)
            response = urllib3.PoolManager().request('GET', url)
            i+=1
        # set all tomorrow prices to 0
        for i in range(24, 48):
            url = getUpdateTomorrowPriceURL(i-24, 0)
            response = urllib3.PoolManager().request('GET', url)
            
    elif len(price_dict) >= 48:
        for key, value in price_dict.items():
            if i < 24:
                url = getUpdateTodayPriceURL(i, value)
                response = urllib3.PoolManager().request('GET', url)
                i+=1
            else:
                url = getUpdateTomorrowPriceURL(i-24, value)
                response = urllib3.PoolManager().request('GET', url)
                i+=1
                
    # Calculate the average price for the current day
    today_prices = list(price_dict.values())[:24]
    average_price = sum(today_prices) / len(today_prices)
    
    # Update the average price
    url = getUpdateAveragePriceURL(average_price)
    response = urllib3.PoolManager().request('GET', url)
