#include <TimeLib.h>

int updateHour() {
  unsigned long epoch = WiFi.getTime();
  return hour(epoch);
}
