#include "gpio.h"


#if defined(__cplusplus)
extern "C" {
#endif


void gpio_mode(gpio_pin_en pin, uint8_t mode){

  uint8_t mask = 1;
  mask = mask << (pin % 8);
  
  if (pin > 7){
    if (mode == INPUT){
      DDRB &= ~mask;
    }
    else{
      DDRB |= mask;
    }
  }
  else{
    if (mode == INPUT){
      DDRD &= ~(mask);
    }
    else{
      DDRD |= mask;
    }
  }

  //return EX_SUCCESS;
}

void gpio_write(gpio_pin_en pin, uint8_t state){

  uint8_t mask = 1;
  mask = mask << (pin % 8);

  if (pin > 7){
    if (state == LOW){
      PORTB &= ~mask;
    }
    else{
      PORTB |= mask;
    }
    
  }
  else{
    if (state == LOW){
      PORTD &= ~mask;
    }
    else{
      PORTD |= mask;
    }
  }
  
  //return EX_SUCCESS;
}

void gpio_read(gpio_pin_en pin, uint8_t *ptr ){

  uint8_t mask = 1;
  mask = mask << (pin % 8);

  if (pin > 7){
    *ptr = PINB & mask;
    if (*ptr > 0) ptr = 1;
  }
  else{
    *ptr = PIND & mask;
    if (*ptr > 0) ptr = 1;
  }
  
  //return EX_SUCCESS;
}

#if defined(__cplusplus)
}
#endif
