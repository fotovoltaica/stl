/*
  Repeating Web client
 
 This sketch connects to a a web server and makes a request
 using a Wiznet Ethernet shield. You can use the Arduino Ethernet shield, or
 the Adafruit Ethernet shield, either one will work, as long as it's got
 a Wiznet Ethernet module on board.
 
 This example uses DNS, by assigning the Ethernet client with a MAC address,
 IP address, and DNS address.
 
 Circuit:
 * Ethernet shield attached to pins 10, 11, 12, 13
 
 created 19 Apr 2012
 by Tom Igoe
 
 http://arduino.cc/en/Tutorial/WebClientRepeating
 This code is in the public domain.
 
 */

#include <SPI.h>
#include <Ethernet.h>
#include <OneWire.h> 
#include <dht.h>
#include <DallasTemperature.h>


dht DHT;

#define DHT11_PIN 2
#define ONE_WIRE_BUS_1 5        //Bus 1 en Pin 5

OneWire oneWire_1(ONE_WIRE_BUS_1);
DallasTemperature sensores_1(&oneWire_1);

//float t_int_1[1]; // variable donde guardaremos la temperatura leida del sensor


int DS18S20_Pin = 5; //DS18S20 Signal pin on digital 5

//Temperature chip i/o
OneWire ds(DS18S20_Pin);  // on digital pin 5

// assign a MAC address for the ethernet controller.
// fill in your address here:
byte mac[] = { 
  0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED};
// fill in an available IP address on your network here,
// for manual configuration:
//IPAddress ip(192,168,1,220);
//IPAddress server(192,168,1,5); // Google
// fill in your Domain Name Server address here:
//IPAddress myDns(200,58,116,42);

// initialize the library instance:
EthernetClient client;

char server[] = "www.subtilis.com.ar";
//char server[] = "200.58.111.142";

unsigned long lastConnectionTime = 0;          // last time you connected to the server, in milliseconds
boolean lastConnected = false;                 // state of the connection last time through the main loop
const unsigned long postingInterval = 900000;  // delay between updates, in milliseconds -> 15 minutos

void setup() {
  // start serial port:
  Serial.begin(9600);
  // give the ethernet module time to boot up:
  delay(1000);
  // start the Ethernet connection using a fixed IP address and DNS server:
  Ethernet.begin(mac);
//  Ethernet.begin(mac, ip, myDns);

  // print the Ethernet board/shield's IP address:
//  Serial.print("My IP address: ");
//  Serial.println(Ethernet.localIP());
//  Serial.println(server);


}

void loop() {
  // if there's incoming data from the net connection.
  // send it out the serial port.  This is for debugging
  // purposes only:
  if (client.available()) {
    char c = client.read();

  }

  // if there's no net connection, but there was one last time
  // through the loop, then stop the client:
  if (!client.connected() && lastConnected) {
    Serial.println();
    Serial.println("disconnecting.");
    client.stop();
  }
  // if you're not connected, and ten seconds have passed since
  // your last connection, then connect again and send data:
  if(!client.connected() && ((millis() - lastConnectionTime) > postingInterval)) {

    httpRequest();
  }
  // store the state of the connection for next time through
  // the loop:
  lastConnected = client.connected();
}

// this method makes a HTTP connection to the server:
void httpRequest() {
  // if there's a successful connection:
  if (client.connect(server, 80)) {
    Serial.println("connecting...");
    // send the HTTP PUT request:
//    float val = getTemp();
//    String temp[5] = (char) getTemp();

      int chk = DHT.read11(DHT11_PIN);
 /*       client.println(DHT.temperature,1);
          Serial.println(DHT.temperature,1);
          client.println(DHT.humidity,1);
        
   */
    int t_int = (int) getTemp(); //temperatura interior DS18B20
    Serial.println(t_int, DEC);
    int t_ext = (int)(DHT.temperature); //temperatura exterior DHT11
     // int t_ext = (int)(DHT.temperature); //temperatura exterior DHT11

    Serial.println(t_ext);
    int h_ext = (int)(DHT.humidity); //humedad relativa exterior DHT11
    Serial.println(h_ext);
    
    String cadena_1 = "GET http://www.subtilis.com.ar/arduino.php?id=1&tInt=";
    String cadena_2 = "&tExt=";
    String cadena_3 = "&hExt=";
    String cadena_4 = " HTTP/1.0";
    String cadena = cadena_1 + t_int + cadena_2 + t_ext + cadena_3 + h_ext + cadena_4 ;
    Serial.println(cadena);
    client.println(cadena);
    client.println();
    char c = client.read();


    // note the time that the connection was made:
    lastConnectionTime = millis();
  
  } 
  else {
    // if you couldn't make a connection:
    Serial.println("connection failed");
    Serial.println("disconnecting.");
    client.stop();
  }
}

float getTemp(){
  //returns the temperature from one DS18S20 in DEG Celsius

  byte data[12];
  byte addr[8];

  if ( !ds.search(addr)) {
      //no more sensors on chain, reset search
      ds.reset_search();
      return -1000;
  }

  if ( OneWire::crc8( addr, 7) != addr[7]) {
      Serial.println("CRC is not valid!");
      return -1000;
  }

  if ( addr[0] != 0x10 && addr[0] != 0x28) {
      Serial.print("Device is not recognized");
      return -1000;
  }

  ds.reset();
  ds.select(addr);
  ds.write(0x44,1); // start conversion, with parasite power on at the end

  byte present = ds.reset();
  ds.select(addr);    
  ds.write(0xBE); // Read Scratchpad

  
  for (int i = 0; i < 9; i++) { // we need 9 bytes
    data[i] = ds.read();
  }
  
  ds.reset_search();
  
  byte MSB = data[1];
  byte LSB = data[0];

  float tempRead = ((MSB << 8) | LSB); //using two's compliment
  float TemperatureSum = tempRead / 16;
  
  return TemperatureSum;
  
}
