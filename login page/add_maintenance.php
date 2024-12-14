<?php
// add_maintenance.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Database connection
    $conn = new mysqli("localhost", "root", "", "mini_project_gym");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Retrieve form data
    $equipment_id = $_POST['equipment_id'];
    $maintenance_date = $_POST['maintenance_date'];
    $type = $_POST['type'];
    $description = $_POST['description'];
    $cost = $_POST['cost'];
    $performed_by = $_POST['performed_by'];
    $status = $_POST['status'];

    // Insert into maintenance table
    $sql = "INSERT INTO maintenance (equipment_id, maintenance_date, type, description, cost, performed_by, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssdss", $equipment_id, $maintenance_date, $type, $description, $cost, $performed_by, $status);
    
    if ($stmt->execute()) {
        echo "Maintenance record added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    header("Location: admin_dashboard.php");
    $stmt->close();
    $conn->close();
}
?>
