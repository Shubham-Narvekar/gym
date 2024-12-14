<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mini_project_gym";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $member_id = $_POST['member_id'];
    $trainer_id = $_POST['trainer_id'];
    $session_time = $_POST['session_time'];
    $duration_minutes = $_POST['duration_minutes'];
    $notes = $_POST['notes'];
    $amount_charged = $_POST['amount_charged'];

    // Insert the training session data into the database
    $query = "
        INSERT INTO training_session (member_id, trainer_id, session_time, duration_minutes, status, notes, amount_charged)
        VALUES (?, ?, ?, ?, 'Scheduled', ?, ?)
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('iisssd', $member_id, $trainer_id, $session_time, $duration_minutes, $notes, $amount_charged);

    if ($stmt->execute()) {
        echo "Training session added successfully!";
    } else {
        echo "Error adding training session: " . $conn->error;
    }

    header("Location: admin_dashboard.php");
}
?>
