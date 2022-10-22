/* 
Communicates with the webserver
*/

char findVal(String txt){
  for(int i = 0; i < txt.length(); i++){
    if (txt[i] == '#') { 
      return txt[i+1];
    }
  }
}

char makeRequest(String field="") {
  WiFiClient client;
  String response;
  String request = "GET /RX.php?id=99999&pw=2580" + field + " HTTP/1.1";
  // Serial.println(request);

  if (client.connectSSL("nutipistik.fun", 443)) {
    client.println(request);
    client.println("Host: nutipistik.fun");
    client.println("Connection: close");
    client.println();
  }
  int timeout = 0;
  while (!client.available()){
    delay(1);
    timeout++;
    if(timeout>10000) {
      resetFunc();
    }
  }
  while (client.available()) {
    char c = client.read();
    response += c;
  }
  // if the server's disconnected, stop the client:
  if (!client.connected()) {
    client.flush();
    client.stop();
  }

  return findVal(response);
}
