<?php
session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Secure CSRF token
}
require 'db.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Check if the email exists
    $stmt = $conn->prepare("SELECT id, username, password, role, is_active FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $username, $hashed_password, $role, $is_active);
        $stmt->fetch();

        $_SESSION['user_role'] = $role; 

        if (password_verify($password, $hashed_password)) {
            if ($is_active) {
                
                // Generate a 6-digit verification code
                $verification_code = rand(100000, 999999);

                // Store the code in the session 
                $_SESSION['verification_code'] = $verification_code;
                $_SESSION['temp_user_id'] = $id;  
                $_SESSION['verification_code_time'] = time();

                // Send the verification code via email
                $mail = new PHPMailer(true);

                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'axiniteee@gmail.com';  
                    $mail->Password = 'tddvogaherbeaujy'; 
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    // Recipients
                    $mail->setFrom('axiniteee@gmail.com', 'Art Galleria');
                    $mail->addAddress($email);

                    // Email content
                    $mail->isHTML(true);
                    $mail->Subject = 'Your Verification Code';
                    $mail->Body = "<p>Your verification code is: <strong>$verification_code</strong></p>";

                    $mail->send();
                    header('Location: verify_code.php');
                    exit();

                } catch (Exception $e) {
                    $errors[] = "Failed to send verification email: " . $mail->ErrorInfo;
                }

            } else {
                $errors[] = "Your account is not activated. Please check your email.";
            }

        } else {
            $errors[] = "Invalid email or password.";
        }
    } else {
        $errors[] = "No account found with that email.";
    }

    $stmt->close();
    $conn->close();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }
}
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <form action="login.php" method="POST" class="login-form">

            <h1>Welcome back 👋</h1>
            <h2>Sign in to your account</h2>

            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

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

            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Login</button>

            <a href="index.php" class="back-link">Back to Main Page</a>

            <p class="register-link">Don't have an account? <a href="register.php">Register</a></p>

        </form>
    </div>

</body>
</html>
