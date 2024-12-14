<?php
// Start session
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mini_project_gym";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch logged-in member details
$member_id = $_SESSION['member_id']; // Assume member_id is stored in session after login

// Fetch member details
$member_sql = "SELECT * FROM MEMBER WHERE member_id = ?";
$stmt = $conn->prepare($member_sql);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$member_result = $stmt->get_result();
$member = $member_result->fetch_assoc();

// Fetch membership details
$membership_sql = "SELECT mp.name AS plan_name, ms.start_date, ms.end_date, ms.amount_paid, ms.payment_status 
                   FROM MEMBERSHIP ms 
                   JOIN MEMBERSHIP_PLAN mp ON ms.plan_id = mp.plan_id 
                   WHERE ms.member_id = ?";
$stmt = $conn->prepare($membership_sql);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$membership_result = $stmt->get_result();
$membership = $membership_result->fetch_assoc();

// Fetch attendance details
$attendance_sql = "SELECT COUNT(*) AS total_attendance FROM ATTENDANCE WHERE member_id = ?";
$stmt = $conn->prepare($attendance_sql);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$attendance_result = $stmt->get_result();
$attendance = $attendance_result->fetch_assoc();

// Fetch training session details
$training_sql = "SELECT COUNT(*) AS total_sessions FROM TRAINING_SESSION WHERE member_id = ?";
$stmt = $conn->prepare($training_sql);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$training_result = $stmt->get_result();
$training = $training_result->fetch_assoc();

// Fetch scheduled classes
$class_sql = "SELECT * FROM CLASS_SCHEDULE WHERE status = 'Scheduled' AND schedule_time > NOW()";
$class_result = $conn->query($class_sql);

// Handle class booking
if (isset($_POST['book_class'])) {
    $class_id = $_POST['class_id'];

    // Check if member already booked the class
    $check_booking_sql = "SELECT * FROM CLASS_BOOKING WHERE class_id = ? AND member_id = ?";
    $stmt = $conn->prepare($check_booking_sql);
    $stmt->bind_param("ii", $class_id, $member_id);
    $stmt->execute();
    $booking_result = $stmt->get_result();

    if ($booking_result->num_rows > 0) {
        echo "<script>alert('You have already booked this class.');</script>";
    } else {
        // Book the class for the member
        $booking_time = date("Y-m-d H:i:s");
        $status = 'Approved'; // You can change this based on your booking approval system
        $attendance_status = 'Not Attended';

        $booking_sql = "INSERT INTO CLASS_BOOKING (class_id, member_id, booking_time, status, attendance_status) 
                        VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($booking_sql);
        $stmt->bind_param("iisss", $class_id, $member_id, $booking_time, $status, $attendance_status);
        $stmt->execute();

        echo "<script>alert('Class booked successfully!');</script>";
    }
}

$stmt->close();
$conn->close();

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: member_login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f4f9;
        }
        .dashboard-container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
        }
        .logout-container {
            text-align: right;
            margin-bottom: 20px;
        }
        .logout-container form {
            display: inline-block;
        }
        .logout-container button {
            background-color: #ff4b5c;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .logout-container button:hover {
            background-color: #e0434f;
        }
        .card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }
        .card-header {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .card-content {
            font-size: 16px;
            color: #555;
        }
        .card i {
            margin-right: 10px;
            color: #007bff;
        }
        .stats-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .stat-card {
            flex: 1;
            min-width: 250px;
            background: linear-gradient(135deg, #6e8efb, #a777e3);
            color: #fff;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            animation: fadeInUp 0.8s;
        }
        .stat-card h2 {
            font-size: 2.5rem;
            margin: 0;
        }
        .stat-card p {
            margin: 0;
            font-size: 1.2rem;
        }

        .booking-form {
            margin-top: 30px;
        }
        .booking-form label {
            font-size: 16px;
            color: #333;
            display: block;
            margin-bottom: 5px;
        }
        .booking-form select,
        .booking-form button {
            padding: 10px;
            font-size: 16px;
            width: 100%;
            border-radius: 5px;
            border: 1px solid #ddd;
            margin-bottom: 15px;
        }
        .booking-form button {
            background-color: #6e8efb;
            color: #fff;
            cursor: pointer;
        }
        .booking-form button:hover {
            background-color: #4c6bdb;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Logout Button -->
        <div class="logout-container">
            <form method="POST">
                <button type="submit" name="logout"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </form>
        </div>
        <!-- Profile Card -->
        <div class="card animate__animated animate__fadeInLeft">
            <div class="card-header"><i class="fas fa-user-circle"></i> Member Profile</div>
            <div class="card-content">
                <p><strong>Name:</strong> <?= $member['first_name'] . " " . $member['last_name'] ?></p>
                <p><strong>Email:</strong> <?= $member['email'] ?></p>
                <p><strong>Phone:</strong> <?= $member['phone'] ?></p>
                <p><strong>Join Date:</strong> <?= $member['join_date'] ?></p>
                <p><strong>Status:</strong> <?= $member['status'] ?></p>
            </div>
        </div>

        <!-- Membership Details Card -->
        <div class="card animate__animated animate__fadeInRight">
            <div class="card-header"><i class="fas fa-id-card-alt"></i> Membership Details</div>
            <div class="card-content">
                <p><strong>Plan Name:</strong> <?= $membership['plan_name'] ?></p>
                <p><strong>Start Date:</strong> <?= $membership['start_date'] ?></p>
                <p><strong>End Date:</strong> <?= $membership['end_date'] ?></p>
                <p><strong>Amount Paid:</strong> <?= $membership['amount_paid'] ?></p>
                <p><strong>Payment Status:</strong> <?= $membership['payment_status'] ?></p>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="stats-container">
            <div class="stat-card animate__animated animate__bounceIn">
                <h2><?= $attendance['total_attendance'] ?></h2>
                <p>Total Attendance</p>
            </div>
            <div class="stat-card animate__animated animate__bounceIn">
                <h2><?= $training['total_sessions'] ?></h2>
                <p>Training Sessions</p>
            </div>
        </div>

         <!-- Class Booking Form -->
         <div class="card animate__animated animate__fadeInUp">
            <div class="card-header"><i class="fas fa-calendar-check"></i> Book a Class</div>
            <div class="card-content">
                <form method="POST" class="booking-form">
                    <label for="class_id">Select a Class:</label>
                    <select name="class_id" id="class_id" required>
                        <option value="">-- Select Class --</option>
                        <?php
                        // Fetch classes
                        if ($class_result->num_rows > 0) {
                            while ($class = $class_result->fetch_assoc()) {
                                echo "<option value='" . $class['class_id'] . "'>" . $class['name'] . " - " . $class['schedule_time'] . "</option>";
                            }
                        } else {
                            echo "<option value=''>No classes available</option>";
                        }
                        ?>
                    </select>
                    <button type="submit" name="book_class"><i class="fas fa-book"></i> Book Class</button>
                </form>
            </div>
        </div>

    </div>
</body>
</html>
