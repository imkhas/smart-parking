#include <stdio.h>
#include <stdint.h>
//#include <arduino.h>
#include <avr/io.h>
#include <avr/interrupt.h>
#include <string.h>
#include "delay.h"
#include "gpio.h"
#include "timer2.h"
#include "timer0.h"
#include "uart.h"
#include "adc.h"
#include "twi.h"
#include "swuart.h"
#include "ble.h"

//global setting
#define ERROR_CHECK_ENABLE			1
#define IR_BUF_SIZE				4
	
//macros
#if ERROR_CHECK_ENABLE > 0
#define CHECK_ERROR_FATAL(x)		if((err_code = x) != 0) Error_Handler(err_code, __LINE__, __FILE__);
#else
#define CHECK_ERROR_FATAL(x)		x
#endif

#if defined(__cplusplus)
extern "C" {
#endif

extern void Error_Handler(uint16_t code, uint32_t line, char* file);

#if defined(__cplusplus)
}
#endif
