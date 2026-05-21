#include "global.h"

//#define LED_PIN						13

#define LED1_PIN					8
#define LED2_PIN					10
#define LED3_PIN					12
#define LED4_PIN					6

#define LED_FULL_PIN			16

#define IR1_PIN						9
#define IR2_PIN						11
#define IR3_PIN						13
#define IR4_PIN						7

#define IR_COUNT					4
#define ADC_PIN						0

typedef union{
	float f;
	uint8_t c[4];
	uint16_t half[4];
}float_t;

void Error_Handler(uint16_t code, uint32_t line, char* file);

int main(void) {
	uint16_t err_code;
	uint8_t pin_value;
	uint32_t timeout = 0;
	uint8_t LED_state = 0;

	uint8_t IR_PIN[IR_COUNT] = {9, 11, 13, 7};
	uint8_t IR_state[IR_COUNT] = {1, 1, 1, 1};
	uint8_t IR_LastState[IR_COUNT] = {1, 1, 1, 1};

	char str[80];
	bool first_state_run = false;
	bool IR_tx_true = false;
	uint32_t IR_timeout = SYS_TICK + 1000;

	bool first_run = true;
	uint32_t ADC_timeout = SYS_TICK + 3000;
	uint16_t ADC_value[ADC_BUF_SIZE] = {0};
	uint32_t ADC_sum = 0;
	uint16_t ADC_average;
	uint8_t ADC_ptr = 0;

	float Batt_Volt = 0;
	uint16_t batt_health = 0;

	uint8_t BLE_connected_pre = 0;
	uint8_t BLE_connected = 0;
	char BLE_connected_str[] = "BLE Connected\n";
	char BLE_disconnected_str[] = "BLE DISCONNECTED\n";

	float_t float_bat;
	float_t float_val;
	uint8_t data[IR_COUNT];
	uint8_t received_byte;
	
	CHECK_ERROR_FATAL(TIMER2_Init());
	sei();

	CHECK_ERROR_FATAL(UART_Init());
	CHECK_ERROR_FATAL(ADC_Init());
	CHECK_ERROR_FATAL(TIMER0_Init());
	CHECK_ERROR_FATAL(SWUART_Init());
	CHECK_ERROR_FATAL(BLE_Init());

	/*CHECK_ERROR_FATAL(GPIO_Write(LED_PIN, LOW));
	CHECK_ERROR_FATAL(GPIO_Mode(LED_PIN, OUTPUT));*/

	CHECK_ERROR_FATAL(GPIO_Mode(IR_PIN[0], INPUT));
	CHECK_ERROR_FATAL(GPIO_Mode(LED1_PIN, OUTPUT));

	
	CHECK_ERROR_FATAL(GPIO_Mode(IR_PIN[1], INPUT));
	CHECK_ERROR_FATAL(GPIO_Mode(LED2_PIN, OUTPUT));

	
	CHECK_ERROR_FATAL(GPIO_Mode(IR_PIN[2], INPUT));
	CHECK_ERROR_FATAL(GPIO_Mode(LED3_PIN, OUTPUT));

	CHECK_ERROR_FATAL(GPIO_Mode(IR_PIN[3], INPUT));
	CHECK_ERROR_FATAL(GPIO_Mode(LED4_PIN, OUTPUT));


	snprintf(str, sizeof(str), "Compiled on %s %s\nSystem Initialised\n", __DATE__, __TIME__);
	CHECK_ERROR_FATAL(UART_Write_String(str, strlen(str)));

	for(;;) {
		BLE_connected_pre = BLE_connected;
		CHECK_ERROR_FATAL(BLE_Connected(&BLE_connected));

		if(BLE_connected != BLE_connected_pre) {
			if(BLE_connected) {
				CHECK_ERROR_FATAL(UART_Write_String(BLE_connected_str, strlen(BLE_connected_str)));
			}
			else {
				CHECK_ERROR_FATAL(UART_Write_String(BLE_disconnected_str, strlen(BLE_disconnected_str)));
			}
		}
		
		/*if(SYS_TICK >= timeout) {
			timeout = SYS_TICK + 1000;

			if(LED_state == 0) {
				CHECK_ERROR_FATAL(GPIO_Write(LED_PIN, HIGH));		
				LED_state = 1;
			}
			else {
				CHECK_ERROR_FATAL(GPIO_Write(LED_PIN, LOW));
				LED_state = 0;			
			}
		}*/



		for (int i = 0; i < IR_COUNT; i++) CHECK_ERROR_FATAL(GPIO_Read(IR_PIN[i], &data[i]));
		
		if(IR_state[0] != data[0]) {IR_LastState[0] = IR_state[0]; IR_state[0] = data[0]; CHECK_ERROR_FATAL(GPIO_Write(LED1_PIN, (data[0] == 0) ? HIGH: LOW)); }
		if(IR_state[1] != data[1]) {IR_LastState[1] = IR_state[1]; IR_state[1] = data[1]; CHECK_ERROR_FATAL(GPIO_Write(LED2_PIN, (data[1] == 0) ? HIGH: LOW)); }
		if(IR_state[2] != data[2]) {IR_LastState[2] = IR_state[2]; IR_state[2] = data[2]; CHECK_ERROR_FATAL(GPIO_Write(LED3_PIN, (data[2] == 0) ? HIGH: LOW)); }
		if(IR_state[3] != data[3]) {IR_LastState[3] = IR_state[3]; IR_state[3] = data[3]; CHECK_ERROR_FATAL(GPIO_Write(LED4_PIN, (data[3] == 0) ? HIGH: LOW)); }

		if((SYS_TICK >= IR_timeout) ) {
			IR_timeout = SYS_TICK + 3000;

			/*ADC_sum -= ADC_value[ADC_ptr];								//subtract oldest value
			CHECK_ERROR_FATAL(ADC_Read(ADC_PIN, &IR_value[IR_ptr]));	//read latest value
			ADC_sum += ADC_value[ADC_ptr];								//add newest value
			ADC_average = ADC_sum / ADC_BUF_SIZE;						//ADC averaged value*/
			
			for (int i = 0; i < IR_COUNT; i++){
				
				if (IR_LastState[i] != IR_state[i]){
					
					
					float_val.f = (float)data[i];
					if(BLE_connected) {											//BLE connected
						str[0] = 200+i;
						str[1] = 0;
						str[2] = float_val.c[0];					//MSB in str[0]
						str[3] = float_val.c[1];
						str[4] = float_val.c[2];
						str[5] = float_val.c[3];
						str[6] = 1;
						
						BLE_Write_String(str, 6);								//send result to BLE module for transmission
					}
					else {														//BLE not connected
						snprintf(str, sizeof(str), "%u\n", data[i]);
						UART_Write_String(str, strlen(str));					//print result to serial monitor
					}

					IR_tx_true = IR_tx_true || (IR_LastState[i] != IR_state[i]);
					first_state_run = true;
					IR_LastState[i] = IR_state[i];
				}
			}

			if ((SYS_TICK >= ADC_timeout) || (IR_tx_true != true) && (first_state_run)){
				IR_tx_true == false;
				ADC_timeout = SYS_TICK + 3000;

				for (int i = 0; (first_run == true) && (i < ADC_BUF_SIZE); i++){
				ADC_sum -= ADC_value[ADC_ptr];								//subtract oldest value
				CHECK_ERROR_FATAL(ADC_Read(ADC_PIN, &ADC_value[ADC_ptr]));	//read latest value
				ADC_sum += ADC_value[ADC_ptr];								//add newest value
				ADC_average = ADC_sum / ADC_BUF_SIZE;						//ADC averaged value
				Batt_Volt = battery_calc(ADC_average);
				//batt_health = batt_percent(Batt_Volt);
				//ADC_ptr = (ADC_ptr + 1) & (ADC_BUF_SIZE - 1);
			}
			

			if (first_run == false){
				ADC_sum -= ADC_value[ADC_ptr];								//subtract oldest value
				CHECK_ERROR_FATAL(ADC_Read(ADC_PIN, &ADC_value[ADC_ptr]));	//read latest value
				ADC_sum += ADC_value[ADC_ptr];								//add newest value
				ADC_average = ADC_sum / ADC_BUF_SIZE;						//ADC averaged value
				Batt_Volt = battery_calc(ADC_average);
				//batt_health = batt_percent(Batt_Volt);
			}
			
			float_bat.f = Batt_Volt;
			if((BLE_connected)) {												//BLE connected
				str[0] = 100;									
				str[1] = 1;
				str[2] = float_bat.c[0];
				str[3] = float_bat.c[1];
				str[4] = float_bat.c[2];
				str[5] = float_bat.c[3];
				BLE_Write_String(str, 6);								//send result to BLE module for transmission
				}
			else {														//BLE not connected
				snprintf(str, sizeof(str), "adc: %hu,  Batt Volt: %hu, Batt Percent: %hu\n", ADC_average, Batt_Volt, batt_percent);
				UART_Write_String(str, strlen(str));					//print result to serial monitor
				}
			
			if (first_run == false) {ADC_ptr = (ADC_ptr + 1) & (ADC_BUF_SIZE - 1); }
			first_run = false;
			}

		}

		if(UART_RX_BYTES > 0) {									//received by UART (from Serial Monitor)
			CHECK_ERROR_FATAL(UART_Read(&received_byte));		//read UART
			CHECK_ERROR_FATAL(SWUART_Write(received_byte));		//write to SWUART (send to BLE module)
		}
			
		if(SWUART_RX_BYTES > 0) {								//received by SWUART (from BLE module)
			CHECK_ERROR_FATAL(SWUART_Read(&received_byte));		//read SWUART
			CHECK_ERROR_FATAL(UART_Write(received_byte));		//write to UART (send to Serial Monitor)
		}
	}
}


void Error_Handler(uint16_t code, uint32_t line, char* file) {
	char str[200];
	uint8_t i = 0;
	
	GPIO_Write(13, HIGH);
	GPIO_Mode(13, OUTPUT);

	UCSR0B = 0x08;						//enable transmitter
	UCSR0C = 0x06;						//8-bit character, 1 stop bit, no parity
	UBRR0 = 103;						//9600 baud @ 16MHz

	snprintf(str, sizeof(str), "ERROR: 0x%04X", code);
	snprintf(str, sizeof(str), "%s at line %u", str, line);
	snprintf(str, sizeof(str), "%s in %s\n", str, file);

	do	{
		UDR0 = str[i];					//transmit str[i]
		i++;							//point to next character in string
		while((UCSR0A & 0x20) == 0);	//wait until UDRE0 bit is set
		} while(str[i] != 0);			//do while str[i] is NOT NUL (0x00)

	for(;;);
}
