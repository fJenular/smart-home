#include <WiFi.h>
#include <PubSubClient.h>
#include <ArduinoJson.h>
#include <DHTesp.h>
#include <ESP32Servo.h>

// =====================
// PIN CONFIGURATION
// =====================

#define DHT_PIN     15
#define PIR_PIN     12  // Disesuaikan dengan diagram.json Wokwi (sebelumnya 14)
#define LDR_PIN     34
#define LED_PIN      2
#define SERVO_PIN   13  // Disesuaikan dengan diagram.json Wokwi (sebelumnya 18)
#define FAN_PIN      4
#define ALARM_PIN   14

// =====================
// WIFI
// =====================

const char* ssid = "Wokwi-GUEST";
const char* password = "";

// =====================
// SHIFTR.IO MQTT
// =====================

const char* mqtt_server = "jenul.cloud.shiftr.io";
const int mqtt_port = 1883;

const char* mqtt_user = "jenul";
const char* mqtt_password = "jenul";

// =====================
// MQTT TOPICS
// =====================

const char* TEMP_TOPIC = "home/temperature";
const char* HUM_TOPIC = "home/humidity";
const char* MOTION_TOPIC = "home/motion";
const char* LIGHT_TOPIC = "home/light";

const char* LAMP_CONTROL = "home/control/lamp";
const char* DOOR_CONTROL = "home/control/door";

const char* LAMP_STATUS = "home/status/lamp";
const char* DOOR_STATUS = "home/status/door";

// =====================
// OBJECTS
// =====================

WiFiClient espClient;
PubSubClient client(espClient);

DHTesp dht;
Servo doorServo;

// =====================
// VARIABLES
// =====================

unsigned long lastPublish = 0;
const unsigned long publishInterval = 5000;

bool lampStatus = false;
bool doorStatus = false;
bool fanStatus = false;
bool alarmStatus = false;

// =====================
// WIFI CONNECT
// =====================

void setupWifi() {

  Serial.println();
  Serial.println("Connecting WiFi...");

  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println();
  Serial.println("WiFi Connected");
  Serial.print("IP : ");
  Serial.println(WiFi.localIP());
}

// =====================
// PUBLISH STATUS
// =====================

void publishLampStatus() {

  StaticJsonDocument<128> doc;

  doc["device"] = "lamp";
  doc["status"] = lampStatus ? "ON" : "OFF";

  char payload[128];

  serializeJson(doc, payload);

  client.publish(LAMP_STATUS, payload, true);
}

void publishDoorStatus() {

  StaticJsonDocument<128> doc;

  doc["device"] = "door";
  doc["status"] = doorStatus ? "OPEN" : "CLOSE";

  char payload[128];

  serializeJson(doc, payload);

  client.publish(DOOR_STATUS, payload, true);
}

void publishFanStatus() {

  StaticJsonDocument<128> doc;

  doc["device"] = "fan";
  doc["status"] = fanStatus ? "ON" : "OFF";

  char payload[128];

  serializeJson(doc, payload);

  client.publish("home/status/fan", payload, true);
}

void publishAlarmStatus() {

  StaticJsonDocument<128> doc;

  doc["device"] = "alarm";
  doc["status"] = alarmStatus ? "ON" : "OFF";

  char payload[128];

  serializeJson(doc, payload);

  client.publish("home/status/alarm", payload, true);
}

// =====================
// MQTT CALLBACK
// =====================

void callback(char* topic, byte* payload, unsigned int length) {

  String message = "";

  for (int i = 0; i < length; i++) {
    message += (char)payload[i];
  }

  Serial.println("=================================");
  Serial.print("Topic : ");
  Serial.println(topic);

  Serial.print("Payload : ");
  Serial.println(message);

  StaticJsonDocument<128> doc;

  DeserializationError error =
    deserializeJson(doc, message);

  if (error) {
    Serial.println("Invalid JSON");
    return;
  }

  String status = doc["status"];

  // =====================
  // LAMP CONTROL
  // =====================

  if (String(topic) == LAMP_CONTROL) {

    if (status == "ON") {

      digitalWrite(LED_PIN, HIGH);
      lampStatus = true;

      Serial.println("Lamp ON");

    } else {

      digitalWrite(LED_PIN, LOW);
      lampStatus = false;

      Serial.println("Lamp OFF");
    }

    publishLampStatus();
  }

  // =====================
  // DOOR CONTROL
  // =====================

  if (String(topic) == DOOR_CONTROL) {

    if (status == "OPEN") {

      doorServo.write(90);
      doorStatus = true;

      Serial.println("Door OPEN");

    } else {

      doorServo.write(0);
      doorStatus = false;

      Serial.println("Door CLOSE");
    }

    publishDoorStatus();
  }

  // =====================
  // FAN CONTROL
  // =====================

  if (String(topic) == "home/control/fan") {

    if (status == "ON") {

      digitalWrite(FAN_PIN, HIGH);
      fanStatus = true;

      Serial.println("Fan ON");

    } else {

      digitalWrite(FAN_PIN, LOW);
      fanStatus = false;

      Serial.println("Fan OFF");
    }

    publishFanStatus();
  }

  // =====================
  // ALARM CONTROL
  // =====================

  if (String(topic) == "home/control/alarm") {

    if (status == "ON") {

      digitalWrite(ALARM_PIN, HIGH);
      alarmStatus = true;

      Serial.println("Alarm ON");

    } else {

      digitalWrite(ALARM_PIN, LOW);
      alarmStatus = false;

      Serial.println("Alarm OFF");
    }

    publishAlarmStatus();
  }
}

