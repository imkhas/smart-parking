#define BLE_SUCCESS					  0
#define BLE_ERROR_INIT				0x9000
#define BLE_ERROR_STATUS			0x9001
#define BLE_ERROR_TRANSMIT	  0x9002

#define BLE_EN_PIN					4
#define BLE_STATE_PIN				5

#if defined(__cplusplus)
extern "C" {
#endif

extern uint16_t BLE_Init(void);
extern uint16_t BLE_Connected(uint8_t* connected);
extern uint16_t BLE_Write_String(uint8_t* str, uint8_t size);

#if defined(__cplusplus)
}
#endif
