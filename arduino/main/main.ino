#include "arduino_secrets.h"
#include "wifi_connect.h"
#include "web_application.h"
#include "update_time.h"
#include "electricity_price.h"
#include <utility/wifi_drv.h>

WiFiClient client;

const unsigned long PERIOD_30S = 30UL*1000UL; // 30 seconds
unsigned long timer_state = 0;
bool start_timer = 0;

char current_state = '0';

void setup() {
  // Open serial communications and wait for port to open:
  Serial.begin(9600);
  while (!Serial);
  Serial.println("start");
  connectWifi();

  WiFiDrv::pinMode(25, OUTPUT); //GREEN
  WiFiDrv::analogWrite(25, 0);
}

void loop() {
  if (WiFi.status() != WL_CONNECTED){
    resetFunc();
  }

  unsigned long current_time = millis();
  if ((unsigned long)(current_time - timer_state) >= PERIOD_30S) { // run every 30 sec
    timer_state = current_time;

    char output = makeRequest(client);

    if(current_state == '0' && output == '1'){
      Serial.println("on");
      current_state = '1';
      WiFiDrv::analogWrite(25, 255);
      makeRequest(client, "&arduino=1");
    }
    else if(current_state == '1' && output == '0'){
      Serial.println("off");
      current_state = '0';
      WiFiDrv::analogWrite(25, 0);
      makeRequest(client, "&arduino=0");
    }

  }
}
