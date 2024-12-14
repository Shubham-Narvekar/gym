<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "mini_project_gym");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $specialization = $_POST['specialization'];
    $certification = $_POST['certification'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $hourly_rate = $_POST['hourly_rate'];
    $availability = $_POST['availability'];

    // Insert query to add trainer to the database
    $query = "INSERT INTO trainer (first_name, last_name, specialization, certification, phone, email, hourly_rate, availability)
              VALUES ('$first_name', '$last_name', '$specialization', '$certification', '$phone', '$email', '$hourly_rate', '$availability')";

    if ($conn->query($query) === TRUE) {
        echo "New trainer added successfully";
    } else {
        echo "Error: " . $query . "<br>" . $conn->error;
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainer Addition Success</title>
</head>
<body>
    <button onclick="window.location.href='admin_dashboard.php'">Back to dashboard</button>
</body>
</html>