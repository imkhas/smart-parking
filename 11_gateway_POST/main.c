#include "global.h"

typedef enum {
	POST_STATE_START,
	POST_STATE_IDLE,
	POST_STATE_WAIT,
	POST_STATE_REPLY,
	POST_STATE_RESULT
	} POST_STATE_e;

static struct termios orig_termios;

void reset_terminal_mode(void) {
    tcsetattr(STDIN_FILENO, TCSANOW, &orig_termios);
	}

void set_conio_terminal_mode(void) {
    struct termios new_termios;

    tcgetattr(STDIN_FILENO, &orig_termios);									    //get current settings
    atexit(reset_terminal_mode);												//register cleanup

    new_termios = orig_termios;													//disable canonical mode and echo
    new_termios.c_lflag &= ~(ICANON | ECHO);
    tcsetattr(STDIN_FILENO, TCSANOW, &new_termios);
	}

int kbhit(void) {
    struct timeval tv = {0L, 0L};
    fd_set fds;
    FD_ZERO(&fds);
    FD_SET(STDIN_FILENO, &fds);
    return select(STDIN_FILENO+1, &fds, NULL, NULL, &tv);
	}

int getch(void) {
    unsigned char c;
    if (read(STDIN_FILENO, &c, sizeof(c)) < 0) return -1;
    return c;
	}

void on_device_found(const char *name, const char *mac, const char* path) {
	if(strncmp(name, BLE_NAME, sizeof(BLE_NAME)) == 0) {
	    printf("Found device %s (%s), path=%s\n", name, mac, path);
	    strncpy(BLE_PATH, path, sizeof(BLE_PATH));
	    BLE_FOUND = 1;
	    }
	}

int main() {
	uint64_t timeout;
	const char payload[] = "Hello from Linux";
	uint8_t running = 1;
	uint8_t ch;
	char str[65536];
	char str1[65536];
	int32_t err_code;
	int32_t rx_bytes;
	char *ret, *start, *end;
	int8_t web_ok = 0;
	uint32_t i;
	SENSE_t reading;
	volatile POST_STATE_e POST_state = POST_STATE_START;
	volatile POST_STATE_e POST_state_next = POST_STATE_IDLE;
	
	SYS_Error_Check(SYS_Init());
	SYS_Error_Check(BLE_Scan_Start());
	printf("Scanning for %s for %u seconds\n", BLE_NAME, BLE_SCAN_DURATION);

	timeout = SYS_TICK + (BLE_SCAN_DURATION * 1000);
	while((SYS_TICK < timeout) && (!BLE_FOUND)) {
        BLE_Poll(on_device_found);
        usleep(100000);
		}
		
	if(!BLE_FOUND) {
		printf("Did not find device %s after %u seconds\n", BLE_NAME, BLE_SCAN_DURATION);
		return 1;
		}

	printf("Connecting device\n");	
	SYS_Error_Check(BLE_Connect(BLE_PATH));
	printf("Device connected\n");
	
	printf("Resolving service and characteristic\n");
	SYS_Error_Check(BLE_Wait_For_Services_Resolved(BLE_PATH, 5000));
	printf("Service and characteristic resolved\n");
	
	printf("Enabling notification\n");
	SYS_Error_Check(BLE_Notification_Enable(BLE_SERVICE_UUID, BLE_CHARACTERISTIC_UUID, BLE_Receive));
	printf("Notification enabled\n");
	
	printf("BLE ready. Press ESC to terminate application\n");
	set_conio_terminal_mode();	

	while(running) {														//handle BLE_Receive
		dbus_connection_read_write_dispatch(BLE_CONN, 100);
	
		if(kbhit()) {
			ch = getch();
			if(ch == 27) { 													//ESC key ASCII code
				printf("ESC pressed, disconnecting...\n");
				running = 0;
				}
			}

		if(POST_state != POST_state_next) {
			printf("POST state changed\n");
			
			switch(POST_state_next) {
				case POST_STATE_START: {printf("POST_STATE_START\n"); break; }
				case POST_STATE_IDLE: {printf("POST_STATE_IDLE\n"); break; }
				case POST_STATE_WAIT: {printf("POST_STATE_WAIT\n"); break; }
				case POST_STATE_REPLY: {printf("POST_STATE_REPLY\n"); break; }
				case POST_STATE_RESULT: {printf("POST_STATE_RESULT\n"); break; }
				}
			}
		POST_state = POST_state_next;

		switch(POST_state) {
			case POST_STATE_IDLE: {
				if(SENSE_READING >= SENSE_READING_TX_TH) {
					printf("POST TH reached (SENSE_READING = %u)\n", SENSE_READING);
				
					strcpy(str,"data=");
					for(i = 0; i < SENSE_READING_TX_TH; i++) {
						SENSE_Read(&reading);
						sprintf(str1, ",%u,, %u,%lu,%f;",reading.sid, reading. mid, reading.timestamp, reading.value);
						strcat(str, str1);
					}
					
					str[strlen(str) - 1] = '\0';
					printf("POST str = %s\n", str);
					
					if((err_code = CLIENT_POST("/sensor_data.php", str)) == CLIENT_SUCCESS) {
						timeout =  SYS_TICK + 30000;
						POST_state_next = POST_STATE_WAIT;
						}
					else printf("CLIENT_POST FAILED (0x%08X)\n", err_code);
					}
				break;
				}
			case POST_STATE_WAIT: {											//wait for first byte(s) to be received
				if(SYS_TICK < timeout) {
					if((err_code = CLIENT_Read((uint8_t*)str1, sizeof(str1), &rx_bytes)) == CLIENT_SUCCESS) {
						if(rx_bytes > 0) {									//bytes received
							printf("rx_bytes=%d\n", rx_bytes);
							strncpy(str, str1, rx_bytes);
							
							printf("Started receiving reply\n");
							timeout =  SYS_TICK + 100;
							POST_state_next = POST_STATE_REPLY;
							break;
							}
						}
					}
				else {
					printf("Timeout receiving reply\n");
					POST_state_next = POST_STATE_IDLE;
					}
				break;
				}
			case POST_STATE_REPLY: {
				if(SYS_TICK >= timeout) {
					printf("Complete reply received\n");
					POST_state_next = POST_STATE_RESULT;
					}
							
				if((err_code = CLIENT_Read((uint8_t*)str1, sizeof(str1), &rx_bytes)) ==  CLIENT_SUCCESS) {
					if(rx_bytes > 0) {								//bytes received
						strncat(str, str1, rx_bytes);
						timeout =  SYS_TICK + 100;
						}
					
					}
				break;
				}
			case POST_STATE_RESULT: {
				printf("\n!!! RECEIVED: START !!!\n%s", str);							//display returned string
				printf("!!! RECEIVED: END !!!\n\n");

				ret = strstr(str, "\"code\":");
					
				if(ret > 0) {
					strncpy(str1, ret + 7, 3);
					err_code = atoi(str1);
					
					if(err_code == 200) printf("POST successful\n");
					else printf("POST FAILED, err_code = %d\n", err_code);
					}
				
				CLIENT_Close();
				POST_state_next = POST_STATE_IDLE;
				break;
				}
			}
		}	
		
	BLE_Disconnect();
	reset_terminal_mode();
	return 0;
	}

