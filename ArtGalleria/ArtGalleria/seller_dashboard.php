<?php 
include 'db.php';
include 'session.php';

$conn->query("UPDATE auctions SET status = 'active' WHERE status = 'upcoming' AND start_time <= NOW()");
$conn->query("UPDATE auctions SET status = 'completed' WHERE status = 'active' AND end_time <= NOW()");

$seller_id = $_SESSION['user_id'];

// Check if the user is allowed to access the page
if ($_SESSION['user_role'] !== 'seller') {
    header('Location: index.php');
    exit();
}

// Fetch live and upcoming auctions separately
$query_live = "SELECT id, title, starting_bid, current_bid, end_time FROM auctions WHERE seller_id = ? AND status = 'active'";
$query_upcoming = "SELECT id, title, starting_bid, start_time, end_time FROM auctions WHERE seller_id = ? AND status = 'upcoming'";
$query_completed = "
    SELECT 
        a.id, a.title, a.starting_bid, a.current_bid, a.end_time,
        (SELECT u.username FROM bids b 
         JOIN users u ON b.user_id = u.id 
         WHERE b.auction_id = a.id 
         ORDER BY b.bid_amount DESC 
         LIMIT 1) AS winner
    FROM auctions a 
    WHERE a.seller_id = ? AND a.status = 'closed'";


$stmt_live = $conn->prepare($query_live);
if (!$stmt_live) {
    die("Error preparing statement (Live Auctions): " . $conn->error);
}
$stmt_live->bind_param("i", $seller_id);
$stmt_live->execute();
$result_live = $stmt_live->get_result();

$stmt_upcoming = $conn->prepare($query_upcoming);
if (!$stmt_upcoming) {
    die("Error preparing statement (Upcoming Auctions): " . $conn->error);
}
$stmt_upcoming->bind_param("i", $seller_id);
$stmt_upcoming->execute();
$result_upcoming = $stmt_upcoming->get_result();

$stmt_completed = $conn->prepare($query_completed);
if (!$stmt_completed) {
    die("Error preparing statement (Completed Auctions): " . $conn->error);
}
$stmt_completed->bind_param("i", $seller_id);
$stmt_completed->execute();
$result_completed = $stmt_completed->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Seller Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="seller-container">

<div class="seller-dashboard">
    <div class="top-nav">
        <h2>Seller Dashboard</h2>
        <a href="logout.php" onclick="return confirmLogout();" class="logout-btn">Sign out</a>
    </div>
    
    <a href="add_auction.php" class="add-auction-btn">➕ Add New Auction</a>

    <!-- Live Auctions -->
    <h3>🟢 Live Auctions</h3>
    <table class="auction-table">
        <tr>
            <th>Image</th>
            <th>Title</th>
            <th>Current Bid</th>
            <th>End Time</th>
        </tr>
        <?php while ($row = $result_live->fetch_assoc()) { ?>
        <tr>
            <td>
                <img src="display_image.php?auction_id=<?php echo $row['id']; ?>" alt="Auction Image" class="auction-img">
            </td>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td>₱<?php echo number_format($row['current_bid'] ?? $row['starting_bid'], 2); ?></td>
            <td><?php echo date("F j, Y, g:i A", strtotime($row['end_time'])); ?></td>
        </tr>
        <?php } ?>
    </table>

    <!-- Upcoming Auctions -->
    <h3>⏳ Upcoming Auctions</h3>
    <table class="auction-table">
        <tr>
            <th>Image</th>
            <th>Title</th>
            <th>Starting Bid</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result_upcoming->fetch_assoc()) { ?>
        <tr>
            <td>
                <img src="display_image.php?auction_id=<?php echo $row['id']; ?>" alt="Auction Image" class="auction-img">
            </td>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td>₱<?php echo number_format($row['starting_bid'], 2); ?></td>
            <td><?php echo date("F j, Y, g:i A", strtotime($row['start_time'])); ?></td>
            <td><?php echo date("F j, Y, g:i A", strtotime($row['end_time'])); ?></td>
            <td class="auction-actions">
                <a href="edit_auction.php?id=<?php echo $row['id']; ?>" class="edit-btn">✏ Edit</a>
                <a href="delete_auction.php?id=<?php echo $row['id']; ?>" class="delete-btn" onclick="return confirm('Delete this auction?')">❌ Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>

    <!-- Completed Auctions -->
    <h3>✅ Completed Auctions</h3>
        <table class="auction-table">
            <tr>
                <th>Image</th>
                <th>Title</th>
                <th>Final Bid</th>
                <th>End Time</th>
                <th>Winner</th>
            </tr>
            <?php while ($row = $result_completed->fetch_assoc()) { ?>
            <tr>
                <td>
                    <img src="display_image.php?auction_id=<?php echo $row['id']; ?>" alt="Auction Image" class="auction-img">
                </td>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td>₱<?php echo number_format($row['current_bid'], 2); ?></td>
                <td><?php echo date("F j, Y, g:i A", strtotime($row['end_time'])); ?></td>
                <td><?php echo $row['winner'] ? htmlspecialchars($row['winner']) : 'No Bids'; ?></td>
                
            </tr>
        <?php } ?>
    </table>

</div>

<script src="script.js"></script>

</body>
</html>
