import urllib3

SERVER_ID_AND_PASS = {"99999": "2580", "12345": "0000"}

def getUpdateCurrentPriceURL(price):
    return "https://nutipistik.fun/TX.php?&id=99999&pw=2580&un=1&n1=" + str(price)

def getUpdateAveragePriceURL(price):
    return "https://nutipistik.fun/TX.php?&id=99999&pw=2580&un=2&n2=" + str(price)

def getUpdateTodayPriceURL(hour, price):
    return "https://nutipistik.fun/TX.php?&id=99999&pw=2580&td=" + str(hour) + "&val=" + str(price)

def getUpdateTomorrowPriceURL(hour, price):
    return "https://nutipistik.fun/TX.php?&id=99999&pw=2580&tm=" + str(hour) + "&val=" + str(price)

def getServerValueURL(id=99999, password=2580):
    return f"https://nutipistik.fun/TX.php?id={id}&pw={password}"

def getUpdateOutputURL(output):
    return "https://nutipistik.fun/TX.php?id=99999&pw=2580&out=" + str(output)

def custom_split(response):
    pairs = []
    temp = ''
    inside_brackets = False

    for char in response:
        if char == '[':
            inside_brackets = True
        elif char == ']':
            inside_brackets = False

        if char == ',' and not inside_brackets:
            pairs.append(temp)
            temp = ''
        else:
            temp += char
    pairs.append(temp)
    return pairs

def getServerValues(url):
    response = urllib3.PoolManager().request('GET', url)

    values = {}

    response = response.data.decode('utf-8').strip()
    response_pairs = custom_split(response)
    for pair in response_pairs:
        # each pair is in the form of "key:value", e.g. "control_type:0"
        # split the pair into key and value, and add them to the values dict
        key, value = pair.split(":", 1)  # Add the maxsplit parameter here
        values[key] = value
        
    # if values[region] = 1, then replace it with "ee"
    # 2 = "fi", 3 = "lv", 4 = "lt"
    regions = {1: "ee", 2: "fi", 3: "lv", 4: "lt"}
    values["region"] = regions[int(values["region"])]
        
    return values
