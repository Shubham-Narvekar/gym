<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "mini_project_gym");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $name = $_POST['name'];
    $category = $_POST['category'];
    $manufacturer = $_POST['manufacturer'];
    $purchase_date = $_POST['purchase_date'];
    $status = $_POST['status'];
    $maintenance_frequency_days = $_POST['maintenance_frequency_days'];

    // SQL query to insert equipment into the database
    $sql = "INSERT INTO EQUIPMENT (name, category, manufacturer, purchase_date, status, maintenance_frequency_days) 
            VALUES ('$name', '$category', '$manufacturer', '$purchase_date', '$status', '$maintenance_frequency_days')";

    if ($conn->query($sql) === TRUE) {
        echo "New equipment added successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    header("Location: admin_dashboard.php");
    // Close the connection
    $conn->close();
}
?>
