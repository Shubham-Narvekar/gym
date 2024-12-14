<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mini_project_gym";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch membership plans from database
    $stmt = $conn->query("SELECT * FROM MEMBERSHIP_PLAN ORDER BY price");
    $membership_plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Fitcore Gym - Registration Page</title>
        <link rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <link rel="stylesheet" href="registration_style.css">
    </head>
    <body>
        <button class="back-button"
            onclick="window.location.href='login_selection.html'">
            <i class="fas fa-arrow-left"></i> Back to Login Selection
        </button>

        <div class="registration-form-container">
            <div class="registration-form">
                <i class="fas fa-user-plus form-icon"></i>
                <h2>Member Registration</h2>

                <form action="http://localhost:3000/register" method="POST"
                    id="registrationForm">
                    <!-- Personal Information Section -->
                    <h3>Personal Information</h3>
                    <div class="input-row">
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" name="first_name"
                                placeholder="First Name" required>
                        </div>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" name="last_name"
                                placeholder="Last Name" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" placeholder="Email"
                            required>
                    </div>

                    <div class="input-group">
                        <i class="fas fa-phone"></i>
                        <input type="tel" name="phone"
                            placeholder="Phone Number" required>
                    </div>

                    <div class="input-group">
                        <i class="fas fa-calendar"></i>
                        <input type="date" name="date_of_birth" required>
                    </div>

                    <div class="input-group">
                        <i class="fas fa-phone-volume"></i>
                        <input type="text" name="emergency_contact"
                            placeholder="Emergency Contact (Name & Phone)"
                            required>
                    </div>

                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password"
                            placeholder="Password" required>
                    </div>

                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="confirm_password"
                            placeholder="Confirm Password" required>
                    </div>

                    <h3>Select Your Membership Plan</h3>
        <div class="plan-selection">
            <?php foreach($membership_plans as $index => $plan): ?>
            <div class="plan-card" onclick="selectPlan(<?php echo $plan['plan_id']; ?>)">
                <h3><?php echo htmlspecialchars($plan['name']); ?></h3>
                <div class="plan-price">
                    Rs.<?php echo number_format($plan['price'], 2); ?>/month
                </div>
                <input type="radio" name="plan_id" value="<?php echo $plan['plan_id']; ?>" required>
                <div class="plan-duration">
                    Duration: <?php echo $plan['duration_months']; ?> months
                </div>
                <div class="plan-description">
                    <?php echo nl2br(htmlspecialchars($plan['description'])); ?>
                </div>
                <?php if($plan['class_access_level']): ?>
                <div class="plan-access-level">
                    Class Access Level: <?php echo htmlspecialchars($plan['class_access_level']); ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="payment-section">
            <div class="total-amount">
                <!-- Total Amount: Rs.<span id="totalAmount">0.00</span> -->
            </div>
            
            <h3>Payment Method</h3>
            <div class="payment-options">
                <label class="payment-option">
                    <input type="radio" name="payment_method" value="credit_card" required>
                    <span>Credit Card</span>
                </label>
                <label class="payment-option">
                    <input type="radio" name="payment_method" value="debit_card">
                    <span>Debit Card</span>
                </label>
                <label class="payment-option">
                    <input type="radio" name="payment_method" value="cash">
                    <span>Cash</span>
                </label>
            </div>

            <div id="card-details" class="card-details" style="display: none;">
                <div class="input-group">
                    <input type="text" name="card_number" placeholder="Card Number" pattern="[0-9]{16}">
                </div>
                <div class="input-row">
                    <div class="input-group">
                        <input type="text" name="expiry_date" placeholder="MM/YY" pattern="(0[1-9]|1[0-2])\/([0-9]{2})">
                    </div>
                    <div class="input-group">
                        <input type="text" name="cvv" placeholder="CVV" pattern="[0-9]{3}">
                    </div>
                </div>
            </div>
        </div>

                    <button type="submit" class="register-button">Complete
                        Registration</button>
                </form>

                <div class="form-footer">
                    Already have an account? <a href="member_login.html">Login
                        here</a>
                </div>
            </div>
        </div>
        
        <script>
            const planPrices = <?php echo json_encode(array_column($membership_plans, 'price', 'plan_id')); ?>;
        </script>
        <script src="registration.js"></script>
    </body>
</html>