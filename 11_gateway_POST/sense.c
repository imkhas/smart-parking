#include "global.h"

SENSE_t		SENSE[SENSE_SIZE] = {0};
uint16_t 	SENSE_WR;
uint16_t 	SENSE_RD;
uint16_t	SENSE_READING;

int32_t SENSE_Init(void) {
	SENSE_WR = 0;
	SENSE_RD = 0;
	SENSE_READING = 0;
	memset(SENSE, 0, sizeof(SENSE));

	return SENSE_SUCCESS;
	}

int32_t SENSE_Write(SENSE_t* sense) {
	SENSE[SENSE_WR].sid = sense -> sid;
	SENSE[SENSE_WR].mid = sense -> mid;
	SENSE[SENSE_WR].timestamp = sense->timestamp;
	SENSE[SENSE_WR].value = sense->value;
	
	SENSE_WR = (SENSE_WR + 1) & (SENSE_SIZE - 1);

	if(SENSE_READING >= SENSE_SIZE) return SENSE_ERROR_BUF_FULL;

	SENSE_READING++;
	return SENSE_SUCCESS;
	}

int32_t SENSE_Read(SENSE_t* sense) {
	if(!SENSE_READING) return SENSE_ERROR_BUF_EMPTY;
	
	sense->sid = SENSE[SENSE_RD].sid;
	sense->mid = SENSE[SENSE_RD].mid;
	sense->timestamp = SENSE[SENSE_RD].timestamp;
	sense->value = SENSE[SENSE_RD].value;
	
	SENSE_RD = (SENSE_RD + 1) & (SENSE_SIZE - 1);
	
	SENSE_READING--;
	
	return SENSE_SUCCESS;
	}
