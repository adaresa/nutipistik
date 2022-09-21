#include "arduino_secrets.h"
#include "wifi_connect.h"
#include "web_application.h"
#include "update_time.h"
#include "electricity_price.h"
#include <utility/wifi_drv.h>


WiFiClient client;
WiFiUDP Udp;

const unsigned long PERIOD = 10UL*60UL*1000UL;
unsigned long previous_time = 0;
int val;
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
}

void loop() {
  if (WiFi.status() != WL_CONNECTED){
    Serial.println("reconnecting wifi");
    digitalWrite(25, LOW);
    connectWifi();
  }
  String request;

  if (start_timer) {
    unsigned long current_time = millis();
    if ((unsigned long)(current_time - previous_time) >= PERIOD) {
      Serial.println("GO");
      previous_time = current_time;
      request = updatePrice();
    }
  }
  else {
    start_timer = 1;
    request = updatePrice();
    Serial.println("timer go");
  }

  val = makeRequest(client, "b1", request);
  if (val && !lamp_on){
    Serial.println("on");
    val = makeRequest(client, "", "&un=2&b1=1");
    WiFiDrv::analogWrite(25, 255);
    lamp_on = 1;
  }
  else if (!val && lamp_on) {
    Serial.println("off");
    WiFiDrv::analogWrite(25, 0);
    val = makeRequest(client, "", "&un=2&b1=0");
    lamp_on = 0;
  }
}

String updatePrice(){
  String url = getRequestAddress();
  double price = getCurrentPrice(client, url);
  return String("&un=1&n1=" + String(price));
}

