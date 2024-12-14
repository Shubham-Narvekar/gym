<?php
// Start session and include the database connection
session_start();

// Database connection (using your provided details)
$conn = new mysqli("localhost", "root", "", "mini_project_gym");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php'); // Redirect to login page if not logged in
    exit();
}

// Fetch eligible members whose membership plan has status_trainer = 1
$query = "
    SELECT m.member_id, m.first_name, m.last_name 
    FROM member m
    JOIN membership ms ON m.member_id = ms.member_id
    JOIN membership_plan mp ON ms.plan_id = mp.plan_id
    WHERE mp.status_trainer = 1
";

$result = $conn->query($query);

if (!$result) {
    die('Error fetching eligible members: ' . $conn->error);
}

$eligibleMembers = [];
while ($row = $result->fetch_assoc()) {
    $eligibleMembers[] = $row;
}

// Fetch Member Records
$membersQuery = "SELECT * FROM member";
$membersResult = $conn->query($membersQuery);
if (!$membersResult) {
    die('Error fetching members: ' . $conn->error);
}

// Fetch Payment Records
$paymentsQuery = "
    SELECT 
        payment.payment_id,
        CONCAT(member.first_name, ' ', member.last_name) AS member_name,
        payment.amount,
        payment.payment_date
    FROM 
        payment
    INNER JOIN 
        member 
    ON 
        payment.member_id = member.member_id
";
$paymentsResult = $conn->query($paymentsQuery);

// Fetch Membership Records
$membershipsQuery = "SELECT 
        membership.membership_id,
        member.first_name AS member_first_name,
        member.last_name AS member_last_name,
        membership_plan.name AS plan_name,
        membership_plan.price,
        membership_plan.duration_months
    FROM 
        membership
    INNER JOIN 
        membership_plan 
    ON 
        membership.plan_id = membership_plan.plan_id
    INNER JOIN 
        member
    ON 
        membership.member_id = member.member_id";
$membershipsResult = $conn->query($membershipsQuery);
if (!$membershipsResult) {
    die('Error fetching memberships: ' . $conn->error);
}

$members = [];
if ($membersResult) {
    while ($row = $membersResult->fetch_assoc()) {
        $members[] = $row;
    }
}

$payments = [];
if ($paymentsResult) {
    while ($row = $paymentsResult->fetch_assoc()) {
        $payments[] = $row;
    }
}

$memberships = [];
if ($membershipsResult) {
    while ($row = $membershipsResult->fetch_assoc()) {
        $memberships[] = $row;
    }
}

echo '<script>';
echo 'var members = ' . json_encode($members) . ';';
echo 'var payments = ' . json_encode($payments) . ';';
echo 'var memberships = ' . json_encode($memberships) . ';';
echo '</script>';

$trainers = [];
$trainersQuery = "SELECT * FROM trainer";
$trainersResult = $conn->query($trainersQuery);

if ($trainersResult) {
    while ($row = $trainersResult->fetch_assoc()) {
        $trainers[] = $row;
    }
}


?>

<script>
    const trainers = <?php echo json_encode($trainers); ?>;
</script>


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"> <!-- Font Awesome -->
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
    <link rel="stylesheet" href="admin_dashboard_style.css"> <!-- Add your CSS file here -->
    <style>
        
    </style>