// =====================
// MQTT CONNECT
// =====================

void reconnectMQTT() {

  while (!client.connected()) {

    Serial.println();
    Serial.println("Connecting MQTT...");

    String clientId =
      "ESP32-" + String(random(1000, 9999));

    bool connected =
      client.connect(
        clientId.c_str(),
        mqtt_user,
        mqtt_password
      );

    if (connected) {

      Serial.println("MQTT Connected!");

      client.subscribe(LAMP_CONTROL);
      client.subscribe(DOOR_CONTROL);
      client.subscribe("home/control/fan");
      client.subscribe("home/control/alarm");

      Serial.println("Subscribed:");
      Serial.println(LAMP_CONTROL);
      Serial.println(DOOR_CONTROL);
      Serial.println("home/control/fan");
      Serial.println("home/control/alarm");

    } else {

      Serial.print("MQTT Failed, rc=");
      Serial.println(client.state());

      delay(3000);
    }
  }
}

// =====================
// PUBLISH SENSORS
// =====================

void publishSensors() {

  TempAndHumidity data =
    dht.getTempAndHumidity();

  float temp = data.temperature;
  float hum = data.humidity;

  int lightValue =
    analogRead(LDR_PIN);

  int lightPercent = map(lightValue, 0, 4095, 0, 100);
  if (lightPercent > 100) lightPercent = 100;
  if (lightPercent < 0) lightPercent = 0;

  int motion =
    digitalRead(PIR_PIN);

  StaticJsonDocument<128> doc;

  char payload[128];

  // TEMPERATURE

  doc.clear();
  doc["value"] = temp;
  doc["unit"] = "C";

  serializeJson(doc, payload);

  client.publish(TEMP_TOPIC, payload);
  delay(100);

  // HUMIDITY

  doc.clear();
  doc["value"] = hum;
  doc["unit"] = "%";

  serializeJson(doc, payload);

  client.publish(HUM_TOPIC, payload);
  delay(100);

  // MOTION

  doc.clear();
  doc["motion"] = motion;

  serializeJson(doc, payload);

  client.publish(MOTION_TOPIC, payload);
  delay(100);

  // LIGHT

  doc.clear();
  doc["value"] = lightPercent;

  serializeJson(doc, payload);

  client.publish(LIGHT_TOPIC, payload);
  delay(100);

  Serial.println();
  Serial.println("===== SENSOR UPDATE =====");

  Serial.print("Temp : ");
  Serial.println(temp);

  Serial.print("Humidity : ");
  Serial.println(hum);

  Serial.print("Motion : ");
  Serial.println(motion);

  Serial.print("Light : ");
  Serial.println(lightValue);
}

// =====================
// SETUP
// =====================

void setup() {

  Serial.begin(115200);

  pinMode(LED_PIN, OUTPUT);
  pinMode(FAN_PIN, OUTPUT);
  pinMode(ALARM_PIN, OUTPUT);

  digitalWrite(FAN_PIN, LOW);
  digitalWrite(ALARM_PIN, LOW);

  pinMode(PIR_PIN, INPUT);

  dht.setup(DHT_PIN, DHTesp::DHT22);

  doorServo.attach(SERVO_PIN);

  doorServo.write(0);

  setupWifi();

  client.setServer(
    mqtt_server,
    mqtt_port
  );

  client.setCallback(callback);
}

// =====================
// LOOP
// =====================

void loop() {

  if (!client.connected()) {
    reconnectMQTT();
  }

  client.loop();

  if (
    millis() - lastPublish >
    publishInterval
  ) {

    lastPublish = millis();

    publishSensors();
  }
}
