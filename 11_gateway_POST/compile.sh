gcc -o gateway_POST main.c ble.c sys.c sense.c client.c $(pkg-config --cflags --libs dbus-1)
