<?php
session_start();
$conn = new mysqli("localhost", "root", "", "mini_project_gym");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Insert new class schedule
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $trainer_id = $_POST['trainer_id'];
    $schedule_time = $_POST['schedule_time'];
    $duration_minutes = $_POST['duration_minutes'];
    $capacity = $_POST['capacity'];
    $room = $_POST['room'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("
        INSERT INTO CLASS_SCHEDULE (name, description, trainer_id, schedule_time, duration_minutes, capacity, room, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ssisiiss", $name, $description, $trainer_id, $schedule_time, $duration_minutes, $capacity, $room, $status);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Class scheduled successfully!";
    } else {
        $_SESSION['error'] = "Failed to schedule class: " . $stmt->error;
    }

    header("Location: admin_dashboard.php");
    exit();
}
?>
