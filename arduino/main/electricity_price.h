#include <ArduinoJson.h>

char server[] = "dashboard.elering.ee";

double getCurrentPrice(WiFiClient client, String serverPath){
  String response;
  if (client.connectSSL(server, 443)) {
    // Make a HTTP request:
    client.print("GET ");
    client.println(serverPath);
    client.println();
  }
  int timeout = 0;
  while (!client.available()){
    delay(1);
    timeout++;
    if(timeout>10000) {
      Serial.println("Failed connection");
      WiFi.disconnect();
      connectWifi();
      break;
      }
  }
  while (client.available()) {
    char c = client.read();
    response += c;
  }
  if (!client.connected()) {
    client.flush();
    client.stop();
  }

  char* c_response = response.c_str();
  StaticJsonDocument<2500> doc;
  DeserializationError err = deserializeJson(doc, c_response);
  if (err) {
    Serial.println(err.c_str());
  }
  double data = doc["data"]["ee"][0]["price"];
  // Serial.println(double);
  return data;
}



