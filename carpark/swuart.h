#define SWUART_SUCCESS				0
#define SWUART_ERROR_INIT			0x8000
#define SWUART_ERROR_TX_BUF_FULL	0x8001
#define SWUART_ERROR_TX_FSM			0x8002
#define SWUART_ERROR_RX_BUF_EMPTY	0x8003
#define SWUART_ERROR_RX_FSM			0x8004

#define SWUART_TX_BUF_SIZE			128			//MUST BE 2^N size
#define SWUART_RX_BUF_SIZE			128			//MUST BE 2^N size

#define SWUART_RX_PIN				2
#define SWUART_TX_PIN				3

#if defined(__cplusplus)
extern "C" {
#endif

typedef enum {									//self created data type (of type enum)
	SWUART_STATE_IDLE,
	SWUART_STATE_BUSY
	} SWUART_STATE_e;							//name of the self created enum data type

extern uint8_t	SWUART_TX_BYTES;				//denotes the number of bytes in TX_BUF left to transmit
extern uint8_t	SWUART_RX_BYTES;				//denotes the number of bytes in RX_BUF that has not been read

extern uint16_t SWUART_Init(void);
extern uint16_t SWUART_Process(void);
extern uint16_t SWUART_Write(uint8_t byte);
extern uint16_t SWUART_Write_String(uint8_t* str, uint8_t size);
extern uint16_t SWUART_Read(uint8_t* byte);

#if defined(__cplusplus)
}
#endif
