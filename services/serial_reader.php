<?php

include '../includes/db_connect.php';

$port = "/dev/ttyACM0";

exec("stty -F $port 9600 raw -echo");

$serial = fopen($port, "r");

if (!$serial) {
    die("Unable to open serial port");
}

echo "Serial Reader Started...\n";

while (true) {

    $line = trim(fgets($serial));

    if (!$line) {
        continue;
    }

    echo "Received: $line\n";

    $data = explode(',', $line);

    if (count($data) == 4) {

        for ($i = 0; $i < 4; $i++) {

            $slot = $i + 1;
            $status = intval($data[$i]);

            $sql = \"UPDATE parking_slots
                    SET status='$status'
                    WHERE slot_id='$slot'\";

            mysqli_query($conn, $sql);
        }

        echo \"Database Updated\\n\";
    }
}
?>