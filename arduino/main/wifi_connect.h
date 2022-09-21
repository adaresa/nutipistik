/*
Connects Arduino Rev2 to WiFi
*/


#include <WiFiNINA.h>
#include <SPI.h>

void connectWifi() {
  char ssid[] = SECRET_SSID;        // your network SSID (name)
  char pass[] = SECRET_PASS;    // your network password (use for WPA, or use as key for WEP)

  int status = WL_IDLE_STATUS;
  while (status != WL_CONNECTED) {
    status = WiFi.begin(ssid, pass);
    delay(5000);
  }
  Serial.println("Connected to wifi");
  digitalWrite(25, HIGH);
}