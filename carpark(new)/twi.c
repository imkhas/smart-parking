#include "global.h"

uint16_t TWI_Init(void) {
	TWSR = 0x01;									//prescaler = 4
	TWBR = 18;										//SCL = 100kHz @ 16MHz

	return TWI_SUCCESS;
	}

uint16_t TWI_Start_Send(void) {
	TWCR = 0xA4;									//reset int., START, enable
	while((TWCR & 0x80) == 0x00);					//wait till START sent

	if((TWSR & 0xF8) == 0x08) return TWI_SUCCESS;	//START sent, OK
	else return TWI_ERROR_START;					//START not sent, ERROR
	}

uint16_t TWI_Restart_Send(void) {
	TWCR = 0xA4;									//rst int., RESTART, enable
	while((TWCR & 0x80) == 0x00);					//wait till RESTART sent//

	if((TWSR & 0xF8) == 0x10) return TWI_SUCCESS;	//RESTART sent, OK
	else return TWI_ERROR_RESTART;					//RESTART not sent, ERROR
	}

uint16_t TWI_Address_Write(uint8_t add) {			//send the address and set for WRITE
	TWDR = (add << 1) & 0xFE;						//address + W
	TWCR = 0x84;									//rst int., enable
	while((TWCR & 0x80) == 0x00);					//wait till address+W sent

	if((TWSR & 0xF8) == 0x18) return TWI_SUCCESS;	//address + W sent OK
	else return TWI_ERROR_WRITE;					//ERROR
	}

uint16_t TWI_Address_Read(uint8_t add) {			//send the address and set for READ
	TWDR = (add << 1) | 0x01;						//address + R
	TWCR = 0x84;									//rst int., enable
	while((TWCR & 0x80) == 0x00);					//wait till address+R sent

	if((TWSR & 0xF8) == 0x40) return TWI_SUCCESS;	//address + R sent OK
	else return TWI_ERROR_READ;						//ERROR
	}

uint16_t TWI_Data_Send(uint8_t byte, uint8_t *ack) {	//send data
	TWDR = byte;									//data
	TWCR = 0x84;									//rst int., enable
	while((TWCR & 0x80) == 0x00);					//wait till data sent

	if((TWSR & 0xF8) == 0x28) *ack = TWI_ACK;		//data sent, ACKed
	else if((TWSR & 0xF8) == 0x30) *ack = TWI_NACK;	//data sent, not ACKed
	else return TWI_ERROR_ACK;						//ERROR

	return TWI_SUCCESS;
	}

uint16_t TWI_Data_Read(uint8_t* data) {				//read data
	TWCR = 0x84;									//rst int., enable
	while((TWCR & 0x80) == 0x00);					//wait till data sent
	*data = TWDR;									//data

	if((TWSR & 0xF8) == 0x58) return TWI_SUCCESS;	//data read no ACK OK
	else return TWI_ERROR_READ;						//ERROR
	}

uint16_t TWI_Data_Read_ACK(uint8_t* data) {
	TWCR = 0xC4;									//rst int., ACK, enable
	while((TWCR & 0x80) == 0x00);					//wait till data sent
	*data = TWDR;									//data

	if((TWSR & 0xF8) == 0x50) return TWI_SUCCESS;	//data read ACK OK
	else return TWI_ERROR_READ;						//ERROR
	}

uint16_t TWI_Stop_Send(void) {
	TWCR = 0x94;								//reset int., STOP, enable
	while((TWCR & 0x10) == 0x10);				//wait till STOP sent

	return TWI_SUCCESS;
	}
