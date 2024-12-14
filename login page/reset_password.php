<?php
session_start();
$conn = new mysqli("localhost", "root", "", "mini_project_gym");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Ensure the passwords match
    if ($password === $confirm_password) {
        // Hash the new password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Update the password in the database
        $update_query = $conn->prepare("UPDATE MEMBER SET password_hash = ? WHERE email = ?");
        $update_query->bind_param("ss", $password_hash, $email);
        $update_query->execute();

        if ($update_query->affected_rows > 0) {
            echo "<h2>Success</h2><p>Password updated successfully. <a href='member_login.html'>Log in</a></p>";
        } else {
            echo "<h2>Error</h2><p>Email not found or an error occurred. Please try again.</p>";
        }
    } else {
        echo "<h2>Error</h2><p>Passwords do not match. Please try again.</p>";
    }
}
?>
