<?php
session_start();
require 'db.php';

$errors = [];

// Check if the verification code has expired 
if (isset($_SESSION['verification_code_time']) && (time() - $_SESSION['verification_code_time'] > 300)) {
    unset($_SESSION['verification_code']);
    unset($_SESSION['verification_code_time']);
    $errors[] = "Verification code expired. Please log in again.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_code = $_POST['verification_code'];

    if (isset($_SESSION['verification_code']) && $entered_code == $_SESSION['verification_code']) {
        // If the code is correct
        $_SESSION['user_id'] = $_SESSION['temp_user_id'];

        // Clear temporary data
        unset($_SESSION['verification_code']);
        unset($_SESSION['temp_user_id']);
        unset($_SESSION['verification_code_time']);

        // Check user role (seller/buyer/admin)
        if (isset($_SESSION['user_role'])) {
            if ($_SESSION['user_role'] === 'seller') {
                header('Location: seller_dashboard.php');
            } elseif ($_SESSION['user_role'] === 'buyer') {
                header('Location: buyer_dashboard.php');
            } elseif ($_SESSION['user_role'] === 'admin') {
                header('Location: admin.php');
            } else {
                $errors[] = "Invalid user role.";
            }
        } else {
            $errors[] = "User role is not set. Please log in again.";
        }
        exit();

    } else {
        $errors[] = "Invalid verification code. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify Your Code</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="verify-page">
    <div class="verification-page">
        <div class="verification-container">
            <div class="logo">
                <img src="images/logo.png" alt="Art Galleria Logo">
            </div>
            <h2>Two-Factor Authentication</h2>
            <p>Please enter the verification code sent to your email.</p>

            <!-- Error messages -->
            <?php if (!empty($errors)): ?>
                <div class="error-messages">
                    <?php foreach ($errors as $error): ?>
                        <p class="error">&#9888; <?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <label for="verification_code">Verification Code</label>
                <input type="text" name="verification_code" id="verification_code" placeholder="Enter code" required>
                <button type="submit">Verify</button>
                <a href="login.php" class="back-link">Back to Login</a>
            </form>

        </div>
    </div>

    <script src="script.js"></script>

</body>
</html>
