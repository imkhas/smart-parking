#include "global.h"

#if defined(__cplusplus)
extern "C" {
#endif

float battery_calc(uint16_t avg_adc){
  float result = 0;

  result = (float)((((1.1f * (float)avg_adc)/1024.0f) * 5.0f) - 1.0f);
  return result;
}

uint16_t batt_percent(uint16_t Bat_Volt){
  uint16_t result = 0;

  result = (uint16_t)(((Bat_Volt - 330)/170)*100);
  return result;
}

#if defined(__cplusplus)
}
#endif
