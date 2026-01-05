<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to place a bid.";
    exit();
}

$user_id = $_SESSION['user_id'];
$auction_id = $_POST['auction_id'];
$manual_bid_amount = isset($_POST['bid_amount']) ? $_POST['bid_amount'] : 0;

// Get the current auction details
$query = "SELECT current_bid, starting_bid FROM auctions WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $auction_id);
$stmt->execute();
$result = $stmt->get_result();
$auction = $result->fetch_assoc();
$stmt->close();

$current_bid = $auction['current_bid'] ?? $auction['starting_bid'];

// Validate that the bid is higher than the current bid
if ($manual_bid_amount <= $current_bid) {
    echo "Your bid must be higher than the current bid.";
    exit();
}

// Insert the manual bid
$query = "INSERT INTO bids (user_id, auction_id, bid_amount) VALUES (?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("iid", $user_id, $auction_id, $manual_bid_amount);
$stmt->execute();

// Update the auction's current bid
$query = "UPDATE auctions SET current_bid = ? WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("di", $manual_bid_amount, $auction_id);
$stmt->execute();

// Auto-Bidding System

// Get the current highest bidder
$query = "SELECT user_id FROM bids WHERE auction_id = ? ORDER BY bid_amount DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $auction_id);
$stmt->execute();
$result = $stmt->get_result();
$current_highest_bidder = $result->fetch_assoc()['user_id'] ?? null;
$stmt->close();

// Get all auto-bidders and sort by highest max_bid
$query = "SELECT user_id, max_bid FROM auto_bidding WHERE auction_id = ? ORDER BY max_bid DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $auction_id);
$stmt->execute();
$result = $stmt->get_result();

$highest_auto_bidder = null;
$highest_max_bid = null;
$second_highest_max_bid = null;

while ($row = $result->fetch_assoc()) {
    if ($row['user_id'] != $user_id) {
        if ($highest_auto_bidder === null) {
            $highest_auto_bidder = $row['user_id'];
            $highest_max_bid = $row['max_bid'];
        } elseif ($second_highest_max_bid === null) {
            $second_highest_max_bid = $row['max_bid'];
        }
    }
}

// Ensure the highest auto-bidder is winning
if ($highest_auto_bidder !== null && $highest_max_bid > $manual_bid_amount && $highest_auto_bidder != $current_highest_bidder) {
    
    $new_auto_bid = $manual_bid_amount + 100;
    
    if ($second_highest_max_bid !== null && $new_auto_bid > $second_highest_max_bid) {
        $new_auto_bid = $second_highest_max_bid + 100;
    }

    // Ensure we don't exceed the highest max bid
    if ($new_auto_bid > $highest_max_bid) {
        $new_auto_bid = $highest_max_bid;
    }

    // Place the auto-bid
    $query = "INSERT INTO bids (user_id, auction_id, bid_amount) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iid", $highest_auto_bidder, $auction_id, $new_auto_bid);
    $stmt->execute();

    // Update the current bid in the auction
    $query = "UPDATE auctions SET current_bid = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("di", $new_auto_bid, $auction_id);
    $stmt->execute();
}

// Redirect to dashboard with success message
echo("Bid placed successfully!");
exit();
?>
