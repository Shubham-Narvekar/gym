<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful - Fitcore Gym</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea, #764ba2);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .success-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 600px;
            width: 90%;
        }

        .success-icon {
            font-size: 4em;
            color: #28a745;
            margin-bottom: 20px;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 2em;
        }

        p {
            color: #666;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .qr-placeholder {
            background: #f8f9fa;
            border: 2px dashed #ddd;
            width: 200px;
            height: 200px;
            margin: 20px auto;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 0.9em;
            color: #666;
        }

        .member-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: left;
        }

        .member-info p {
            margin: 10px 0;
        }

        .button {
            background: #764ba2;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            transition: 0.3s;
            margin: 10px;
        }

        .button:hover {
            background: #667eea;
        }

        .button-group {
            margin-top: 30px;
        }

        .download-qr {
            background: #28a745;
        }

        .download-qr:hover {
            background: #218838;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                background: white;
            }

            .success-container {
                box-shadow: none;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <i class="fas fa-check-circle success-icon"></i>
        <h1>Registration Successful!</h1>
        <p>Welcome to Fitcore Gym! Your membership has been successfully activated.</p>
        
        <!-- <div class="member-info">
            <?php if(isset($_SESSION['member_id'])): ?>
            <p><strong>Member ID:</strong> <?php echo $_SESSION['member_id']; ?></p>
            <?php endif; ?>
            <p>Please save your QR code below. You'll need it to check in at the gym.</p>
        </div>

        <div class="qr-placeholder">
            Your QR Code will appear here
        </div>

        <p>You can start using our facilities right away! Don't forget to bring your QR code when you visit.</p> -->

        <div class="button-group">
            <!-- <a href="#" class="button download-qr" onclick="window.print()">
                <i class="fas fa-download"></i> Download QR Code
            </a> -->
            <a href="member_login.html" class="button">
                <i class="fas fa-sign-in-alt"></i> Proceed to Login
            </a>
        </div>
    </div>
</body>
</html>