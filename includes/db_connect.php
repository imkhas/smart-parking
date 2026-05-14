<?php

$conn = mysqli_connect(
    "localhost",
    "admin",
    "admin123",
    "smart_parking"
);

if (!$conn) {
    die("DB Connection failed: " . mysqli_connect_error());
}

?>