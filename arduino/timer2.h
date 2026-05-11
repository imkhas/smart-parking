#ifndef TIMER2_H_
#define TIMER2_H_

#if defined(__cplusplus)
extern "C" {
#endif

extern volatile uint32_t SYS_TICK;

void TIMER2_Init(void);

#if defined(__cplusplus)
}
#endif

#endif /* TIMER2_H_ */
