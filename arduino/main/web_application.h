/* 
Communicates with the webserver
*/

String values[4];

void findVal(String txt){
  int count = 0;
  String word = "";

  for(int i = 0; i < txt.length(); i++){
    if (txt[i] == '#') { 
      for(int j = i+1; j < txt.length(); j++) {
        if (txt[j] != ','){
          word += txt[j];
        }
        else {
          values[count] = word;
          word = "";
          count++;
        }
      }
      break;
    }
  }
}

void makeRequest(WiFiClient client, String field="") {
  String response;
  String request = "GET /TX.php?id=99999&pw=2580" + field + " HTTP/1.1";
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

  findVal(response);
}
