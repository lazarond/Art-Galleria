<?php 
include 'db.php';
include 'session.php'; 

$conn->query("UPDATE auctions SET status = 'active' WHERE status = 'upcoming' AND start_time <= NOW()");

// Check if the user is allowed to access the page
if ($_SESSION['user_role'] !== 'buyer') {
    header('Location: index.php'); 
    exit();
}

// Fetch all live auctions
$query = "SELECT id, title, starting_bid, current_bid, end_time, image 
          FROM auctions 
          WHERE status = 'active' AND end_time > NOW()";
$result = mysqli_query($conn, $query);

// Upcoming auctions
$query_upcoming = "SELECT * FROM auctions WHERE status = 'upcoming' ORDER BY start_time ASC";
$result_upcoming = mysqli_query($conn, $query_upcoming);

// Fetch the user's bids
$user_id = $_SESSION['user_id'];
$query_my_bids = "
    SELECT a.title, b.bid_amount, 
           CASE 
               WHEN b.bid_amount = a.current_bid THEN 'Highest' 
               ELSE 'Outbid' 
           END AS status 
    FROM bids b 
    JOIN auctions a ON b.auction_id = a.id 
    WHERE b.user_id = ? 
    ORDER BY b.created_at DESC";

$stmt = $conn->prepare($query_my_bids);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_my_bids = $stmt->get_result();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Buyer Dashboard - Art Galleria</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class = "buyer-container">

    <div class="buyer-dashboard-sidebar">
            <h2>Art Galleria</h2>

        <div class="menu-links">
            <a href="#live-auctions">Live Auctions</a>
            <a href="#upcoming-auctions">Upcoming Auctions</a>
            <a href="#track-my-bid">Track My Bid</a>
            <a href="#auction-history">Auction History</a>
        </div>

        <div class="logout-container">
            <a href="logout.php" onclick="return confirmLogout();" class="logout-btn">Sign out</a>
        </div>  

    </div>

    <div class="buyer-dashboard-main-content">

        <!-- Buyer Dashboard Header -->
        <div class="buyer-dashboard-header">
            <h1>Buyer Dashboard</h1>
        </div>
        
        <!-- Live Auctions -->
        <section id="live-auctions" class="buyer-dashboard-auction-section">
            <h2 class="auction-title">🟢 Live Auctions</h2>

            <div class="buyer-dashboard-auction-cards" id="auction-list">
                <?php 
                $query = "SELECT id, title, description, starting_bid, current_bid, end_time, image 
                        FROM auctions 
                        WHERE status = 'active' AND end_time > NOW()";
                $result = mysqli_query($conn, $query);

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) { 
                        // Format the end time properly for readability
                        $formatted_end_time = date('F j, Y, g:i A', strtotime($row['end_time'])); 
                        // Format the bid amount with ₱ symbol
                        $formatted_bid = '₱' . number_format($row['current_bid'] ?? $row['starting_bid'], 2);
                        ?>
                        
                        <div class="buyer-dashboard-auction-card" onclick="showAuctionDetails(
                                '<?php echo $row['id']; ?>',
                                '<?php echo htmlspecialchars(addslashes($row['title'])); ?>',
                                '<?php echo htmlspecialchars(addslashes($row['description'])); ?>',
                                '<?php echo $formatted_bid; ?>', <!-- Added ₱ to the bid -->
                                '<?php echo $formatted_end_time; ?>',
                                'display_image.php?auction_id=<?php echo $row['id']; ?>'
                            )">
                            
                            <img src="display_image.php?auction_id=<?php echo $row['id']; ?>" 
                                alt="<?php echo htmlspecialchars($row['title']); ?>">
                            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                            <p>Current Bid: <?php echo $formatted_bid; ?></p>
                            <p><strong>Ends On:</strong> <?php echo $formatted_end_time; ?></p> <!-- Formatted Date Here -->

                            <!-- View Details Button -->
                            <button onclick="showAuctionDetails(
                                <?php echo $row['id']; ?>,
                                '<?php echo htmlspecialchars(addslashes($row['title'])); ?>',
                                '<?php echo htmlspecialchars(addslashes($row['description'])); ?>',
                                '<?php echo $formatted_bid; ?>', <!-- Added ₱ to the bid -->
                                '<?php echo $formatted_end_time; ?>',
                                'display_image.php?auction_id=<?php echo $row['id']; ?>'
                            )">View Details</button>

                        </div>
                    <?php }
                } else {
                    echo "<p>No live auctions available.</p>";
                }
                ?>
            </div>
        </section>

         <!-- Upcoming Auctions Section -->
         <section id="upcoming-auctions" class="buyer-dashboard-upcoming-auctions">
            <h2 class="auction-title">Upcoming Auctions</h2>

            <div class="buyer-dashboard-auction-cards">
                <?php
                if (mysqli_num_rows($result_upcoming) > 0) {
                    while ($row = mysqli_fetch_assoc($result_upcoming)) { ?>
                        <div class="buyer-dashboard-auction-card">
                            <img src='display_image.php?auction_id=<?php echo $row['id']; ?>' alt="<?php echo htmlspecialchars($row['title']); ?>">
                            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                            <p>Starting Bid: ₱<?php echo number_format($row['starting_bid'], 2); ?></p>
                            <p>Starts On: <?php echo date("F j, Y, g:i A", strtotime($row['start_time'])); ?></p>
                            <!-- No Bid Button for Upcoming Auctions -->
                            <button class="disabled" disabled>Upcoming Auction</button>
                        </div>
                    <?php }
                } else {
                    echo "<p>No upcoming auctions at the moment.</p>";
                }
                ?>
            </div>
        </section>

        <!-- Bid Tracking -->
        <section id="track-my-bid" class="buyer-dashboard-bid-tracking">
            <h2 class="auction-title">Your Bids</h2>
            <table>
                <thead>
                    <tr>
                        <th>Artwork</th>
                        <th>Your Bid</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_my_bids->fetch_assoc()): ?> 
                        <tr>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td>$<?php echo number_format($row['bid_amount'], 2); ?></td>
                            <td><?php echo $row['status']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <!-- Auto-Bidding Section -->
        <section class="buyer-dashboard-auto-bidding">
            <h2>Auto-Bidding</h2>
            <form action="set_auto_bid.php" method="POST">
                <label for="auction_id">Select Auction:</label>
                <select name="auction_id" required>
                    <?php
                    $query = "SELECT id, title FROM auctions WHERE status = 'active'";
                    $result = mysqli_query($conn, $query);
                    while ($auction = mysqli_fetch_assoc($result)) {
                        echo "<option value='{$auction['id']}'>{$auction['title']}</option>";
                    }
                    ?>
                </select>
                
                <label for="max_bid">Set Maximum Bid:</label>
                <input type="number" name="max_bid" step="0.01" required>
                
                <button type="submit">Enable Auto-Bidding</button>
            </form>
        </section>

        <!-- Auction History -->
        <section id="auction-history" class="buyer-dashboard-auction-history">
            <h2 class="auction-title">Auction History</h2>
            <div class="auction-history-container">
                <table class="auction-history-table">
                    <thead>
                        <tr>
                            <th>Artwork</th>
                            <th>Winning Bid</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT a.title, b.bid_amount 
                                FROM bids b
                                JOIN auctions a ON b.auction_id = a.id
                                WHERE b.user_id = ? 
                                AND b.bid_amount = (SELECT MAX(bid_amount) FROM bids WHERE auction_id = a.id)";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("i", $_SESSION['user_id']);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>
                                        <td>" . htmlspecialchars($row['title']) . "</td>
                                        <td>$" . number_format($row['bid_amount'], 2) . "</td>
                                    </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='2' class='no-history'>No auction history available.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>

    </div>

    <!-- Auction Details Modal -->
    <div id="auctionModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <img id="modalImage" src="" alt="Auction Image">
            <h2 id="modalTitle"></h2>
            <p id="modalDescription"></p>
            <p><strong>Current Bid:</strong> <span id="modalCurrentBid"></span></p>
            <p><strong>Ends On:</strong> <span id="modalEndTime"></span></p>
            
            <!-- New Bid Input -->
            <label for="bidAmount"><strong>Your Bid:</strong></label>
            <input type="number" id="bidAmount" placeholder="Enter your bid" min="0.01" step="0.01">
            
            <button id="placeBidButton">Place Bid</button>
        </div>
    </div>

    <script src="script.js"></script>

</body>
</html>
