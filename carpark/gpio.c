#include "global.h"

#if defined(__cplusplus)
extern "C" {
#endif

uint16_t GPIO_Mode(uint8_t pin, uint8_t mode) {
	uint8_t mask = 0x01;

	if(pin > 13) return GPIO_ERROR_RANGE;
	if(mode > OUTPUT) return GPIO_ERROR_MODE;

	mask = mask << (pin & 7);

	if(pin > 7) {											//DDRB
		if(mode == INPUT) DDRB = DDRB & ~mask;				//set as input (=0)
		else DDRB = DDRB | mask;							//set as output (=1)
		}
	else {													//DDRD
		if(mode == INPUT) DDRD = DDRD & ~mask;				//set as input (=0)
		else DDRD = DDRD | mask;							//set as output (=1)
		}

	return GPIO_SUCCESS;
	}

uint16_t GPIO_Write(uint8_t pin, uint8_t state) {
	uint8_t mask = 0x01 << (pin & 7);

	if(pin > 13) return GPIO_ERROR_RANGE;
	if(state > HIGH) return GPIO_ERROR_STATE;

	if(pin > 7) {											//PORTB
		if(state == LOW) PORTB &= ~mask;					//set LOW
		else PORTB |= mask;									//set HIGH
		}
	else {													//PORTD
		if(state == LOW) PORTD &= ~mask;					//set LOW
		else PORTD |= mask;									//set HIGH
		}

	return GPIO_SUCCESS;
	}

uint16_t GPIO_Read(uint8_t pin, uint8_t* value) {
	uint8_t mask = 0x01 << (pin & 7);
	
	if(pin > 13) return GPIO_ERROR_RANGE;

	if(pin > 7) {
		*value = PINB & mask;				//*value = 0 if pin is 0, else value > 0
		if(*value > 0) *value = 1;
		}
	else {
		*value = PIND & mask;
		if(*value > 0) *value = 1;
		}

	return GPIO_SUCCESS;
	}

#if defined(__cplusplus)
}
#endif
