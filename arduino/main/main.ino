#include "arduino_secrets.h"
#include "wifi_connect.h"
#include "web_application.h"
#include "update_time.h"
#include "electricity_price.h"
#include <utility/wifi_drv.h>

WiFiClient client;

int current_hour = -1;
const unsigned long PERIOD_60S = 1UL*60UL*1000UL; // 1 minute
const unsigned long PERIOD_30S = 30UL*1000UL; // 30 seconds
unsigned long timer_price = 0;
unsigned long timer_state = 0;
bool val = 0;
bool lamp_on = 0;
bool start_timer = 0;

void setup() {
  // Open serial communications and wait for port to open:
  Serial.begin(9600);
  while (!Serial);
  Serial.println("start");
  connectWifi();

  WiFiDrv::pinMode(25, OUTPUT); //GREEN
  WiFiDrv::pinMode(26, OUTPUT); //RED
  WiFiDrv::pinMode(27, OUTPUT); //BLUE
  WiFiDrv::analogWrite(25, 0);
}

void loop() {
  if (WiFi.status() != WL_CONNECTED){
    resetFunc();
  }

  String request = updatePrice();

  unsigned long current_time = millis();
  if ((unsigned long)(current_time - timer_state) >= PERIOD_30S) { // run every 10 sec
    timer_state = current_time;

    val = makeRequest(client, "b1", request);
    if (val && !lamp_on){
      Serial.println("on");
      WiFiDrv::analogWrite(25, 255);
      val = makeRequest(client, "", "&un=2&b1=1");
      lamp_on = 1;
    }
    else if (!val && lamp_on) {
      Serial.println("off");
      WiFiDrv::analogWrite(25, 0);
      val = makeRequest(client, "", "&un=2&b1=0");
      lamp_on = 0;
    }
  }
}

String updatePrice(){
  String request;
  if (start_timer) {
    unsigned long current_time = millis();
    if ((unsigned long)(current_time - timer_price) >= PERIOD_60S) { // run every 1 min
      timer_price = current_time;
      int hour = updateHour();
      if (current_hour != hour) {
        current_hour = hour;
        String url = getRequestAddress();
        double price = getCurrentPrice(client, url);
        request += String("&un=1&n1=" + String(price));
      }
    }
  }
  else {
    start_timer = 1;
    int hour = updateHour();
    current_hour = hour;
    String url = getRequestAddress();
    double price = getCurrentPrice(client, url);
    request += String("&un=1&n1=" + String(price));
  }
  return request;
}
