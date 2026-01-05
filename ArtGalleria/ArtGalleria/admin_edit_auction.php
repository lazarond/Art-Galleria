<?php
session_start();
include 'db.php';

// Check if the 'id' parameter is set
if (!isset($_GET['id'])) {
    header("Location: admin.php");
    exit();
}

$id = $_GET['id'];

// Prepare a query to fetch the auction details by ID
$query = "SELECT * FROM auctions WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id); 
$stmt->execute();
$result = $stmt->get_result();
$auction = $result->fetch_assoc();

// If no auction is found, redirect to the dashboard
if (!$auction) {
    header("Location: admin.php");
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $starting_bid = $_POST['starting_bid'];

    $update_query = "UPDATE auctions SET title = ?, description = ?, starting_bid = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ssdi", $title, $description, $starting_bid, $id); 

    if ($update_stmt->execute()) {
        header("Location: admin.php"); 
        exit();
    } else {
        echo "Error updating auction: " . $update_stmt->error;
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Auction</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="admin-edit-auction-page">
    <div class="admin-edit-auction-container">
        <h2>Edit Auction</h2>
        
        <form action="" method="post">
            <label for="title">Title</label>
            <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($auction['title']); ?>" required>

            <label for="description">Description</label>
            <textarea name="description" id="description" required><?php echo htmlspecialchars($auction['description']); ?></textarea>

            <label for="starting_bid">Starting Bid</label>
            <input type="number" name="starting_bid" id="starting_bid" value="<?php echo htmlspecialchars($auction['starting_bid']); ?>" required>

            <button type="submit">Update Auction</button>
        </form>
        
        <a href="admin.php">⬅ Back</a>
    </div>
</body>
</html>
