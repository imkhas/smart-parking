#define TWI_ACK					0
#define TWI_NACK				1

#define TWI_SUCCESS				0
#define TWI_ERROR_INIT			0x5000
#define TWI_ERROR_START			0x5001
#define TWI_ERROR_RESTART		0x5002
#define TWI_ERROR_STOP			0x5003
#define TWI_ERROR_WRITE			0x5004
#define TWI_ERROR_READ			0x5005
#define TWI_ERROR_ACK			0x5006

#if defined(__cplusplus)
extern "C" {
#endif

extern uint16_t TWI_Init(void);
extern uint16_t TWI_Start_Send(void);
extern uint16_t TWI_Restart_Send(void);
extern uint16_t TWI_Address_Write(uint8_t add);
extern uint16_t TWI_Address_Read(uint8_t add);
extern uint16_t TWI_Data_Send(uint8_t byte, uint8_t *ack);
extern uint16_t TWI_Data_Read(uint8_t* data);
extern uint16_t TWI_Data_Read_ACK(uint8_t* data);
extern uint16_t TWI_Stop_Send(void);

#if defined(__cplusplus)
}
#endif
