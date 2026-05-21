#define UART_SUCCESS			0
#define UART_ERROR_INIT			0x3000
#define UART_ERROR_TX_BUF_FULL	0x3001
#define UART_ERROR_RX_BUF_EMPTY	0x3002

#define UART_TX_BUF_SIZE		128			//MUST BE 2^N size
#define UART_RX_BUF_SIZE		128			//MUST BE 2^N size

#if defined(__cplusplus)
extern "C" {
#endif

extern uint8_t	UART_TX_BYTES;				//denotes the number of bytes in TX_BUF left to transmit
extern uint8_t	UART_RX_BYTES;				//denotes the number of bytes in RX_BUF left to read

extern uint16_t UART_Init(void);
extern uint16_t UART_Write(uint8_t byte);
extern uint16_t UART_Write_String(uint8_t* str, uint8_t size);
extern uint16_t UART_Read(uint8_t *byte);

#if defined(__cplusplus)
}
#endif
