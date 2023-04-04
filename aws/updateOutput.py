from serverSecret import *
from OutputFuncs import *

user_device_values_list = get_user_device_values()
    
for values in user_device_values_list:
    current_price, average_price = get_current_average_price(values["region"])
    
    if values["control_type"] == 1:  # Price limit
        print("Control type: Price Limit")
        current_price = current_price * (1 + float(values["vat"])/100)
        if values["energy_type"] == 'kWh':
            current_price = current_price / 1000
            current_price = round(current_price, 3)
        if PriceLimitOutput(current_price, float(values["price_limit"])):
            update_output_state(values['id'], 1)
        else:
            update_output_state(values['id'], 0)

    elif values["control_type"] == 2:  # Switch
        print("Control type: Manual")
        if SwitchOutput(values["button_state"]):
            update_output_state(values['id'], 1)
        else:
            update_output_state(values['id'], 0)

    elif values["control_type"] == 3:  # Cheapest hours
        print("Control type: Cheapest hours")
        if CheapestHoursOutput(int(values["cheapest_hours"]), current_price):
            update_output_state(values['id'], 1)
        else:
            update_output_state(values['id'], 0)

    elif values["control_type"] == 4:  # Selected hours
        print("Control type: Selected hours")
        # get current time in Tallinn timezone
        tz = pytz.timezone('Europe/Tallinn')
        hour = datetime.datetime.now(tz).hour
        current_state = values["selected_hours"][hour]

        if SwitchOutput(current_state):
            update_output_state(values['id'], 1)
        else:
            update_output_state(values['id'], 0)

    elif values["control_type"] == 5: # Smart Hours
        print("Control type: Smart Hours")
        average_price = average_price * (1 + float(values["vat"])/100)
        if values["energy_type"] == 'kWh':
            average_price = average_price / 1000
            average_price = round(average_price, 3)
        if SmartHoursOutput(int(values["chp_day_hours"]), int(values["exp_day_hours"]), float(values["chp_day_thold"]), float(values["exp_day_thold"]), current_price, average_price):
            update_output_state(values['id'], 1)
        else:
            update_output_state(values['id'], 0)
            
    elif values["control_type"] == 6: # Schedule
        print("Control type: Schedule")
        if ScheduleOutput(values["time_ranges"]):
            update_output_state(values['id'], 1)
        else:
            update_output_state(values['id'], 0)
            
close_connection()
