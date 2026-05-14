#ifndef __SENSE_H__
#define __SENSE_H__
#ifdef __cplusplus
extern "C" {
#endif

#define SENSE_ERROR_BASE		0x00003000
#define SENSE_SUCCESS			0
#define SENSE_ERROR_BUF_EMPTY	(SENSE_ERROR_BASE + 1)
#define SENSE_ERROR_BUF_FULL	(SENSE_ERROR_BASE + 2)

#define SENSE_SIZE				64			//number of sensing elements (must be 2^N)
#define SENSE_READING_TX_TH		1			//number of readings before POST occur
#define SENSE_READING_TX_TO		5000		//timeout (ms) before POST occur

typedef enum {
	MEAS_IR_A,							//0
	MEAS_IR_B,							//1
	MEAS_IR_C,							//2
	MEAS_IR_D,							//3
}MEAS_SENSE;

typedef struct {
	uint32_t 		sid;
	MEAS_SENSE		mid;
	uint64_t		timestamp;
	float			value;
} SENSE_t;

extern SENSE_t		SENSE[SENSE_SIZE];
extern uint16_t 	SENSE_WR;
extern uint16_t 	SENSE_RD;
extern uint16_t		SENSE_READING;

extern int32_t SENSE_Init(void);
extern int32_t SENSE_Write(SENSE_t* sense);
extern int32_t SENSE_Read(SENSE_t* sense);

#ifdef __cplusplus
}
#endif


#endif