</head>
<body>
     <!-- Sidebar -->
     <div class="sidebar">
        <h2 style="color:white; text-align: center;">Admin Dashboard</h2>
        <a href="logout.php" class="logout"><i class="fa fa-sign-out-alt"></i> Logout</a>
        <a onclick="showSection('members')"><i class="fa fa-users"></i> Member Records</a>
        <a onclick="showSection('payments')"><i class="fa fa-credit-card"></i> Payment Records</a>
        <a onclick="showSection('memberships')"><i class="fa fa-gift"></i> Membership Records</a>
        <a onclick="showSection('trainers')"><i class="fa fa-chalkboard-teacher"></i> Trainer Records</a>
        <a onclick="showSection('equipment')"><i class="fa fa-cogs"></i> Equipment Records</a>
        <a onclick="showSection('maintenance')"><i class="fa fa-wrench"></i> Maintenance Records</a>
        <a onclick="showSection('attendanceRecordsSection')"><i class="fa fa-check-circle"></i> Attendance Records</a>
        <a onclick="showSection('add_trainer')"><i class="fa fa-user-plus"></i> Add New Trainer</a>
        <a onclick="showSection('classSchedule')"><i class="fa fa-calendar-alt"></i> Class Schedule</a>
        <a onclick="showSection('add_equipment')"><i class="fa fa-cogs"></i> Add Equipment Records</a>
        <a onclick="showSection('add_maintenance')"><i class="fa fa-tools"></i> Add Maintenance Records</a>
        <a onclick="showSection('attendanceSection')"><i class="fa fa-check-circle"></i> Attendance</a>
        <a onclick="showSection('training_sessions')"><i class="fa fa-user-graduate"></i> Training Sessions</a>
        
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1>Admin Dashboard</h1>

        <!-- Member Records Section -->
        <div class="dashboard-section" id="members">
            <h2>Member Records</h2>
            <button class="download-btn" onclick="downloadPDFMembers()">Download PDF</button>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Join Date</th>
                    </tr>
                </thead>
                <tbody id="memberData">
    
                </tbody>
            </table>
        </div>

        <!-- Payment Records Section -->
        <div class="dashboard-section hidden" id="payments">
            <h2>Payment Records</h2>
            <button class="download-btn" onclick="downloadPDFPayments()">Download PDF</button>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Member Name</th>
                        <th>Amount</th>
                        <th>Payment Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="paymentData"></tbody>
            </table>
        </div>

        <!-- Membership Records Section -->
<div class="dashboard-section hidden" id="memberships">
    <h2>Membership Records</h2>
    <button class="download-btn" onclick="downloadPDFMemberships()">Download PDF</button>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Member Name</th>
                <th>Plan Name</th>
                <th>Price</th>
                <th>Duration (Months)</th>
            </tr>
        </thead>
        <tbody id="membershipData">
        
        </tbody>
    </table>
</div>

    <!-- Add New Trainer Section -->
<div class="dashboard-section hidden" id="add_trainer">
    <h2>Add New Trainer</h2>
    <form class="trainer-form" id="trainerForm" method="POST" action="add_trainer.php">
        <label for="first_name">First Name</label>
        <input type="text" id="first_name" name="first_name" required><br>

        <label for="last_name">Last Name</label>
        <input type="text" id="last_name" name="last_name" required><br>

        <label for="specialization">Specialization</label>
        <input type="text" id="specialization" name="specialization" required><br>

        <label for="certification">Certification</label>
        <input type="text" id="certification" name="certification" required><br>

        <label for="phone">Phone</label>
        <input type="text" id="phone" name="phone" required><br>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required><br>

        <label for="hourly_rate">Hourly Rate</label>
        <input type="number" id="hourly_rate" name="hourly_rate" required step="0.01"><br>

        <label for="availability">Availability</label>
        <input type="text" id="availability" name="availability" required><br>

        <button type="submit" class="submit-btn">Add Trainer</button>
    </form>
</div>
    <!-- Trainer Records Section -->
<div class="dashboard-section hidden" id="trainers">
    <h2>Trainer Records</h2>
    <button class="download-btn" onclick="downloadPDFTrainers()">
        <i class="fa fa-download"></i> Download Trainer Records
    </button>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Specialization</th>
                <th>Certification</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Hourly Rate</th>
                <th>Availability</th>
            </tr>
        </thead>
        <tbody id="trainer-records-table-body">
            <!-- Trainer records will be populated dynamically -->
        </tbody>
    </table>
