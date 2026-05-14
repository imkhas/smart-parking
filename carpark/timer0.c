#include "global.h"

#if defined(__cplusplus)
extern "C" {
#endif

uint16_t TIMER0_Init(void) {
	TCCR0A = 0x02;
	TCCR0B = 0x02;			//prescaler = 8 (16MHz / 8 = 2MHz)
	OCR0A = 51;				//gives 38461.5Hz @16MHz (0.16% error)
	TIFR0 = 0x02;
	TIMSK0 = 0x02;

	return TIMER0_SUCCESS;
	}

ISR(TIMER0_COMPA_vect) {
	SWUART_Process();
	}

#if defined(__cplusplus)
}
#endif
