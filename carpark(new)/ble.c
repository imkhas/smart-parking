#include "global.h"

#if defined(__cplusplus)
extern "C" {
#endif

uint16_t BLE_Init(void) {
	if(GPIO_Write(BLE_EN_PIN, HIGH) != GPIO_SUCCESS) return BLE_ERROR_INIT;		//LOW = AT commands to/from AT-09, HIGH = data to/from AT-09
	if(GPIO_Mode(BLE_EN_PIN, OUTPUT) != GPIO_SUCCESS) return BLE_ERROR_INIT;
	if(GPIO_Mode(BLE_STATE_PIN, INPUT) != GPIO_SUCCESS) return BLE_ERROR_INIT;

	return BLE_SUCCESS;
	}

uint16_t BLE_Connected(uint8_t* connected) {
	if(GPIO_Read(BLE_STATE_PIN, connected) != GPIO_SUCCESS) return BLE_ERROR_STATUS;

	return BLE_SUCCESS;
	}
	
uint16_t BLE_Write_String(uint8_t* str, uint8_t size) {
	if(SWUART_Write_String(str, size) != SWUART_SUCCESS) return BLE_ERROR_TRANSMIT;

	return BLE_SUCCESS;
	}

#if defined(__cplusplus)
}
#endif