</div>
<div class="dashboard-section hidden" id="classSchedule">
    <h2>Class Schedule</h2>
    
    <form action="add_class_schedule.php" method="POST" class="class-schedule-form">
        <div class="form-group">
            <label for="name">Class Name:</label>
            <input type="text" id="name" name="name" required>
        </div>
        
        <div class="form-group">
            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="4" required></textarea>
        </div>
        
        <div class="form-group">
            <label for="trainer_id">Trainer:</label>
            <select id="trainer_id" name="trainer_id" required>
                <?php
                // Fetch trainers from the database
                $trainers = $conn->query("SELECT trainer_id, CONCAT(first_name, ' ', last_name) AS name FROM TRAINER");
                while ($trainer = $trainers->fetch_assoc()) {
                    echo "<option value='{$trainer['trainer_id']}'>{$trainer['name']}</option>";
                }
                ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="schedule_time">Schedule Time:</label>
            <input type="datetime-local" id="schedule_time" name="schedule_time" required>
        </div>
        
        <div class="form-group">
            <label for="duration_minutes">Duration (Minutes):</label>
            <input type="number" id="duration_minutes" name="duration_minutes" required>
        </div>
        
        <div class="form-group">
            <label for="capacity">Capacity:</label>
            <input type="number" id="capacity" name="capacity" required>
        </div>
        
        <div class="form-group">
            <label for="room">Room:</label>
            <input type="text" id="room" name="room" required>
        </div>
        
        <div class="form-group">
            <label for="status">Status:</label>
            <select id="status" name="status" required>
                <option value="Scheduled">Scheduled</option>
                <option value="Cancelled">Cancelled</option>
            </select>
        </div>
        
        <button type="submit" class="button">Add Class</button>
    </form>
</div>
    <!-- Equipment Form Section -->
<div class="dashboard-section hidden" id="add_equipment">
    <h2>Add New Equipment</h2>
    <form class="equipment-form" action="add_equipment.php" method="POST">
        <label for="name">Equipment Name</label>
        <input type="text" id="name" name="name" required>

        <label for="category">Category</label>
        <input type="text" id="category" name="category" required>

        <label for="manufacturer">Manufacturer</label>
        <input type="text" id="manufacturer" name="manufacturer" required>

        <label for="purchase_date">Purchase Date</label>
        <input type="date" id="purchase_date" name="purchase_date" max="" required>

        <label for="status">Status</label>
        <input type="text" id="status" name="status" required>

        <label for="maintenance_frequency">Maintenance Frequency (in days)</label>
        <input type="number" id="maintenance_frequency" name="maintenance_frequency_days" required>

        <button type="submit">Add Equipment</button>
    </form>
</div>
<div id="add_maintenance" class="dashboard-section hidden">
    <h3>Add Maintenance Record</h3>
    <form action="add_maintenance.php" method="POST" class="maintenance-form">
        <label for="equipment_id">Equipment</label>
        <select name="equipment_id" id="equipment_id" required>
            <!-- This will dynamically load equipment names from the database -->
            <!-- Populate options dynamically using PHP -->
            <?php
                // Fetch equipment options from the database
                $result = $conn->query("SELECT * FROM equipment");
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='{$row['equipment_id']}'>{$row['name']}</option>";
                }
            ?>
        </select>

        <label for="maintenance_date">Maintenance Date</label>
        <input type="date" name="maintenance_date" id="maintenance_date" max="" required>

        <label for="type">Maintenance Type</label>
        <input type="text" name="type" id="type" required>

        <label for="description">Description</label>
        <textarea name="description" id="description" required></textarea>

        <label for="cost">Cost</label>
        <input type="number" step="0.01" name="cost" id="cost" required>

        <label for="performed_by">Performed By</label>
        <input type="text" name="performed_by" id="performed_by" required>

        <label for="status">Status</label>
        <select name="status" id="status" required>
            <option value="Completed">Completed</option>
            <option value="Pending">Pending</option>
            <option value="Scheduled">Scheduled</option>
        </select>

        <button type="submit">Add Maintenance Record</button>
    </form>
</div>

    <!-- Equipment Records Section -->
<div id="equipment" class="dashboard-section hidden" >
    <h2>Equipment Records</h2>
    <button class="download-btn" id="downloadEquipmentPDF" onclick="downloadEquipmentPDF()">Download PDF</button>
    <table>
        <thead>
            <tr>
                <th>Equipment Name</th>
                <th>Category</th>
                <th>Manufacturer</th>
                <th>Purchase Date</th>
                <th>Status</th>
                <th>Maintenance Frequency (Days)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Fetch equipment records from the database
            $result = $conn->query("SELECT * FROM equipment");
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['name']}</td>
                        <td>{$row['category']}</td>
                        <td>{$row['manufacturer']}</td>
                        <td>{$row['purchase_date']}</td>
                        <td>{$row['status']}</td>
                        <td>{$row['maintenance_frequency_days']}</td>
                    </tr>";
            }
            ?>
        </tbody>
    </table>
