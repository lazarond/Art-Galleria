<?php 
include 'db.php';
include 'session.php';

// Ensure session role is set before comparison
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all auctions
$sql_auctions = "SELECT * FROM auctions ORDER BY created_at DESC";
$result_auctions = $conn->query($sql_auctions);

$auctions = [];
while ($row = $result_auctions->fetch_assoc()) {
    $auctions[] = $row;
}

// Fetch active users
$sql_users = "SELECT id, username, email, role FROM users WHERE is_active = 1";
$result_users = $conn->query($sql_users);

// Handle auction deletion
if (isset($_GET['delete_auction'])) {
    $auction_id = intval($_GET['delete_auction']);
    $delete_sql = "DELETE FROM auctions WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $auction_id);
    if ($stmt->execute()) {
        echo "<script>alert('Auction deleted successfully.'); window.location='admin.php';</script>";
    } else {
        echo "<script>alert('Failed to delete auction.');</script>";
    }
}

$sql_winner = "
    SELECT users.username, bids.amount
    FROM bids 
    JOIN users ON bids.user_id = users.id
    WHERE bids.auction_id = ? 
    ORDER BY bids.amount DESC 
    LIMIT 1"; 

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Art Galleria</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-container">

        <!-- Sidebar -->
        <aside class="admin-dashboard-sidebar">
            <h2 class="admin-dashboard-title">Art Galleria</h2>
            <nav class="admin-menu-links">
                <a href="admin.php" class="admin-nav-link active">Dashboard</a>
                <a href="#view-users" class="admin-nav-link">View Active Users</a>
                <a href="#manage-auctions" class="admin-nav-link">Manage Auctions</a>
            </nav>
            <div class="logout-container">
                <a href="logout.php" onclick="return confirmLogout();" class="logout-btn">Sign Out</a>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="admin-dashboard-main-content">
            <header class="admin-dashboard-header">
                <h1>Welcome, Admin</h1>
            </header>

            <!-- View Auctions -->
            <section id="view-auctions" class="admin-section">
                <h3 class="admin-section-title">View Auctions</h3>
                <table class="admin-auction-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Starting Bid</th>
                            <th>Current Bid</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Status</th>
                            <th>Winner</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($auctions)): ?>
                            <?php foreach ($auctions as $row): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td>
                                    <?php if (!empty($row['image'])): ?>
                                        <img src="data:image/jpeg;base64,<?= base64_encode($row['image']) ?>" class="auction-img" />
                                    <?php else: ?>
                                        <span class="no-image">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td>₱<?= number_format($row['starting_bid'], 2) ?></td>
                                <td>₱<?= number_format($row['current_bid'] ?? 0, 2) ?></td>
                                <td><?= $row['start_time'] ?></td>
                                <td><?= $row['end_time'] ?></td>
                                <td class="status-<?= strtolower($row['status']) ?>"><?= ucfirst($row['status']) ?></td>

                                <!-- Fetch Winner if Auction is Closed -->
                                <td>
                                    <?php 
                                    if ($row['status'] === 'closed') {
                                        $auction_id = $row['id'];

                                        // Fetch highest bid
                                        $stmt = $conn->prepare("
                                            SELECT users.username, bids.bid_amount
                                            FROM bids 
                                            JOIN users ON bids.user_id = users.id
                                            WHERE bids.auction_id = ? 
                                            ORDER BY bids.bid_amount DESC 
                                            LIMIT 1
                                        ");
                                        $stmt->bind_param("i", $auction_id);
                                        $stmt->execute();
                                        $winner_result = $stmt->get_result();

                                        if ($winner = $winner_result->fetch_assoc()) {
                                            echo "<span class='winner-name'>{$winner['username']} (₱" . number_format($winner['bid_amount'], 2) . ")</span>";
                                        } else {
                                            echo "<span class='no-winner'>No bids</span>";
                                        }
                                    } else {
                                        echo "<span class='no-winner'>No bids</span>";
                                    }
                                    ?>
                                </td>

                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8">No auctions found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>

            <!-- View Active Users -->
            <section id="view-users" class="admin-section">
                <h3 class="admin-section-title">View Active Users</h3>
                <table class="admin-auction-table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $result_users->fetch_assoc()): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= ucfirst($user['role']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </section>

            <!-- Manage Auctions -->
            <section id="manage-auctions" class="admin-section">
                <h3 class="admin-section-title">Manage Auctions</h3>
                <table class="admin-auction-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Starting Bid</th>
                            <th>Current Bid</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($auctions)): ?>
                            <?php foreach ($auctions as $row): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td>
                                    <?php if (!empty($row['image'])): ?>
                                        <img src="data:image/jpeg;base64,<?= base64_encode($row['image']) ?>" class="auction-img" />
                                    <?php else: ?>
                                        <span class="no-image">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td>₱<?= number_format($row['starting_bid'], 2) ?></td>
                                <td>₱<?= number_format($row['current_bid'] ?? 0, 2) ?></td>
                                <td><?= $row['start_time'] ?></td>
                                <td><?= $row['end_time'] ?></td>
                                <td class="status-<?= strtolower($row['status']) ?>"><?= ucfirst($row['status']) ?></td>
                                <td>
                                    <div class="admin-buttons">
                                        <?php if ((strtolower($row['status']) !== 'closed') && (strtolower($row['status']) !== 'active')): ?>
                                            <a href="admin_edit_auction.php?id=<?= $row['id'] ?>" class="admin-edit-btn">Edit</a>
                                            <a href="admin.php?delete_auction=<?= $row['id'] ?>" onclick="return confirm('Delete this auction?')" class="admin-delete-btn">Delete</a>
                                        <?php else: ?>
                                            <span class="auction-closed-msg">Closed</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="9">No auctions found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>

        </div>
    </div>

    <script>
        function confirmLogout() {
            return confirm("Are you sure you want to log out?");
        }
    </script>
</body>
</html>
