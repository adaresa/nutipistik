#include <TimeLib.h>

String timescope;

String getRequestAddress(){

  unsigned long epoch = WiFi.getTime();
  epoch = epoch + (0 * 3600);

  // print Unix time:
  // Serial.println(epoch);
  String yearS = String(year(epoch));
  String monthS = String(month(epoch));
  if (monthS.length() == 1){
    monthS = "0" + monthS;
  }
  String dayS = String(day(epoch));
  if (dayS.length() == 1){
    dayS = "0" + dayS;
  }
  String hourS = String(hour(epoch));
  if (hourS.length() == 1){
    hourS = "0" + hourS;
  }

  String timescope = yearS + "-" + monthS + "-" + dayS + "T" + hourS + ":00:00.000Z";
  return ("https://dashboard.elering.ee/api/nps/price?start=" + timescope + "&end=" + timescope);  
}

int updateHour() {
  unsigned long epoch = WiFi.getTime();
  return hour(epoch);
}
