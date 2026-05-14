#include "global.h"

#if defined(__cplusplus)
extern "C" {
#endif

uint16_t ADC_Init(void) {
	ADMUX = 0x4F; 
	ADCSRA = 0x84;
	ADCSRB = 0x00;
	DIDR0 = 0x00;

	return ADC_SUCCESS;
	}
	
uint16_t ADC_Read(uint8_t channel, uint16_t* value) {
	if(channel >= ADC_CHANNEL_NUM) return ADC_ERROR_CHANNEL_RANGE;

	ADMUX &= 0xF0;
	ADMUX |= channel;

	ADCSRA |= 0x40;
	while((ADCSRA & 0x40) == 0x40);

	*value = (uint16_t)ADCL;
	*value = (((uint16_t)ADCH) << 8) + *value;

	return ADC_SUCCESS;
	}

#if defined(__cplusplus)
}
#endif
