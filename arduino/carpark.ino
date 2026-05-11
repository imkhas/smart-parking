#include "global.h"

gpio_pin_en IR_PIN = 2;
gpio_pin_en LED_PIN = 8;



int main(){
  TIMER2_Init();

  ringbuffer rb;
  //gpio_mode(IR_PIN, INPUT);
  gpio_mode(LED_PIN, OUTPUT);

  while(1){
    uint8_t *data;
    gpio_read(IR_PIN, data);
    
    if (*data > 0){
      gpio_write(LED_PIN, LOW);
      
    }
    else{
      gpio_write(LED_PIN, HIGH);
      
    }

    
  }


}
