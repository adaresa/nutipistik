#include <Arduino.h>
#include <ESP8266WiFi.h>
#include <WiFiManager.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClientSecureBearSSL.h>

// Constants
constexpr uint32_t RECONNECT_TIMEOUT = 30000; // Time in milliseconds to wait before opening access point
constexpr char CONFIG_SSID[] = "Nutipistik";  // SSID for configuration portal
constexpr char REQUEST_URL[] = "https://nutipistik.fun/RX.php?id=99999"; // URL for HTTP GET request

// Pin definitions
constexpr uint8_t RELAY_PIN = 4;

// Variables
uint32_t lastConnectAttempt = 0;

/**
 * Connect to WiFi or start configuration portal if not connected.
 */
void connectToWiFi() {
  WiFiManager wifiManager;

  // Set timeout until configuration portal gets turned off
  wifiManager.setConfigPortalTimeout(180);

  if (!wifiManager.autoConnect(CONFIG_SSID)) {
    Serial.println("Failed to connect and hit timeout");
    delay(3000);
    ESP.reset();
    delay(5000);
  }

  // Connected
  Serial.println("WiFi connected");
}

void setup() {
  Serial.begin(115200);

  connectToWiFi();

  pinMode(RELAY_PIN, OUTPUT);
}

void loop() {
  // Check if WiFi is connected
  if (WiFi.status() != WL_CONNECTED) {
    uint32_t now = millis();

    if (now - lastConnectAttempt > RECONNECT_TIMEOUT) {
      lastConnectAttempt = now;

      connectToWiFi();
    }

    return;
  }

  // Make HTTP GET request to server
  HTTPClient http;
  BearSSL::WiFiClientSecure client;

  client.setInsecure();
  http.begin(client, REQUEST_URL);
  int httpCode = http.GET();

  if (httpCode == HTTP_CODE_OK) {
    String response = http.getString();

    if (response == "#1") {
      // Turn relay ON
      digitalWrite(RELAY_PIN, HIGH);
    } else if (response == "#0") {
      // Turn relay OFF
      digitalWrite(RELAY_PIN, LOW);
    }

    Serial.println(response);

    delay(2500);
  } else {
    // Log error message and wait before retrying
    Serial.printf("HTTP GET request failed with error code: %d\n", httpCode);
    delay(5000);
  }

  http.end();
}