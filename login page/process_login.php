<?php
session_start();
$conn = new mysqli("localhost", "root", "", "mini_project_gym");

// Check if the connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query to check if the member exists
    $query = $conn->prepare("SELECT member_id, password_hash FROM MEMBER WHERE email = ?");
    $query->bind_param("s", $email);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $row['password_hash'])) {
            // Store member ID in session
            $_SESSION['member_id'] = $row['member_id'];

            // Redirect to the member dashboard
            header("Location: member_dashboard.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "Email not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Error</title>
    <link rel="stylesheet" href="login_styles.css">
</head>
<body>
    <div class="login-form-container">
        <div class="login-form">
            <h2>Login Failed</h2>
            <p style="color: red;"><?= isset($error) ? $error : "An unknown error occurred." ?></p>
            <button class="back-button" onclick="window.history.back();">
                <i class="fas fa-arrow-left"></i> Back to Login
            </button>
        </div>
    </div>
</body>
</html>
