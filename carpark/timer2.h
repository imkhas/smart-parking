#define TIMER2_SUCCESS		0
#define TIMER2_ERROR_INIT	0x2000

#if defined(__cplusplus)
extern "C" {
#endif

extern volatile uint32_t SYS_TICK;

extern uint16_t TIMER2_Init(void);

#if defined(__cplusplus)
}
#endif
