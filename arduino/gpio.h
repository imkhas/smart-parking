#ifndef GPIO_H_
#define GPIO_H_

#include "global.h"

#define OUTPUT  0
#define INPUT   1

#define LOW     0
#define HIGH    1

typedef enum {
  PIN_0,
  PIN_1,
  PIN_2,
  PIN_3,
  PIN_4,
  PIN_5,
  PIN_6,
  PIN_7,
  PIN_8,
  PIN_9,
  PIN_10,
  PIN_11,
  PIN_12,
  PIN_13,
  PIN_14,
  PIN_15,
  
}gpio_pin_en;


#if defined(__cplusplus)
extern "C" {
#endif

void gpio_init(void);
void gpio_mode(gpio_pin_en pin, uint8_t mode);
void gpio_write(gpio_pin_en pin, uint8_t state);
void gpio_read(gpio_pin_en pin, uint8_t *ptr);

#if defined(__cplusplus)
}
#endif

#endif /* GPIO_H_ */
