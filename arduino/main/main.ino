#include "arduino_secrets.h"
#include "wifi_connect.h"
#include "web_application.h"
#include "update_time.h"
#include "electricity_price.h"
#include <utility/wifi_drv.h>

WiFiClient client;

const unsigned long PERIOD_30S = 10UL*1000UL; // 10 seconds
unsigned long timer_state = 0;
bool start_timer = 0;

void setup() {
  // Open serial communications and wait for port to open:
  Serial.begin(9600);
  while (!Serial);
  Serial.println("start");
  connectWifi();

  WiFiDrv::pinMode(25, OUTPUT); //GREEN
  WiFiDrv::analogWrite(25, 0);
  makeRequest(client);
}

void loop() {
  if (WiFi.status() != WL_CONNECTED){
    resetFunc();
  }

  unsigned long current_time = millis();
  if ((unsigned long)(current_time - timer_state) >= PERIOD_30S) { // run every 30 sec
    timer_state = current_time;

    // Working based on price limit
    if (values[0] == "1") {
      double price_limit = values[2].toDouble();
      double price_current = values[3].toDouble();
      if (price_current < price_limit) {
        WiFiDrv::analogWrite(25, 255);
        makeRequest(client, "&un=2&b1=1");
      }
      else {
        WiFiDrv::analogWrite(25, 0);
        makeRequest(client, "&un=2&b1=0");
      }
    }
    
    // Working as a switch
    if (values[0] == "2") {
      // if values[1] == "1" then turn on lamp, else turn off lamp
      if (values[1] == "1") {
        WiFiDrv::analogWrite(25, 255);
        makeRequest(client, "&un=2&b1=1");
      }
      else {
        WiFiDrv::analogWrite(25, 0);
        makeRequest(client, "&un=2&b1=0");
      }
    }
  }
}
