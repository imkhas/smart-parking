#define ADC_SUCCESS					0
#define ADC_ERROR_INIT				0x4000
#define ADC_ERROR_CHANNEL_RANGE		0x4001

#define ADC_CHANNEL_NUM				15

#define ADC_BUF_SIZE 4

#if defined(__cplusplus)
extern "C" {
#endif

extern uint16_t ADC_Init(void);
extern uint16_t ADC_Read(uint8_t channel, uint16_t *value);

#if defined(__cplusplus)
}
#endif
