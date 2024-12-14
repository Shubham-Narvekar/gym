<?php
// Admin_process.php

// Start session to store login information
session_start();

// Default admin credentials
$admin_email = "admin@fitcoregym.com";
$admin_password = "Admin123";

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validate admin credentials
    if ($email === $admin_email && $password === $admin_password) {
        // Successful login
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_email'] = $email;
        header("Location: admin_dashboard.php");
        exit;
    } else {
        // Invalid credentials
        $_SESSION['login_error'] = "Invalid email or password.";
        header("Location: admin_login.html");
        exit;
    }
} else {
    // Redirect to login page if accessed directly
    header("Location: admin_login.html");
    exit;
}
?>
