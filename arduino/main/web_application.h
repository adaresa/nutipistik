/* 
Communicates with the webserver
*/

int findVal(String txt, String looking_for){
  String result;
  String match = "#_" + looking_for;
  bool found;
  for(int i = 0; i < txt.length(); i++){
    if (txt[i] == '#') {
      found = 1;
      i += 1;
      for (int j = 0; j < match.length(); j++){
        if (txt[i+j] != match[j]) {
          found = 0;
        }
      }
      if (found){
        i += match.length();
        while (txt[i] != '#'){
          result += txt[i];
          i += 1;
        }
        return result.toInt();
      }
    }
  }
  return -1;
}

int makeRequest(WiFiClient client, String search, String field="") {
  String response;
  String request = "GET /TX.php?id=99999&pw=2580" + field + " HTTP/1.1";
  Serial.println(request);

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

  if (search) {
    return (findVal(response, search));
  }
  return 0;
}