</div>
    
    <!-- Maintenance Records Section -->
<div id="maintenance" class="dashboard-section hidden" >
    <h2>Maintenance Records</h2>
    <button class="download-btn" id="downloadMaintenancePDF" onclick="downloadMaintenancePDF()">Download PDF</button>
    <table>
        <thead>
            <tr>
                <th>Equipment</th>
                <th>Maintenance Date</th>
                <th>Type</th>
                <th>Description</th>
                <th>Cost</th>
                <th>Performed By</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Fetch maintenance records from the database
            $result = $conn->query("SELECT maintenance.*, equipment.name AS equipment_name 
                                    FROM maintenance 
                                    JOIN equipment ON maintenance.equipment_id = equipment.equipment_id");
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['equipment_name']}</td>
                        <td>{$row['maintenance_date']}</td>
                        <td>{$row['type']}</td>
                        <td>{$row['description']}</td>
                        <td>{$row['cost']}</td>
                        <td>{$row['performed_by']}</td>
                        <td>{$row['status']}</td>
                    </tr>";
            }
            ?>
        </tbody>
    </table>
</div>
    
    <!-- Attendance Section -->
<div id="attendanceSection" class="dashboard-section hidden" >
    <h2>Record Attendance</h2>
    
    <!-- Attendance Form -->
    <form id="attendanceForm" class="maintenance-form" method="POST" action="process_attendance.php">
        <label for="member">Select Member:</label>
        <select id="member" name="member_id" required>
            <option value="" disabled selected>Select Member</option>
            <?php
                // Fetch all members for the attendance form
                $result = $conn->query("SELECT member_id, CONCAT(first_name, ' ', last_name) AS member_name FROM member");
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='{$row['member_id']}'>{$row['member_name']}</option>";
                }
            ?>
        </select>
        
        <label for="check_in_time">Check-In Time:</label>
        <input type="datetime-local" id="check_in_time" name="check_in_time" max="" required>

        <label for="check_out_time">Check-Out Time:</label>
        <input type="datetime-local" id="check_out_time" name="check_out_time" max="" required>
        
        <button type="submit">Submit Attendance</button>
    </form>
</div>

    <!-- Attendance Records Section -->
<div id="attendanceRecordsSection" class="dashboard-section hidden" >
    <h2>Attendance Records</h2>
    <button class="download-btn" onclick="downloadAttendancePDF()">Download PDF</button>
    <table id="attendanceTable">
        <thead>
            <tr>
                <th>Member Name</th>
                <th>Check-In Time</th>
                <th>Check-Out Time</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Fetch attendance records from the database
            $result = $conn->query("SELECT attendance.*, CONCAT(member.first_name, ' ', member.last_name) AS member_name 
                                    FROM attendance
                                    JOIN member ON attendance.member_id = member.member_id");

            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['member_name']}</td>
                        <td>{$row['check_in_time']}</td>
                        <td>{$row['check_out_time']}</td>
                    </tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<div id="training_sessions" class="dashboard-section hidden " >
    <h2>Add Training Session</h2>
    <form method="POST" class="maintenance-form" action="process_training_session.php">
        <!-- Member Selection -->
        <label for="member">Select Member</label>
        <select name="member_id" id="member" required>
            <?php foreach ($eligibleMembers as $member): ?>
                <option value="<?php echo $member['member_id']; ?>">
                    <?php echo $member['first_name'] . ' ' . $member['last_name']; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Trainer Selection -->
        <label for="trainer">Select Trainer</label>
        <select name="trainer_id" id="trainer" required>
            <!-- You can populate this with trainer data -->
            <!-- Example, fetching trainer data -->
            <?php
            // Fetch trainer data
            $trainerQuery = "SELECT trainer_id, first_name, last_name FROM trainer";
            $trainerResult = $conn->query($trainerQuery);
            while ($trainer = $trainerResult->fetch_assoc()):
            ?>
                <option value="<?php echo $trainer['trainer_id']; ?>">
                    <?php echo $trainer['first_name'] . ' ' . $trainer['last_name']; ?>
                </option>
            <?php endwhile; ?>
        </select>

        <!-- Session Time -->
        <label for="session_time">Session Time</label>
        <input type="datetime-local" id="session_time" name="session_time" required>

        <!-- Duration -->
        <label for="duration">Duration (minutes)</label>
        <input type="number" id="duration" name="duration_minutes" required>

        <!-- Notes -->
        <label for="notes">Notes</label>
        <textarea id="notes" name="notes" rows="4" required></textarea>

        <!-- Amount Charged -->
        <label for="amount_charged">Amount Charged</label>
        <input type="number" id="amount_charged" name="amount_charged" step="0.01" required>

        <!-- Submit Button -->
        <button type="submit">Add Training Session</button>
    </form>
