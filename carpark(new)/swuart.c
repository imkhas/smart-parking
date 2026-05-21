#include "global.h"

#if defined(__cplusplus)
extern "C" {
#endif

SWUART_STATE_e SWUART_TX_STATE;					//SWUART transmitter state
SWUART_STATE_e SWUART_RX_STATE;					//SWUART receiver state

uint8_t SWUART_TX_BUF[SWUART_TX_BUF_SIZE];		//transmit (TX) buffer
uint8_t SWUART_TX_BUF_WR;						//TX buffer write pointer
uint8_t SWUART_TX_BUF_RD;						//TX buffer read pointer
uint8_t	SWUART_TX_BYTES;						//denotes the number of bytes in TX_BUF left to transmit

uint8_t SWUART_RX_BUF[SWUART_TX_BUF_SIZE];		//receive (RX) buffer
uint8_t SWUART_RX_BUF_WR;						//RX buffer write pointer
uint8_t SWUART_RX_BUF_RD;						//RX buffer read pointer
uint8_t	SWUART_RX_BYTES;						//denotes the number of bytes in RX_BUF left to transmit

uint16_t SWUART_Init(void) {
	SWUART_TX_STATE = SWUART_STATE_IDLE;
	SWUART_TX_BUF_WR = 0;
	SWUART_TX_BUF_RD = 0;
	SWUART_TX_BYTES = 0;

	SWUART_RX_STATE = SWUART_STATE_IDLE;
	SWUART_RX_BUF_WR = 0;
	SWUART_RX_BUF_RD = 0;
	SWUART_RX_BYTES = 0;

	GPIO_Write(SWUART_TX_PIN, HIGH);			//default RS232 IDLE level
	GPIO_Mode(SWUART_TX_PIN, OUTPUT);			//set as output
	GPIO_Mode(SWUART_RX_PIN, INPUT);			//set as input
	
	return SWUART_SUCCESS;
	}

uint16_t SWUART_Process(void) {
	static uint8_t tx_cycle, rx_cycle;
	static uint16_t tx_byte, rx_byte;
	uint8_t pin;
		
	if(SWUART_TX_STATE == SWUART_STATE_IDLE) {				//TX is IDLE
		if(SWUART_TX_BYTES > 0) {							//got byte(s) to transmit
			tx_cycle = 0;									//zero the cycle
			tx_byte = (SWUART_TX_BUF[SWUART_TX_BUF_RD] << 1) | 0x0200;
			SWUART_TX_STATE = SWUART_STATE_BUSY;			//change to BUSY state on next call of SWUART_Process
			}
		}
	else if(SWUART_TX_STATE == SWUART_STATE_BUSY) {			//busy transmitting
		if(tx_cycle >= 39) {
			SWUART_TX_BUF_RD = (SWUART_TX_BUF_RD + 1) & (SWUART_TX_BUF_SIZE - 1);
			SWUART_TX_BYTES--;
			GPIO_Write(SWUART_TX_PIN, HIGH);				//make sure IDLE
			SWUART_TX_STATE = SWUART_STATE_IDLE;			//finish transmission, back to IDLE
			}
		else {												//still transmitting
			if((tx_cycle & 3) == 0) {						//0, 4, 8, etc
				if((tx_byte & 0x01) == 0x01) GPIO_Write(SWUART_TX_PIN, HIGH);
				else GPIO_Write(SWUART_TX_PIN, LOW);

				tx_byte = tx_byte >> 1;
				}
			}
		tx_cycle++;
		}
	else Error_Handler(SWUART_ERROR_TX_FSM, __LINE__, __FILE__);	//SHOULD NEVER COME HERE!

	if(SWUART_RX_STATE == SWUART_STATE_IDLE) {
		GPIO_Read(SWUART_RX_PIN, &pin);
		
		if(pin == LOW) {
			rx_cycle = 1;
			rx_byte = 0;
			SWUART_RX_STATE = SWUART_STATE_BUSY;
			}
		}
	else if(SWUART_RX_STATE == SWUART_STATE_BUSY) {
		if(rx_cycle >= 39) {								//entire byte received (include STOP bit)
			if(((rx_byte & 0x0001) == 0) && ((rx_byte & 0x0200) == 0x0200)) {	//START = 0, STOP = 1
				if(SWUART_RX_BYTES < SWUART_RX_BUF_SIZE) {	//enough space in buffer
					rx_byte >>= 1;							//remove START
					SWUART_RX_BUF[SWUART_RX_BUF_WR] = (uint8_t)rx_byte;
					SWUART_RX_BUF_WR = (SWUART_RX_BUF_WR + 1) & (SWUART_RX_BUF_SIZE - 1);
					SWUART_RX_BYTES++;
					}
				}

			SWUART_RX_STATE = SWUART_STATE_IDLE;
			}
		else {												//not fully received
			if((rx_cycle  & 3) == 2) {						//2, 6, 10, etc.
				GPIO_Read(SWUART_RX_PIN, &pin);				//read bit
				if(pin) rx_byte |= 0x0400;
				rx_byte = rx_byte >> 1;
				}
			}
		rx_cycle++;
		}
	else Error_Handler(SWUART_ERROR_RX_FSM, __LINE__, __FILE__);	//SHOULD NEVER COME HERE!	
		
	return SWUART_SUCCESS;
	}

uint16_t SWUART_Write(uint8_t byte) {
	if((SWUART_TX_BYTES + 1) > SWUART_TX_BUF_SIZE) return SWUART_ERROR_TX_BUF_FULL;

	SWUART_TX_BUF[SWUART_TX_BUF_WR] = byte;
	SWUART_TX_BUF_WR = (SWUART_TX_BUF_WR + 1) & (SWUART_TX_BUF_SIZE - 1);
	SWUART_TX_BYTES++;	
	
	return SWUART_SUCCESS;
	}
	
uint16_t SWUART_Write_String(uint8_t* str, uint8_t size) {
	uint8_t i;

	if((size + SWUART_TX_BYTES) > SWUART_TX_BUF_SIZE) return SWUART_ERROR_TX_BUF_FULL;

	for(i = 0; i < size; i++) {					//write to TX_BUF
		SWUART_TX_BUF[SWUART_TX_BUF_WR] = str[i];
		SWUART_TX_BUF_WR = (SWUART_TX_BUF_WR + 1) & (SWUART_TX_BUF_SIZE - 1);
		SWUART_TX_BYTES++;
		}

	return SWUART_SUCCESS;
	}

uint16_t SWUART_Read(uint8_t *byte) {
	if(SWUART_RX_BYTES == 0) return SWUART_ERROR_RX_BUF_EMPTY;

	*byte = SWUART_RX_BUF[SWUART_RX_BUF_RD];
	SWUART_RX_BUF_RD = (SWUART_RX_BUF_RD + 1) & (SWUART_RX_BUF_SIZE - 1);
	SWUART_RX_BYTES--;
	
	return SWUART_SUCCESS;
	}

#if defined(__cplusplus)
}
#endif
