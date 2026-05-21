#if defined(__cplusplus)
extern "C" {
#endif

float battery_calc(uint16_t avg_adc);

uint16_t batt_percent(uint16_t Bat_Volt);

#if defined(__cplusplus)
}
#endif
