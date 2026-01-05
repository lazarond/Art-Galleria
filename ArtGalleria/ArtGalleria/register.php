<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

session_start();
require 'db.php'; 

$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $role = isset($_POST['role']) && $_POST['role'] === 'seller' ? 'seller' : 'buyer';
    $created_at = date('Y-m-d H:i:s');
    $activation_code = bin2hex(random_bytes(16)); // Random activation code

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Password validation (min 8 characters, 1 uppercase, 1 lowercase, 1 special character)
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\W).{8,}$/', $password)) {
        $errors[] = "Password must be at least 8 characters long, with at least 1 uppercase, 1 lowercase, and 1 special character.";
    }

    // Check if passwords match
     if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {

         // Check if the email is already registered
        $email_check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $email_check_stmt->bind_param('s', $email);
        $email_check_stmt->execute();
        $email_check_stmt->store_result();

        if ($email_check_stmt->num_rows > 0) {
            $errors[] = "This email is already registered. Please use a different email.";
        }
        $email_check_stmt->close();

        // Hash the password before inserting it into the database
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert user into the database
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, created_at, activation_code, is_active) VALUES (?, ?, ?, ?, ?, ?, 0)");
        if (!$stmt) {
        die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param('ssssss', $username, $email, $hashed_password, $role, $created_at, $activation_code);

        if ($stmt->execute()) {
          
            // Send verification email 
            $mail = new PHPMailer(true);

            try {
                // SMTP configuration
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';        
                $mail->SMTPAuth = true;
                $mail->Username = 'axiniteee@gmail.com'; 
                $mail->Password = 'tddvogaherbeaujy'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Email settings
                $mail->setFrom('axiniteee@gmail.com', 'Art Galleria');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Account Activation - Art Galleria';
                $mail->Body = "
                    <h1>Activate your account</h1>
                    <p>Click the link below to activate your account:</p>
                    <a href='http://localhost/artgalleria/activate.php?code=$activation_code'>Activate Now</a>
                ";

                $mail->send();
                $success_message = "Registration successful! Please check your email to activate your account.";
            } catch (Exception $e) {
                $errors[] = "Mailer Error: " . $mail->ErrorInfo;
            }
        } else {
            $errors[] = "An error occurred during registration. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="register-container">
        <form action="register.php" method="POST" class="register-form">
            <h1>Let's Get Started 🚀</h1>
            <h2>Sign up to your account</h2>

             <!-- Success message -->
            <?php if (!empty($success_message)): ?>
                <p class="success"><?php echo $success_message; ?></p>
            <?php endif; ?>

            <!-- Error messages -->
            <?php if (!empty($errors)): ?>
                <div class="error-messages">
                    <?php foreach ($errors as $error): ?>
                        <p class="error"><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <div class="role-toggle">
                <label for="role">Role:</label>
                <span id="role-text">Buyer</span>
                <input type="checkbox" id="role" name="role" value="buyer">
            </div>

            <button type="submit">Register</button>

            <a href="index.php" class="back-link">Back to Main Page</a>

            <p class="login-link">Already have an account? <a href="login.php">Login</a></p>

        </form>
    </div>

    <script src="script.js"></script>

</body>
</html>
