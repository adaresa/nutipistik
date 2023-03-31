import urllib3
import json
import datetime
from serverSecret import *
import dateutil.tz
import pytz

def lambda_handler(event, context):
    # Get the current date in Estonia and set the time to 00:00
    est_tz = pytz.timezone('Europe/Tallinn')
    est_today = datetime.datetime.now(est_tz).date()
    est_midnight = est_tz.localize(datetime.datetime.combine(est_today, datetime.time.min))

    # Determine the UTC offset for Estonia
    summer_time = bool(pytz.country_timezones['ee'][-1])
    if summer_time:
        utc_offset = datetime.timedelta(hours=3)
    else:
        utc_offset = datetime.timedelta(hours=2)

    # Calculate the start and end times in UTC
    start = est_midnight - utc_offset
    end = est_midnight + datetime.timedelta(days=1) - datetime.timedelta(milliseconds=1) - utc_offset

    start_str = start.strftime("%Y-%m-%dT%H:%M:%S.") + start.strftime("%f")[:3] + "Z"
    end_str = end.strftime("%Y-%m-%dT%H:%M:%S.") + end.strftime("%f")[:3] + "Z"

    # Make request to https://dashboard.elering.ee/api/nps/price?
    url = f"https://dashboard.elering.ee/api/nps/price?start={start_str}&end={end_str}"
    response = urllib3.PoolManager().request('GET', url)
    data = json.loads(response.data.decode('utf-8'))

    # Check if data for the next day exists
    next_day_start = (end + datetime.timedelta(milliseconds=1)).strftime("%Y-%m-%dT%H:%M:%S.%f")[:-3] + "Z"
    next_day_end_temp = (end + datetime.timedelta(days=1)).replace(hour=20, minute=59, second=59, microsecond=999)
    next_day_end = next_day_end_temp.strftime("%Y-%m-%dT%H:%M:%S.%f")[:-6] + "999Z"
    next_day_url = f"https://dashboard.elering.ee/api/nps/price?start={next_day_start}&end={next_day_end}"
    next_day_response = urllib3.PoolManager().request('GET', next_day_url)
    next_day_data = json.loads(next_day_response.data.decode('utf-8'))

    # Get the selected region
    url = getServerValueURL()

    # Get the values from the server
    values = getServerValues(url)

    if next_day_data:
        # If data for the next day exists, append it to the existing data
        data['data'][values['region']] += next_day_data['data'][values['region']]
        

    # print(url)
    price_dict = {}

    # get all from "data", values[region] in the json
    for i in data["data"][values["region"]]:
        # populate price_dict with "timestamp": "price"
        price_dict[i["timestamp"]] = i["price"]

    # sort price_dict by key
    price_dict = dict(sorted(price_dict.items()))


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
