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
    $confirm_password = $_POST['confirm_password'];
    $plan_id = $_POST['plan_id'];
    $payment_method = $_POST['payment_method'];

    // Validate passwords match
    if ($password !== $confirm_password) {
        throw new Exception("Passwords do not match!");
    }

    // Generate password hash
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Generate unique QR code
    $qr_code = uniqid('GYM_', true);

    // Set default status as 'Active'
    $status = 'Active';

    // Get current date for join_date
    $join_date = date('Y-m-d');

    // Check if email already exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM MEMBER WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("Email already registered!");
    }

    // Get the next available member_id
    $stmt = $conn->query("SELECT COALESCE(MAX(member_id), 0) FROM MEMBER");
    $next_member_id = $stmt->fetchColumn() + 1;

    // Insert into MEMBER table
    $sql = "INSERT INTO MEMBER (member_id, first_name, last_name, email, phone, date_of_birth, 
            emergency_contact, join_date, status, password_hash, qr_code) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $next_member_id,
        $first_name,
        $last_name,
        $email,
        $phone,
        $date_of_birth,
        $emergency_contact,
        $join_date,
        $status,
        $password_hash,
        $qr_code
    ]);

    // Get membership plan details
    $stmt = $conn->prepare("SELECT * FROM MEMBERSHIP_PLAN WHERE plan_id = ?");
    $stmt->execute([$plan_id]);
    $plan = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$plan) {
        throw new Exception("Invalid membership plan selected!");
    }

    // Calculate end date based on plan duration
    $end_date = date('Y-m-d', strtotime($join_date . ' + ' . $plan['duration_months'] . ' months'));

    // Get the next available membership_id
    $stmt = $conn->query("SELECT COALESCE(MAX(membership_id), 0) FROM MEMBERSHIP");
    $next_membership_id = $stmt->fetchColumn() + 1;

    // Insert into MEMBERSHIP table
    $sql = "INSERT INTO MEMBERSHIP (membership_id, member_id, plan_id, start_date, end_date, amount_paid, payment_status, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $next_membership_id,
        $next_member_id,
        $plan_id,
        $join_date,
        $end_date,
        $plan['price'],
        'Paid',
        'Active'
    ]);

    // Get the next available payment_id
    $stmt = $conn->query("SELECT COALESCE(MAX(payment_id), 0) FROM PAYMENT");
    $next_payment_id = $stmt->fetchColumn() + 1;

    // Generate transaction ID
    $transaction_id = uniqid('TXN_', true);

    // Insert into PAYMENT table
    $sql = "INSERT INTO PAYMENT (payment_id, member_id, membership_id, amount, payment_date, payment_method, transaction_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $next_payment_id,
        $next_member_id,
        $next_membership_id,
        $plan['price'],
        $join_date,
        $payment_method,
        $transaction_id
    ]);
    
    // Commit transaction
    $conn->commit();

    // Get complete plan details
$stmt = $conn->prepare("SELECT * FROM MEMBERSHIP_PLAN WHERE plan_id = ?");
$stmt->execute([$plan_id]);
$plan_details = $stmt->fetch(PDO::FETCH_ASSOC);

// Get complete membership details
$stmt = $conn->prepare("SELECT * FROM MEMBERSHIP WHERE membership_id = ?");
$stmt->execute([$next_membership_id]);
$membership_details = $stmt->fetch(PDO::FETCH_ASSOC);

// Get payment details
$stmt = $conn->prepare("SELECT * FROM PAYMENT WHERE payment_id = ?");
$stmt->execute([$next_payment_id]);
$payment_details = $stmt->fetch(PDO::FETCH_ASSOC);

$memberData = [
    // Member Information
    'member_id' => $next_member_id,
    'first_name' => $first_name,
    'last_name' => $last_name,
    'email' => $email,
    'phone' => $phone,
    'join_date' => $join_date,
    'status' => $status,
    
    // Membership Information
    'membership_id' => $next_membership_id,
    'plan_name' => $plan_details['name'],
    'duration_months' => $plan_details['duration_months'],
    'start_date' => $membership_details['start_date'],
    'end_date' => $membership_details['end_date'],
    'class_access_level' => $plan_details['class_access_level'],
    
    // Payment Information
    'payment_id' => $next_payment_id,
    'amount_paid' => $payment_details['amount'],
    'payment_method' => $payment_details['payment_method'],
    'transaction_id' => $payment_details['transaction_id'],
    'payment_date' => $payment_details['payment_date'],
    
    // QR Code Information
    // 'qr_code_path' => $qr_code_path
];
// Send email using the Node.js service
$ch = curl_init('http://localhost:3000/send-confirmation');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($memberData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_status !== 200) {
    // Log email sending error but don't stop the registration process
    error_log("Failed to send confirmation email: " . $response);
}

    // Start session and store success message
    session_start();
    $_SESSION['registration_success'] = true;
    $_SESSION['member_id'] = $next_member_id;

    // Redirect to success page
    header("Location: registration_success.php");
    exit();

} catch(PDOException $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    // Log the error and show user-friendly message
    error_log("Registration Error: " . $e->getMessage());
    header("Location: registration.html?error=registration_failed");
    exit();
} catch(Exception $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    // Handle validation errors
    header("Location: registration.html?error=" . urlencode($e->getMessage()));
    exit();
}

$conn = null;
?>