<?php
require 'db.php';

if (isset($_GET['code'])) {
    $activation_code = $_GET['code'];

    // Checks if the activation code exists and the account is not active
    $stmt = $conn->prepare("SELECT username, role, is_active FROM users WHERE activation_code = ? AND is_active = 0");
    $stmt->bind_param('s', $activation_code);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {

        // Activation code is valid
        $stmt->bind_result($username, $role, $is_active);
        $stmt->fetch();

        // Update user status to active
        $update_stmt = $conn->prepare("UPDATE users SET is_active = 1 WHERE activation_code = ?");
        $update_stmt->bind_param('s', $activation_code);
        if ($update_stmt->execute()) {

            // Activation successful — redirect based on role
            echo "<h1>Account activated successfully!</h1>";
            echo "<p>Welcome, $username! Redirecting to your account...</p>";
            
            // Redirect to the appropriate dashboard
            if ($role === 'buyer') {
                header("refresh:3; url=buyer_dashboard.php");
            } else {
                header("refresh:3; url=seller_dashboard.php");
            }

        } else {
            echo "<p>Something went wrong while activating your account.</p>";
        }

    } else {
        // Invalid or already activated code
        echo "<h1>Invalid or expired activation code.</h1>";
        echo "<p>Please check your email or contact support.</p>";
    }

    $stmt->close();
    $conn->close();
    
} else {
    echo "<h1>Invalid request!</h1>";
}
?>
