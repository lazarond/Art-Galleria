<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to set auto-bid.";
    exit();
}

$user_id = $_SESSION['user_id'];
$auction_id = $_POST['auction_id'];
$max_bid = $_POST['max_bid'];

// Validate input
if (!is_numeric($max_bid) || $max_bid <= 0) {
    die("Invalid bid amount.");
}

// Check if the user already has an auto-bid for this auction
$query = "SELECT id FROM auto_bidding WHERE user_id = ? AND auction_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $auction_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update existing max bid
    $query = "UPDATE auto_bidding SET max_bid = ? WHERE user_id = ? AND auction_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("dii", $max_bid, $user_id, $auction_id);
} else {
    // Insert new auto-bid
    $query = "INSERT INTO auto_bidding (user_id, auction_id, max_bid) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iid", $user_id, $auction_id, $max_bid);
}

// Execute the query 
if ($stmt->execute()) {
    echo "Auto-bid set successfully!";
    header("Location: buyer_dashboard.php");
    exit();
}
?>
