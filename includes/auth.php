<?php
session_start();

define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'admin123');

// LOGIN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {

    if ($_POST['username'] === ADMIN_USER &&
        $_POST['password'] === ADMIN_PASS) {

        $_SESSION['admin_auth'] = true;

        header('Location: admin.php');
        exit;

    } else {
        $login_error = 'Invalid credentials';
    }
}

// LOGOUT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {

    session_destroy();

    header('Location: login.php');
    exit;
}
?>

