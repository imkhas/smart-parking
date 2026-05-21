#define INPUT				  0
#define OUTPUT				1
#define LOW					  0
#define HIGH			  	1

#define GPIO_SUCCESS		  0
#define GPIO_ERROR_RANGE	0x1000
#define GPIO_ERROR_MODE		0x1001
#define GPIO_ERROR_STATE	0x1002

#if defined(__cplusplus)
extern "C" {
#endif

extern uint16_t GPIO_Mode(uint8_t pin, uint8_t mode);
extern uint16_t GPIO_Write(uint8_t pin, uint8_t state);
extern uint16_t GPIO_Read(uint8_t pin, uint8_t* value);

#if defined(__cplusplus)
}
#endif
