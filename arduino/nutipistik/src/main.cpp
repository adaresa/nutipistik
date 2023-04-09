#include <Arduino.h>
#include <ESP8266WiFi.h>
#include <WiFiManager.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClientSecureBearSSL.h>
#include "secrets.h"

// Uncomment the following line to enable debug prints
// #define DEBUG

#ifdef DEBUG
#define DEBUG_PRINT(...) Serial.print(__VA_ARGS__)
#define DEBUG_PRINTLN(...) Serial.println(__VA_ARGS__)
#else
#define DEBUG_PRINT(...)
#define DEBUG_PRINTLN(...)
#endif

// Constants
constexpr uint32_t RECONNECT_TIMEOUT = 5000; // Time in milliseconds to wait before attempting to reconnect
const char CONFIG_SSID[] = "Nutipistik";     // SSID for configuration portal

// Pin definitions
constexpr uint8_t RELAY_PIN = 4;
constexpr uint8_t BUTTON_PIN = 5;
constexpr uint8_t RGBLED_R_PIN = 16;
constexpr uint8_t RGBLED_G_PIN = 14;
constexpr uint8_t RGBLED_B_PIN = 12;
constexpr uint32_t DEBOUNCE_DELAY = 50; // Debounce delay in milliseconds

// Variables
uint32_t lastConnectAttempt = 0;
bool systemActive = true;
bool buttonHeld = false;
volatile bool lastButtonState = true;
volatile uint32_t lastButtonChangeTime = 0;
volatile uint32_t buttonPressTime = 0;
volatile bool buttonPressed = false;

String deviceIdText = "<p>Pistiku id: <b>" + String(DEVICE_ID) + "</b></p>";
WiFiManagerParameter deviceIdParam(deviceIdText.c_str());

String devicePasswordText = "<p>Pistiku Salasõna: <b>" + String(DEVICE_PASSWORD) + "</b></p>";
WiFiManagerParameter devicePasswordParam(devicePasswordText.c_str());

// Function prototypes
void connectToWiFi();
void controlLed(const bool red, const bool green, const bool blue);
void IRAM_ATTR handleButtonChange();
void checkRelayState();

// flag for saving data
bool shouldSaveConfig = false;

// callback notifying us of the need to save config
void saveConfigCallback()
{
  shouldSaveConfig = true;
}

// Connect to WiFi or keep trying to reconnect if not connected.
void connectToWiFi()
{
  WiFiManager wifiManager;
  wifiManager.addParameter(&deviceIdParam);
  wifiManager.addParameter(&devicePasswordParam);

  wifiManager.setSaveConfigCallback(saveConfigCallback);

  DEBUG_PRINTLN("Connecting to WiFi");

  // Check for existing credentials
  if (WiFi.status() == WL_CONNECTED)
  {
    DEBUG_PRINTLN("WiFi connected");
    return;
  }

  // Check if no credentials are saved
  String savedSSID = WiFi.SSID();
  if (savedSSID.length() == 0)
  {
    DEBUG_PRINTLN("No saved WiFi credentials");
    controlLed(false, false, true);
    if (wifiManager.startConfigPortal(CONFIG_SSID))
    {
      DEBUG_PRINTLN("Config portal closed");
    }
    return;
  }

  // Try to connect to saved credentials
  DEBUG_PRINTLN("Trying to connect to saved WiFi credentials");
  WiFi.begin(savedSSID.c_str(), WiFi.psk().c_str());

  // Wait for the connection to be established with a series of short delays
  unsigned long startTime = millis();
  unsigned long maxWaitTime = 10000;
  unsigned long delayInterval = 100;
  while (WiFi.status() != WL_CONNECTED && (millis() - startTime) < maxWaitTime)
  {
    delay(delayInterval);
  }
  // If connection is successful, blink LED 3 times to indicate successful connection
  if (WiFi.status() == WL_CONNECTED)
  {
    DEBUG_PRINTLN("WiFi connected");
    for (uint8_t i = 0; i < 3; i++)
    {
      controlLed(false, true, true);
      delay(200);
      controlLed(false, false, false);
      delay(200);
    }
  }

  lastConnectAttempt = millis();
}

