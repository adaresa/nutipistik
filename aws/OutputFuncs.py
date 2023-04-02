import datetime
import json
import urllib3
import pytz
    
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
    
def fetch_prices():
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

    price_dict = {}
    # Get all from "data", "ee" in the json
    for i in data["data"]["ee"]:
        # Populate price_dict with "timestamp": "price"
        price_dict[i["timestamp"]] = i["price"]

    return price_dict

def CheapestHoursOutput(cheapest_hours, current_price): # If current time is in cheapest hours 
    if cheapest_hours == 0:
        return False
    
    price_dict = fetch_prices()

    # from price_dict, set the NUM_OF_CHEAPEST_HOURS'th cheapest hour as the price_limit
    price_limit = sorted(price_dict.values())[cheapest_hours-1]
    
    if current_price <= price_limit:
        return True
    else:
        return False
    
def SmartHoursOutput(chp_day_hours, exp_day_hours, chp_day_thold, exp_day_thold, current_price, average_price):
    price_dict = fetch_prices()

    current_day_average_price = average_price
    
    # Calculate the number of hours the plug run based on a linear interpolation
    if current_day_average_price <= chp_day_thold:
        threshold_hours = chp_day_hours
    elif current_day_average_price >= exp_day_thold:
        threshold_hours = exp_day_hours
    else:
        price_ratio = (current_day_average_price - chp_day_thold) / (exp_day_thold - chp_day_thold)
        threshold_hours = int(round(chp_day_hours + price_ratio * (exp_day_hours - chp_day_hours)))
        
    if threshold_hours == 0:
        return False
    
    # Sort the price_dict based on price values
    sorted_prices = sorted(price_dict.items(), key=lambda item: item[1])
    
    # Get the threshold price from the sorted prices
    threshold_price = sorted_prices[threshold_hours-1][1]
    
    # print threshold_hours number of prices in sorted_prices, and also timestamp. Convert timestamp to local time
    #for i in range(threshold_hours-1):
    #    a = sorted_prices[i][1]
    #    b= datetime.datetime.fromtimestamp(sorted_prices[i][0], tz=pytz.timezone('Europe/Tallinn')).strftime("%H:%M")
    #    print(f'{b}: {a}')
    
    # Check if the current price is less than or equal to the threshold price
    if current_price <= threshold_price:
        return True
    else:
        return False

def ScheduleOutput(schedule):
    schedule_list = json.loads(schedule)
    estonia_tz = pytz.timezone('Europe/Tallinn')
    current_time = datetime.datetime.now(estonia_tz)

    for time_range in schedule_list:
        start_time = pytz.timezone('Europe/Tallinn').localize(datetime.datetime.strptime(time_range["start"], "%Y-%m-%d %H:%M:%S"))
        end_time = pytz.timezone('Europe/Tallinn').localize(datetime.datetime.strptime(time_range["end"], "%Y-%m-%d %H:%M:%S"))
        if start_time <= current_time <= end_time + datetime.timedelta(minutes=1):
            return True

    return False
