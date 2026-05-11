#include "global.h"

#if defined(__cplusplus)
extern "C" {
#endif

void DELAY(uint32_t delay) {
  uint32_t timeout = SYS_TICK + delay;

  while(SYS_TICK < timeout);
  }

#if defined(__cplusplus)
}
#endif