void controlLed(const bool red, const bool green, const bool blue)
{
  digitalWrite(RGBLED_R_PIN, red ? LOW : HIGH);
  digitalWrite(RGBLED_G_PIN, green ? LOW : HIGH);
  digitalWrite(RGBLED_B_PIN, blue ? LOW : HIGH);
}

void IRAM_ATTR handleButtonChange()
{
  uint32_t currentTime = millis();

  if (currentTime - lastButtonChangeTime > DEBOUNCE_DELAY)
  {
    lastButtonChangeTime = currentTime;

    bool buttonState = digitalRead(BUTTON_PIN);
    if (buttonState == LOW)
    {
      // Button press
      buttonPressTime = currentTime;
      buttonPressed = true;
    }
    else
    {
      // Button release
      if (buttonPressed)
      {
        uint32_t pressDuration = currentTime - buttonPressTime;
        buttonPressed = false;

        if (pressDuration > 2000)
        {
          // Button held for more than 2 seconds
          DEBUG_PRINTLN("Button held");
          buttonHeld = true;
        }
        else
        {
          systemActive = !systemActive;
          DEBUG_PRINTLN("Button pressed");
          DEBUG_PRINTLN(systemActive ? "System active" : "System inactive");
        }
      }
    }
  }
}

void checkRelayState()
{
  // Make HTTP GET request to server
  HTTPClient http;
  BearSSL::WiFiClientSecure client;

  String requestUrl = String("https://nutipistik.fun/RX.php?id=") + String(DEVICE_ID) + String("&pw=") + String(DEVICE_PASSWORD);

  client.setInsecure();
  http.begin(client, requestUrl);

  int httpCode = http.GET();

  if (httpCode == HTTP_CODE_OK)
  {
    String response = http.getString();
    if (response == "#1")
    {
      // Turn relay ON
      digitalWrite(RELAY_PIN, HIGH);
      controlLed(false, true, false); // Set LED to green
    }
    else if (response == "#0")
    {
      // Turn relay OFF
      digitalWrite(RELAY_PIN, LOW);
      controlLed(true, false, false); // Set LED to red
    }
    else
    {
      // Invalid data, set LED to purple
      controlLed(true, false, true);
    }

    DEBUG_PRINTLN(response);
    delay(2500);
  }
  else
  {
    // Log error message and wait before retrying
    controlLed(true, false, true);
    DEBUG_PRINT("HTTP GET request failed with error code: ");
    DEBUG_PRINTLN(httpCode);
    delay(5000);
  }

  http.end();
}

void setup()
{
  Serial.begin(115200);

  attachInterrupt(digitalPinToInterrupt(BUTTON_PIN), handleButtonChange, CHANGE);

  pinMode(RELAY_PIN, OUTPUT);
  pinMode(BUTTON_PIN, INPUT_PULLUP);
  pinMode(RGBLED_R_PIN, OUTPUT);
  pinMode(RGBLED_G_PIN, OUTPUT);
  pinMode(RGBLED_B_PIN, OUTPUT);

  controlLed(false, true, true); // cyan - connecting to WiFi
  connectToWiFi();
}

void loop()
{
  if (!systemActive)
  {
    digitalWrite(RELAY_PIN, LOW);
    controlLed(false, false, false); // Turn off LED
    delay(1000);
    return;
  }

  if (buttonHeld)
  {
    buttonHeld = false;
    DEBUG_PRINTLN("Resetting WiFi credentials");
    WiFi.disconnect(true);
    WiFiManager wifiManager;
    wifiManager.resetSettings();
    controlLed(false, true, false); // Set LED to blue
    connectToWiFi();
  }

  if (WiFi.status() != WL_CONNECTED)
  {
    if (millis() - lastConnectAttempt > RECONNECT_TIMEOUT)
    {
      controlLed(false, true, true); // Set LED to cyan
      connectToWiFi();
    }
  }
  else
  {
    checkRelayState();
  }
}
