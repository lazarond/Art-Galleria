<?php
require 'db.php';

$current_time = date('Y-m-d H:i:s');

$sql = "UPDATE auctions 
        SET status = 'active' 
        WHERE status = 'upcoming' 
        AND NOW() >= end_time";

$conn->query($sql);

echo json_encode(["success" => true]);

$conn->close();
?>
