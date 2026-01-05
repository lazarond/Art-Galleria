<?php
require 'db.php';

// Get all active auctions
$query = "SELECT id, title, current_bid, end_time, image FROM auctions WHERE status = 'active' AND end_time > NOW()";
$result = mysqli_query($conn, $query);

while ($auction = mysqli_fetch_assoc($result)) {
    $auction_id = $auction['id'];
    $current_bid = $auction['current_bid'] ?? $auction['starting_bid'];

    // Check auto-bidders
    $query = "SELECT user_id, max_bid FROM auto_bidding WHERE auction_id = ? ORDER BY max_bid DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $auction_id);
    $stmt->execute();
    $auto_result = $stmt->get_result();

    $highest_auto_bidder = null;
    $highest_max_bid = null;
    $second_highest_max_bid = null;

    while ($row = $auto_result->fetch_assoc()) {
        if ($highest_auto_bidder === null) {
            $highest_auto_bidder = $row['user_id'];
            $highest_max_bid = $row['max_bid'];
        } elseif ($second_highest_max_bid === null) {
            $second_highest_max_bid = $row['max_bid'];
        }
    }
    $stmt->close();

    if ($highest_auto_bidder !== null && $highest_max_bid > $current_bid) {
        $new_auto_bid = $current_bid + 100;

        // Ensure we don't exceed the highest max bid
        if ($second_highest_max_bid !== null && $new_auto_bid > $second_highest_max_bid) {
            $new_auto_bid = $second_highest_max_bid + 100;
        }
        if ($new_auto_bid > $highest_max_bid) {
            $new_auto_bid = $highest_max_bid;
        }

        // Place the auto-bid
        $query = "INSERT INTO bids (user_id, auction_id, bid_amount) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iid", $highest_auto_bidder, $auction_id, $new_auto_bid);
        $stmt->execute();
        $stmt->close();

        // Update the auction bid
        $query = "UPDATE auctions SET current_bid = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("di", $new_auto_bid, $auction_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Return updated auction data
$query = "SELECT id, title, current_bid, end_time FROM auctions WHERE status = 'active' AND end_time > NOW()";
$result = mysqli_query($conn, $query);

$auctions[] = [
    'id' => $row['id'],
    'title' => $row['title'],
    'current_bid' => '₱' . number_format($row['current_bid'], 2),
    'end_time' => date('F j, Y, g:i A', strtotime($row['end_time'])),
    'image' => 'display_image.php?auction_id=' . $row['id'] 
];


echo json_encode($auctions);
?>
