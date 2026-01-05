<?php
session_start();
include 'db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    mysqli_query($conn, "DELETE FROM auctions WHERE id=$id");
}

header("Location: seller_dashboard.php");
exit();
?>
