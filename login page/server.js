// email-service.js
const express = require('express');
const nodemailer = require('nodemailer');
const bodyParser = require('body-parser');
const cors = require('cors');
const app = express();

app.use(cors({
    origin: 'http://localhost', // Allow your PHP server origin
    methods: ['POST', 'GET', 'OPTIONS'],
    allowedHeaders: ['Content-Type']
}));

app.use(express.urlencoded({ extended: true }));
// Use JSON parser for incoming requests
app.use(bodyParser.json());

// Configure nodemailer
const transporter = nodemailer.createTransport({
    service: 'Gmail',
    auth: {
        user: 'snk20041130@gmail.com',
        pass: 'mdqf fskk ziym htqw'
    }
});

function generateEmailContent(memberData) {
    return `
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #764ba2; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .details-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
            .details-table th, .details-table td { padding: 10px; border: 1px solid #ddd; }
            .details-table th { background: #f0f0f0; }
            .footer { text-align: center; padding: 20px; font-size: 0.8em; color: #666; }
            .important-note { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Welcome to Fitcore Gym!</h1>
            </div>
            <div class="content">
                <h2>Registration Confirmation</h2>
                <p>Dear ${memberData.first_name} ${memberData.last_name},</p>
                <p>Thank you for joining Fitcore Gym! Your membership has been successfully activated.</p>
                
                <h3>Member Information:</h3>
                <table class="details-table">
                    <tr>
                        <th>Member ID</th>
                        <td>${memberData.member_id}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>${memberData.email}</td>
                    </tr>
                    <tr>
                        <th>Join Date</th>
                        <td>${memberData.join_date}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>${memberData.status}</td>
                    </tr>
                </table>

                <h3>Membership Details:</h3>
                <table class="details-table">
                    <tr>
                        <th>Membership ID</th>
                        <td>${memberData.membership_id}</td>
                    </tr>
                    <tr>
                        <th>Plan Name</th>
                        <td>${memberData.plan_name}</td>
                    </tr>
                    <tr>
                        <th>Duration</th>
                        <td>${memberData.duration_months} months</td>
                    </tr>
                    <tr>
                        <th>Start Date</th>
                        <td>${memberData.start_date}</td>
                    </tr>
                    <tr>
                        <th>End Date</th>
                        <td>${memberData.end_date}</td>
                    </tr>
                    <tr>
                        <th>Class Access Level</th>
                        <td>${memberData.class_access_level}</td>
                    </tr>
                </table>
                
                <h3>Payment Information:</h3>
                <table class="details-table">
                    <tr>
                        <th>Payment ID</th>
                        <td>${memberData.payment_id}</td>
                    </tr>
                    <tr>
                        <th>Amount Paid</th>
                        <td>Rs.${memberData.amount_paid}</td>
                    </tr>
                    <tr>
                        <th>Payment Method</th>
                        <td>${memberData.payment_method}</td>
                    </tr>
                    <tr>
                        <th>Transaction ID</th>
                        <td>${memberData.transaction_id}</td>
                    </tr>
                    <tr>
                        <th>Payment Date</th>
                        <td>${memberData.payment_date}</td>
                    </tr>
                </table>

                <div class="important-note">
                    <strong>Important:</strong>
                    <ul>
                        <li>Check our class schedule and book your preferred classes through our website</li>
                        <li>Initial fitness assessment is complimentary with your membership</li>
                    </ul>
                </div>
                
                <p>If you have any questions or need assistance, our support team is here to help:</p>
                <ul>
                    <li>Email: support@fitcoregym.com</li>
                    <li>Phone: +1-234-567-8900</li>
                    <li>Reception Hours: 6:00 AM - 10:00 PM</li>
                </ul>
            </div>
            <div class="footer">
                <p>Fitcore Gym<br>
                Your Health is Our Priority<br>
                123 Fitness Street, Gym City, GC 12345</p>
            </div>
        </div>
    </body>
    </html>
    `;
}

// Registration endpoint
app.post('/register', async (req, res) => {
    try {
        // Convert form data to PHP-friendly format
        const formData = new URLSearchParams();
        Object.entries(req.body).forEach(([key, value]) => {
            formData.append(key, value);
        });

        // Make request to PHP script
        const phpResponse = await fetch('http://localhost/gym/login%20page/process_registration.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: formData.toString()
        });

        if (!phpResponse.ok) {
            throw new Error(`PHP server error! status: ${phpResponse.status}`);
        }

        // Handle PHP response
        const responseText = await phpResponse.text();
        console.log('PHP Response:', responseText);

        try {
            // Try to parse as JSON if possible
            const jsonResponse = JSON.parse(responseText);
            res.status(200).json(jsonResponse);
        } catch (e) {
            // If not JSON, send as text
            res.status(200).send(responseText);
        }

    } catch (error) {
        console.error('Registration error:', error);
        res.status(500).json({ 
            error: 'Registration failed', 
            details: error.message,
            stack: process.env.NODE_ENV === 'development' ? error.stack : undefined
        });
    }
});


app.post('/send-confirmation', async (req, res) => {
    try {
        const memberData = req.body;
        
        const mailOptions = {
            from: '"Fitcore Gym" <admin@fitcoregym.com>',
            to: memberData.email,
            subject: 'Welcome to Fitcore Gym - Registration Confirmation',
            html: generateEmailContent(memberData),
            // attachments: [{
            //     filename: 'gym-qr-code.png',
            //     path: memberData.qr_code_path
            // }]
        };

        await transporter.sendMail(mailOptions);
        
        res.status(200).json({ message: 'Email sent successfully' });
    } catch (error) {
        console.error('Email sending error:', error);
        res.status(500).json({ error: 'Failed to send email' });
    }
});

app.use((err, req, res, next) => {
    console.error(err.stack);
    res.status(500).json({ 
        error: 'Something broke!',
        details: process.env.NODE_ENV === 'development' ? err.message : undefined
    });
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`Email service running on port ${PORT}`);
    console.log(`Attempting to connect to PHP endpoint at: http://localhost/gym/login%20page/process_registration.php`);
});