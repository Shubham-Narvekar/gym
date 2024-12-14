<?php
// Start session
session_start();

// Include database connection
$conn = new mysqli("localhost", "root", "", "mini_project_gym");

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $member_id = $_POST['member_id'];
    $check_in_time = $_POST['check_in_time'];
    $check_out_time = $_POST['check_out_time'];

    // Insert attendance record into the database
    $query = "INSERT INTO attendance (member_id, check_in_time, check_out_time) 
              VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iss", $member_id, $check_in_time, $check_out_time);

    if ($stmt->execute()) {
        echo "Attendance recorded successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    header("Location: admin_dashboard.php");
    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>
