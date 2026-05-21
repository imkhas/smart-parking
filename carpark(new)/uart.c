#include "global.h"

#if defined(__cplusplus)
extern "C" {
#endif

uint8_t UART_TX_BUF[UART_TX_BUF_SIZE];	//transmit buffer
uint8_t UART_TX_BUF_WR;
uint8_t UART_TX_BUF_RD;
uint8_t	UART_TX_BYTES;

uint8_t UART_RX_BUF[UART_RX_BUF_SIZE];	//receive buffer
uint8_t UART_RX_BUF_WR;
uint8_t UART_RX_BUF_RD;
uint8_t	UART_RX_BYTES;

uint16_t UART_Init(void) {
	uint8_t dummy = UDR0;				//just to ensure if there is a byte in it, it is read and receive interrupt flag is reset
	UCSR0B = 0x98;						//enable transmitter, enable receiver, enable receiver interrupt
	UCSR0C = 0x06;						//8-bit character, 1 stop bit, no parity
	UBRR0 = 103;						//9600 baud @ 16MHz

	UART_TX_BUF_WR = 0;
	UART_TX_BUF_RD = 0;
	UART_TX_BYTES = 0;
	UART_RX_BUF_WR = 0;
	UART_RX_BUF_RD = 0;
	UART_RX_BYTES = 0;
	
	return UART_SUCCESS;
	}

uint16_t UART_Write(uint8_t byte) {
	uint8_t int_state = UCSR0B & 0x20;		//record state of UDR interrupt (if set means UART is BUSY)
	
	if((UART_TX_BYTES + 1) > UART_TX_BUF_SIZE) return UART_ERROR_TX_BUF_FULL;

	UART_TX_BUF[UART_TX_BUF_WR] = byte;
	UART_TX_BUF_WR = (UART_TX_BUF_WR + 1) & (UART_TX_BUF_SIZE - 1);
	UART_TX_BYTES++;

	if(int_state == 0) {						//interrupt not enabled, UART is FREE
		UDR0 = UART_TX_BUF[UART_TX_BUF_RD];		//write byte
		UART_TX_BUF_RD = (UART_TX_BUF_RD + 1) & (UART_TX_BUF_SIZE - 1);
		UART_TX_BYTES--;
		}

	UCSR0B |= 0x20;								//enable interrupt
	return UART_SUCCESS;
	}
	
uint16_t UART_Write_String(uint8_t* str, uint8_t size) {
	uint8_t i;
	uint8_t int_state = UCSR0B & 0x20;		//record state of UDR interrupt (if set means UART is BUSY)

	UCSR0B &= ~0x20;							//disable interrupt
	if((size + UART_TX_BYTES) > UART_TX_BUF_SIZE) return UART_ERROR_TX_BUF_FULL;

	for(i = 0; i < size; i++) {					//write to TX_BUF
		UART_TX_BUF[UART_TX_BUF_WR] = str[i];
		UART_TX_BUF_WR = (UART_TX_BUF_WR + 1) & (UART_TX_BUF_SIZE - 1);
		UART_TX_BYTES++;
		}

	if(int_state == 0) {						//interrupt not enabled, UART is FREE
		UDR0 = UART_TX_BUF[UART_TX_BUF_RD];		//write first byte of string
		UART_TX_BUF_RD = (UART_TX_BUF_RD + 1) & (UART_TX_BUF_SIZE - 1);
		UART_TX_BYTES--;
		}

	UCSR0B |= 0x20;								//enable interrupt		
	return UART_SUCCESS;
	}

ISR(USART_UDRE_vect) {
	if(UART_TX_BYTES > 0) {						//got bytes left to TX
		UDR0 = UART_TX_BUF[UART_TX_BUF_RD];		//write first byte
		UART_TX_BUF_RD = (UART_TX_BUF_RD + 1) & (UART_TX_BUF_SIZE - 1);
		UART_TX_BYTES--;
		}		
	else UCSR0B &= ~0x20;						//no more bytes left to TX, disable UDRE interrupt
	}

ISR(USART_RX_vect) {							//ISR for reception
	uint8_t byte = UDR0;						//reads the register and store in byte

	if(UART_RX_BYTES < UART_RX_BUF_SIZE) {		//there is space in RX_BUF
		UART_RX_BUF[UART_RX_BUF_WR] = byte;
		UART_RX_BUF_WR = (UART_RX_BUF_WR + 1) & (UART_RX_BUF_SIZE - 1);
		UART_RX_BYTES++;
		}
	}

uint16_t UART_Read(uint8_t *byte) {
	if(UART_RX_BYTES == 0) return UART_ERROR_RX_BUF_EMPTY;

	*byte = UART_RX_BUF[UART_RX_BUF_RD];
	UART_RX_BUF_RD = (UART_RX_BUF_RD + 1) & (UART_RX_BUF_SIZE - 1);
	UART_RX_BYTES--;
	
	return UART_SUCCESS;
	}

#if defined(__cplusplus)
}
#endif
