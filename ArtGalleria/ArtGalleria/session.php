<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    
    // Redirect to the login page if not authenticated
    header('Location: login.php');
    exit();
}
?>
