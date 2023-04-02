import urllib3
import json
from serverSecret import *

def lambda_handler(event, context):
    # update today and tomorrow prices for all regions
    data = get_today_tomorrow_prices()

    regions = ("ee", "fi", "lv", "lt")
    for region in regions:
        print("Updating prices for region " + region)
        price_dict = {}
        for i in data.get(region, []):
            # populate price_dict with "timestamp": "price"
            price_dict[i["timestamp"]] = i["price"]

        # sort price_dict by key
        price_dict = dict(sorted(price_dict.items()))


        # to find current_price, make request to https://dashboard.elering.ee/api/nps/price/'values[region]'/current
        url = "https://dashboard.elering.ee/api/nps/price/" + region + "/current"
        response = urllib3.PoolManager().request('GET', url)
        current_price_data = json.loads(response.data.decode('utf-8'))
        current_price = current_price_data["data"][0]["price"]
        # make POST request to https://nutipistik.fun/
        update_current_price(current_price, region)

        prices = {}
        # for each hour in price_dict, save it to 'prices' as "hour": "price", e.g. {"td0": 0.1, "td1": 0.2, ..., "tm0": 0.1, "tm1": 0.2, ...}
        i = 0
        if len(price_dict) == 24:
            for key, value in price_dict.items():
                prices["td" + str(i)] = value
                i+=1
            # set all tomorrow prices to 0
            for i in range(24, 48):
                prices["tm" + str(i-24)] = 0
                
        elif len(price_dict) >= 48:
            for key, value in price_dict.items():
                if i < 24:
                    prices["td" + str(i)] = value
                    i+=1
                else:
                    prices["tm" + str(i-24)] = value
                    i+=1
                    
        update_electricity_prices(prices, region)
                    
        # Calculate the average price for the current day
        today_prices = list(price_dict.values())[:24]
        average_price = sum(today_prices) / len(today_prices)

        # Update the average price
        update_average_price(average_price, region)
