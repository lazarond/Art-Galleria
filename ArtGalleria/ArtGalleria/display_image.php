<?php
include 'db.php'; 

if (isset($_GET['auction_id'])) {
    $auction_id = intval($_GET['auction_id']); 

    $query = "SELECT image FROM auctions WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $auction_id);
    $stmt->execute();
    $stmt->bind_result($image);
    $stmt->fetch();
    $stmt->close();

    if ($image) {
        header("Content-Type: image/jpeg"); 
        echo $image;
        exit;
    }
}

header("Content-Type: image/png");
readfile("placeholder.jpg");
?>
