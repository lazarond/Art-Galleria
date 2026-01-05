<?php
include 'db.php';

$query_upcoming = "SELECT * FROM auctions WHERE status = 'upcoming' ORDER BY start_time ASC";
$result_upcoming = mysqli_query($conn, $query_upcoming);

$auctions = [];
while ($row = mysqli_fetch_assoc($result_upcoming)) {
    $auctions[] = $row;
}

header('Content-Type: application/json');
echo json_encode($auctions);
?>
