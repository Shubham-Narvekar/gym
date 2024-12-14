<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mini_project_gym";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Start transaction
    $conn->beginTransaction();

    // Get form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $date_of_birth = $_POST['date_of_birth'];
    $emergency_contact = $_POST['emergency_contact'];
    $password = $_POST['password'];
    $plan_id = $_POST['plan_id'];
    
    // Validate plan exists and get price
    $stmt = $conn->prepare("SELECT price FROM MEMBERSHIP_PLAN WHERE plan_id = ?");
    $stmt->execute([$plan_id]);
    $plan = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$plan) {
        throw new Exception("Invalid membership plan selected!");
    }

    // Generate password hash
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Generate unique membership ID
    $membership_id = uniqid('GYM_', true);

    // Insert member data
    $sql = "INSERT INTO MEMBER (first_name, last_name, email, phone, date_of_birth, 
            emergency_contact, password_hash, membership_id, plan_id, join_date, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_DATE, 'Active')";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $first_name,
        $last_name,
        $email,
        $phone,
        $date_of_birth,
        $emergency_contact,
        $password_hash,
        $membership_id,
        $plan_id
    ]);
    
    $member_id = $conn->lastInsertId();

    // Record the payment
    $sql = "INSERT INTO PAYMENT (member_id, plan_id, amount, payment_status) 
            VALUES (?, ?, ?, 'completed')";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $member_id,
        $plan_id,
        $plan['price']
    ]);

    // Commit transaction
    $conn->commit();

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful!',
        'membership_id' => $membership_id
    ]);

} catch(PDOException $e) {
    // Rollback transaction on error
    if ($conn) {
        $conn->rollBack();
    }
    
    error_log("Registration Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Registration failed. Please try again later.'
    ]);
} catch(Exception $e) {
    // Rollback transaction on error
    if ($conn) {
        $conn->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    $conn = null;
}
?>