# Get start time in shape of 2022-10-14T00%3A00%3A00.000Z
# Get end time in shape of 2022-10-14T59%3A59%3A59.999Z

import urllib3
import json
import datetime

# Get start time in UTC
start = datetime.datetime.utcnow()
start = start.replace(hour=0, minute=0, second=0, microsecond=0)
start = start - datetime.timedelta(hours=2)
start = start.strftime("%Y-%m-%dT%H%%3A%M%%3A%S.000Z")
# Get end time in UTC
end = datetime.datetime.utcnow()
end = end + datetime.timedelta(days=1)
end = end.replace(hour=23, minute=59, second=59, microsecond=999)
end = end.strftime("%Y-%m-%dT%H%%3A%M%%3A%S.999Z")

# make request to https://dashboard.elering.ee/api/nps/price?
# for example: https://dashboard.elering.ee/api/nps/price?start=2022-10-14T00%3A00%3A00.000Z&end=2022-10-14T23%3A59%3A59.999Z

url = "https://dashboard.elering.ee/api/nps/price?start=" + start + "&end=" + end
response = urllib3.PoolManager().request('GET', url)
data = json.loads(response.data.decode('utf-8'))

# print(url)
price_dict = {}

# get all from "data", "ee" in the json
for i in data["data"]["ee"]:
    # populate price_dict with "timestamp": "price"
    price_dict[i["timestamp"]] = i["price"]

# sort price_dict by key
price_dict = dict(sorted(price_dict.items()))

# print price_dict
# i = 0
# for key, value in price_dict.items():
#     print(f"{i+1}. {key}, {value}")
#     i+=1
    
# get current time in unix timestamp, in GMT+3
current_time = datetime.datetime.utcnow()
current_time = current_time.replace(minute=0, second=0, microsecond=0)
current_time = current_time.timestamp()

current_price = price_dict[current_time]

# make POST request to https://nutipistik.fun/TX.php?&id=99999&pw=2580&un=2&b1={current_price}
# for example: https://nutipistik.fun/TX.php?&id=99999&pw=2580&un=2&b1=0.1
url = "https://nutipistik.fun/TX.php?&id=99999&pw=2580&un=1&n1=" + str(current_price)
response = urllib3.PoolManager().request('GET', url)

# for each hour in price_dict, make POST request to https://nutipistik.fun/TX.php?&id=99999&pw=2580&td={i}&val={current_price}
# for example: https://nutipistik.fun/TX.php?&id=99999&pw=2580&td=0&val=0.1
i = 0

# length of price_dict
# print(len(price_dict))

# if length of price_dict is 24, then make request for https://nutipistik.fun/TX.php?&id=99999&pw=2580&td={i}&val={current_price}
# if length of price_dict is 48, then make the last 24 requests for https://nutipistik.fun/TX.php?&id=99999&pw=2580&tm={i}&val={current_price}
if len(price_dict) == 24:
    for key, value in price_dict.items():
        url = "https://nutipistik.fun/TX.php?&id=99999&pw=2580&td=" + str(i) + "&val=" + str(value)
        response = urllib3.PoolManager().request('GET', url)
        i+=1
    # set all in  https://nutipistik.fun/TX.php?&id=99999&pw=2580&tm={i}&val=0
    for i in range(24, 48):
        url = "https://nutipistik.fun/TX.php?&id=99999&pw=2580&tm=" + str(i-24) + "&val=0"
        response = urllib3.PoolManager().request('GET', url)
        
elif len(price_dict) == 48:
    for key, value in price_dict.items():
        if i < 24:
            url = "https://nutipistik.fun/TX.php?&id=99999&pw=2580&td=" + str(i) + "&val=" + str(value)
            response = urllib3.PoolManager().request('GET', url)
            i+=1
        else:
            url = "https://nutipistik.fun/TX.php?&id=99999&pw=2580&tm=" + str(i-24) + "&val=" + str(value)
            response = urllib3.PoolManager().request('GET', url)
            i+=1