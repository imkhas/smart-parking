#ifndef RINGBUFFER_H_
#define RINGBUFFER_H_

#if defined(__cplusplus)
extern "C" {
#endif

typedef struct{
  uint8_t *buff;
  uint8_t size;
  uint8_t head;
  uint8_t tail;
}ringbuffer;

void init_ringbuff(ringbuffer *rb, uint8_t *arr, uint8_t size);

uint8_t get_ringbuff(ringbuffer *rb);

uint8_t peek_ringbuff(ringbuffer *rb);

void put_ringbuff(ringbuffer *rb, uint8_t data);

bool full_ringbuff(ringbuffer *rb);

bool empty_ringbuff(ringbuffer *rb);

#if defined(__cplusplus)
}
#endif

#endif /* RINGBUFFER_H_ */