</div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
    // Function to populate the members table
    function populateMembersTable() {
        const tableBody = document.getElementById('memberData');
        tableBody.innerHTML = ''; // Clear existing rows (if any)

        members.forEach(member => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${member.member_id}</td>
                <td>${member.first_name} ${member.last_name}</td>
                <td>${member.email}</td>
                <td>${member.phone}</td>
                <td>${member.join_date}</td>
            `;
            tableBody.appendChild(row);
        });
    }

    function populatePaymentsTable() {
    const tableBody = document.getElementById('paymentData');
    tableBody.innerHTML = ''; // Clear existing rows

    if (Array.isArray(payments) && payments.length > 0) {
        payments.forEach(payment => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${payment.payment_id}</td>
                <td>${payment.member_name}</td> <!-- Use Member Name -->
                <td>${payment.amount}</td>
                <td>${payment.payment_date}</td>
                <td>
                    <button class="download-receipt-btn" onclick="downloadReceipt(${payment.payment_id})" title="Download Receipt">
                        <i class="fa fa-download"></i>
                    </button>
                </td>
            `;
            tableBody.appendChild(row);
        });
    } else {
        tableBody.innerHTML = `<tr><td colspan="5">No payments found.</td></tr>`;
    }
}


    // Populate Memberships Table
    function populateMembershipsTable() {
        const tableBody = document.getElementById('membershipData');
        tableBody.innerHTML = ''; // Clear existing rows

        if (Array.isArray(memberships) && memberships.length > 0) {
            memberships.forEach(membership => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${membership.membership_id}</td>
                    <td>${membership.member_first_name} ${membership.member_last_name}</td>
                    <td>${membership.plan_name}</td>
                    <td>${membership.price}</td>
                    <td>${membership.duration_months}</td>
                `;
                tableBody.appendChild(row);
            });
        } else {
            tableBody.innerHTML = `<tr><td colspan="5">No memberships found.</td></tr>`;
        }
    }

    function populateTrainerRecords() {
    const tableBody = document.getElementById('trainer-records-table-body');
    tableBody.innerHTML = ''; // Clear existing rows

    trainers.forEach((trainer) => {
        const row = document.createElement('tr');

        row.innerHTML = `
            <td>${trainer.trainer_id}</td>
            <td>${trainer.first_name} ${trainer.last_name}</td>
            <td>${trainer.specialization}</td>
            <td>${trainer.certification}</td>
            <td>${trainer.phone}</td>
            <td>${trainer.email}</td>
            <td>${trainer.hourly_rate}</td>
            <td>${trainer.availability}</td>
        `;

        tableBody.appendChild(row);
    });
}

    // Populate members table on page load
    populateMembersTable();
    populatePaymentsTable();
    populateMembershipsTable();
    populateTrainerRecords();
});

        // Function to download Member Records PDF
function downloadPDFMembers() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    const members = <?php echo json_encode($members); ?>;
    
    doc.setFontSize(20);
    doc.text('FitCore Gym - Members Report', 15, 20);
    
    const tableColumn = ["ID", "Name", "Email", "Phone", "Join Date"];
    const tableRows = [];

    members.forEach(member => {
        const memberData = [
            member.member_id, // ID
            `${member.first_name} ${member.last_name}`, // Name
            member.email, // Email
            member.phone, // Phone
            member.join_date // Join Date
        ];
        tableRows.push(memberData);
    });

    doc.autoTable({
        head: [tableColumn],
        body: tableRows,
        startY: 30,
        theme: 'grid',
        styles: {
            fontSize: 10,
            cellPadding: 3,
            overflow: 'linebreak'
        },
        columnStyles: {
            0: { cellWidth: 20 },
            1: { cellWidth: 40 },
            2: { cellWidth: 50 },
            3: { cellWidth: 30 },
            4: { cellWidth: 30 }
        }
    });

    doc.save('members_report.pdf');
}

// Function to download Payment Records PDF
function downloadPDFPayments() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    const payments = <?php echo json_encode($payments); ?>;
    
    doc.setFontSize(20);
    doc.text('FitCore Gym - Payment Records Report', 15, 20);
    
    const tableColumn = ["Payment ID", "Member ID", "Amount", "Payment Date"];
    const tableRows = [];

    payments.forEach(payment => {
        const paymentData = [
            payment.payment_id,
            payment.member_id,
            payment.amount,
            payment.payment_date
        ];
        tableRows.push(paymentData);
    });

    doc.autoTable({
        head: [tableColumn],
        body: tableRows,
        startY: 30,
        theme: 'grid',
        styles: {
            fontSize: 10,
            cellPadding: 3,
            overflow: 'linebreak'
        },
        columnStyles: {
            0: { cellWidth: 40 },
            1: { cellWidth: 30 },
            2: { cellWidth: 30 },
            3: { cellWidth: 40 }
        }
    });

    doc.save('payments_report.pdf');
}

// Function to download Membership Records PDF
function downloadPDFMemberships() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    const memberships = <?php echo json_encode($memberships); ?>;
    
    doc.setFontSize(20);
    doc.text('FitCore Gym - Membership Records Report', 15, 20);
    
    const tableColumn = ["Membership ID", "Member Name", "Plan Name", "Price", "Duration (Months)"];
    const tableRows = [];

    memberships.forEach(membership => {
        const membershipData = [
            membership.membership_id,
            `${membership.member_first_name} ${membership.member_last_name}`,
            membership.plan_name,
            membership.price,
            membership.duration_months
        ];
        tableRows.push(membershipData);
    });

    doc.autoTable({
        head: [tableColumn],
        body: tableRows,
        startY: 30,
        theme: 'grid',
        styles: {
            fontSize: 10,
            cellPadding: 3,
            overflow: 'linebreak'
        },
        columnStyles: {
            0: { cellWidth: 30 },
            1: { cellWidth: 50 },
            2: { cellWidth: 50 },
            3: { cellWidth: 30 },
            4: { cellWidth: 30 }
        }
    });

    doc.save('memberships_report.pdf');
}
function downloadReceipt(paymentId) {
    const { jsPDF } = window.jspdf;

    const payment = payments.find(p => p.payment_id == paymentId);
    if (!payment) {
        alert('Payment not found!');
        return;
    }

    const doc = new jsPDF();

    // Receipt Header
    doc.setFontSize(18);
    doc.text('FitCore Gym - Payment Receipt', 20, 20);

    // Payment Details
    doc.setFontSize(12);
    doc.text(`Payment ID: ${payment.payment_id}`, 20, 40);
    doc.text(`Member Name: ${payment.member_name}`, 20, 50); // Use Member Name
    doc.text(`Amount Paid: ${payment.amount}`, 20, 60);
    doc.text(`Payment Date: ${payment.payment_date}`, 20, 70);

    // Footer
    doc.setFontSize(10);
    doc.text('Thank you for your payment!', 20, 90);

    // Save PDF
    doc.save(`payment_receipt_${payment.member_name}.pdf`);
}

// Function to generate and download Trainer Records PDF
function downloadPDFTrainers() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    // Title
    doc.setFontSize(18);
    doc.text('FitCore Gym - Trainer Records Report', 15, 20);

    // Table Columns
    const tableColumns = [
        "ID",
        "Name",
        "Specialization",
        "Certification",
        "Phone",
        "Email",
        "Hourly Rate",
        "Availability"
    ];

    // Table Rows
    const tableRows = trainers.map(trainer => [
        trainer.trainer_id,
        `${trainer.first_name} ${trainer.last_name}`,
        trainer.specialization,
        trainer.certification,
        trainer.phone,
        trainer.email,
        `$${trainer.hourly_rate}`,
        trainer.availability
    ]);

    // Add Table to PDF
    doc.autoTable({
        head: [tableColumns],
        body: tableRows,
        startY: 30,
        theme: 'grid',
        styles: {
            fontSize: 8,
            cellPadding: 2,
            overflow: 'linebreak'
        },
        columnStyles: {
            0: { cellWidth: 10 },  // ID
            1: { cellWidth: 25 }, // Name
            2: { cellWidth: 30 }, // Specialization
            3: { cellWidth: 30 }, // Certification
            4: { cellWidth: 25 }, // Phone
            5: { cellWidth: 35 }, // Email
            6: { cellWidth: 15 }, // Hourly Rate
            7: { cellWidth: 25 }  // Availability
        },
        tableWidth: 'auto', // Automatically adjust table width
        margin: { left: 10, right: 10 } // Add margins to prevent cutoff
    });

    // Save PDF
    doc.save('trainer_records_report.pdf');
}

    // Function to generate Equipment Records PDF
function downloadEquipmentPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    const rows = [];
    const headers = ["Equipment Name", "Category", "Manufacturer", "Purchase Date", "Status", "Maintenance Frequency (Days)"];

    // Get the table rows for equipment
    const tableRows = document.querySelectorAll("#equipment table tbody tr");
    tableRows.forEach(row => {
        const rowData = Array.from(row.cells).map(cell => cell.textContent);
        rows.push(rowData);
    });

    // Add the title and table to the PDF
    doc.setFontSize(20);
    doc.text("Equipment Records", 15, 20);

    doc.autoTable({
        head: [headers],
        body: rows,
        startY: 30,
        theme: 'grid',
        styles: {
            fontSize: 10,
            cellPadding: 3,
            overflow: 'linebreak'
        },
        columnStyles: {
            0: { cellWidth: 30 },
            1: { cellWidth: 25 },
            2: { cellWidth: 30 },
            3: { cellWidth: 30 },
            4: { cellWidth: 25 },
            5: { cellWidth: 40 }
        }
    });

    doc.save('equipment_records.pdf');
}


// Function to generate Maintenance Records PDF
function downloadMaintenancePDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    const rows = [];
    const headers = ["Equipment", "Maintenance Date", "Type", "Description", "Cost", "Performed By", "Status"];

    // Get the table rows for maintenance records
    const tableRows = document.querySelectorAll("#maintenance table tbody tr");

    if (tableRows.length === 0) {
        console.error("No rows found in the maintenance table.");
    } else {
        tableRows.forEach(row => {
            const rowData = Array.from(row.cells).map(cell => cell.textContent.trim());
            rows.push(rowData);
        });

        // Add the title and table to the PDF
        doc.setFontSize(20);
        doc.text("Maintenance Records", 15, 20);

        doc.autoTable({
            head: [headers],
            body: rows,
            startY: 30,
            theme: 'grid',
            styles: {
                fontSize: 10,
                cellPadding: 3,
                overflow: 'linebreak'
            },
            columnStyles: {
                0: { cellWidth: 30 },
                1: { cellWidth: 25 },
                2: { cellWidth: 25 },
                3: { cellWidth: 40 },
                4: { cellWidth: 20 },
                5: { cellWidth: 25 },
                6: { cellWidth: 25 }
            }
        });

        doc.save('maintenance_records.pdf');
    }
}

    // Function to download attendance records as PDF
function downloadAttendancePDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    const table = document.getElementById("attendanceTable");

    const tableRows = [];
    for (let i = 1; i < table.rows.length; i++) { // Skip header row
        const row = table.rows[i];
        const rowData = [];
        
        // Loop through each cell in the row
        for (let j = 0; j < row.cells.length; j++) {
            rowData.push(row.cells[j].innerText); // Extract text content from cells
        }
        
        tableRows.push(rowData);
    }

    doc.setFontSize(18);
    doc.text("Attendance Records", 14, 20);

    // Set column names (Header)
    const columns = ["Member Name", "Check-In Time", "Check-Out Time"];

    // Adding table with dynamic data
    doc.autoTable({
        head: [columns],
        body: tableRows,
        startY: 30,
        theme: 'grid',
        styles: {
            fontSize: 10,
            cellPadding: 3,
            overflow: 'linebreak'
        },
        columnStyles: {
            0: { cellWidth: 50 },
            1: { cellWidth: 60 },
            2: { cellWidth: 60 }
        }
    });

    // Save the generated PDF
    doc.save('attendance_records.pdf');
}

 </script>
    <script src="admin_dashboard.js"></script>
</body>
</html>
