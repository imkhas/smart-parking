#include "global.h"


#if defined(__cplusplus)
extern "C" {
#endif

void init_ringbuff(ringbuffer *rb, uint8_t *arr, uint8_t size){

  rb->buff = arr;
  rb->size = size;
  rb->head = 0;
  rb->tail = 0;
  
}

uint8_t get_ringbuff(ringbuffer *rb){

  uint8_t data = rb->buff[rb->tail];
  rb->tail++;

  if(rb->tail == rb->size){
    rb->tail = 0;
  }

  return data;
  
}

uint8_t peek_ringbuff(ringbuffer *rb){
  return rb->buff[rb->tail];
}

void put_ringbuff(ringbuffer *rb, uint8_t data){
  rb->buff[rb->head] = data;
  rb->head++;

  if(rb->head == rb->size){
    rb->head = 0;
  }
}

bool full_ringbuff(ringbuffer *rb){
  
  uint8_t headadd = rb->head;
  headadd++;
  if (headadd > rb->size){
    headadd = 0;
  }
  return headadd == rb->tail;
}

bool empty_ringbuff(ringbuffer *rb){

  return rb->tail == rb->head;
  
}

#if defined(__cplusplus)
}
#endif
