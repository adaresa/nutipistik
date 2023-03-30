import pandas as pd
import requests
import matplotlib.pyplot as plt
import matplotlib.dates as mdates
import numpy as np

# Fetch data from the Elering API
start = "2022-03-01T00%3A00%3A00.000Z"
end = "2023-02-28T23%3A59%3A59.999Z"
url = f"https://dashboard.elering.ee/api/nps/price?start={start}&end={end}"
response = requests.get(url)

if response.status_code == 200:
    data = response.json()["data"]["ee"]
else:
    raise Exception(f"Failed to fetch data from Elering API. Status code: {response.status_code}")

# Convert data to a DataFrame
df = pd.DataFrame(data)
df["timestamp"] = pd.to_datetime(df["timestamp"], unit="s")
df["date"] = df["timestamp"].dt.date

# Convert fixed price from 15 eurocents/kWh to EUR/MWh (METHOD 1)
fixed_price = 15 * 10

# Calculate the average stock market electricity price for each separate day (METHOD 2)
daily_average_market_prices = df.groupby("date")["price"].mean()

# Calculate the cost using the 4 cheapest hours of each day (METHOD 3)
cheapest_hours_df = df.groupby("date", as_index=False).apply(lambda x: x.nsmallest(4, "price"))
cheapest_hours_daily_avg = cheapest_hours_df.groupby("date")["price"].mean()

# Calculate daily costs in EUR for consuming electricity for 4 hours every day, at 10 kW per hour (SCENARIO)
daily_consumption = 4  # in hours
daily_power_consumption_kw = 10  # in kilowatts (kW)
daily_energy_consumption_kwh = daily_power_consumption_kw * daily_consumption  # in kilowatt-hours (kWh)

# Calculate daily costs
fixed_daily_cost = fixed_price * daily_energy_consumption_kwh / 1000
average_market_daily_cost = daily_average_market_prices * daily_energy_consumption_kwh / 1000
cheapest_hours_daily_cost = cheapest_hours_daily_avg * daily_energy_consumption_kwh / 1000

# Calculate cumulative costs
fixed_cumulative_cost = np.cumsum(np.repeat(fixed_daily_cost, len(average_market_daily_cost)))
average_market_cumulative_cost = np.cumsum(average_market_daily_cost)
cheapest_hours_cumulative_cost = np.cumsum(cheapest_hours_daily_cost)

# CREATE A LINE GRAPH

# Set up the line graph
dates = daily_average_market_prices.index
plt.plot(dates, fixed_cumulative_cost, label="Fikseeritud hind (15 eurosenti/kWh)", linewidth=2)
plt.plot(dates, average_market_cumulative_cost, label="Päeva keskmine turuhind", linewidth=2)
plt.plot(dates, cheapest_hours_cumulative_cost, label="Päeva 4 odavamat tundi", linewidth=2)

# Add labels, title and legend
plt.ylabel("Summaarne kulu (EUR)", fontsize=14)
plt.xlabel("Kuupäev", fontsize=14)
plt.title("Eesti elektrikulude võrdlus (2022-03-01 kuni 2023-02-28)", fontsize=16)
plt.legend(fontsize=12)

# Configure x-axis ticks
ax = plt.gca()
ax.xaxis.set_major_locator(mdates.MonthLocator(interval=1))
ax.xaxis.set_major_formatter(mdates.DateFormatter('%Y-%m'))
plt.xticks(rotation=45, fontsize=12)
plt.yticks(fontsize=12)

# Add gridlines
plt.grid()

# Add labels for last data points
plt.annotate("{:.2f} EUR".format(fixed_cumulative_cost[-1]), xy=(dates[-1], fixed_cumulative_cost[-1]), xytext=(-60, 8), textcoords='offset points', fontsize=14)
plt.annotate("{:.2f} EUR".format(average_market_cumulative_cost[-1]), xy=(dates[-1], average_market_cumulative_cost[-1]), xytext=(-60, 8), textcoords='offset points', fontsize=14)
plt.annotate("{:.2f} EUR".format(cheapest_hours_cumulative_cost[-1]), xy=(dates[-1], cheapest_hours_cumulative_cost[-1]), xytext=(-60, 8), textcoords='offset points', fontsize=14)

# Save it as "electricity_costs.png"
plt.savefig("electricity_costs.png", dpi=300)

# Display the graph
plt.show()
