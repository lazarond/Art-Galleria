<?php
include 'db.php';
include 'session.php';

$errors = []; 

// Function to compress image
function compressImage($source, $mime, $quality) {
    if ($mime == 'image/jpeg') {
        $image = imagecreatefromjpeg($source);
        ob_start();
        imagejpeg($image, null, $quality);
        $compressed = ob_get_clean();
    } elseif ($mime == 'image/png') {
        $image = imagecreatefrompng($source);
        ob_start();
        imagepng($image, null, 9 - ($quality / 10)); 
        $compressed = ob_get_clean();
    } else {
        return file_get_contents($source); 
    }

    return $compressed;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $starting_bid = $_POST['starting_bid'];
    $end_time = $_POST['end_time'];
    $seller_id = $_SESSION['user_id'];

    // Check if the selected start time is in the past
     $start_time = date('Y-m-d H:i:s', strtotime($_POST['start_time']));
     if (strtotime($start_time) < time()) {
        $errors[] = "The start time cannot be in the past.";
     }
 
    // Check if the selected end time is before the start time
     $end_time = date('Y-m-d H:i:s', strtotime($_POST['end_time']));
     if (strtotime($end_time) <= strtotime($start_time)) {
        $errors[] = "The end time must be after the start time.";
     }

    if (!empty($errors)) {

        echo '<script type="text/javascript">';
        echo 'var errors = ' . json_encode($errors) . ';';
        echo 'var errorMessages = errors.join("\\n");';
        echo 'alert(errorMessages);';  
        echo 'window.location.href = window.location.href;'; 
        echo '</script>';
        exit();
    }
    

    // Determine auction status
    if (!empty($_POST['start_time'])) {
        $start_time = date('Y-m-d H:i:s', strtotime($_POST['start_time'])); 
        $end_time = date('Y-m-d H:i:s', strtotime($_POST['end_time'])); 

        if (strtotime($start_time) > time()) {
            $status = "upcoming"; // Future start time → upcoming
        } elseif (strtotime($end_time) <= time()) {
            $status = "closed"; // End time has passed → closed
        } else {
            $status = "active"; // Start time is now or earlier, but end time is in the future → active
        }
    } else {
        $start_time = date('Y-m-d H:i:s');
        $end_time = date('Y-m-d H:i:s', strtotime($_POST['end_time'])); 
        $status = (strtotime($end_time) <= time()) ? "closed" : "active"; // If no start time, check end time
    }
    

    // Handle image upload and compression
    $image_data = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image_tmp_name = $_FILES['image']['tmp_name'];
        $image_type = $_FILES['image']['type'];

        // Compress image (set quality: 60 for JPEG, 70 for PNG)
        $image_data = compressImage($image_tmp_name, $image_type, 60);
    }

    // Insert into database
    $query = "INSERT INTO auctions (seller_id, title, description, image, starting_bid, current_bid, end_time, status, created_at, start_time) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("isssddsss", $seller_id, $title, $description, $image_data, $starting_bid, $starting_bid, $end_time, $status, $start_time);

    if ($stmt->execute()) {
        header("Location: seller_dashboard.php");
        exit();
    } else {
        die("Error: " . $stmt->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Auction</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="add-auction-container">
    <div class="add-auction-box">
        
        <h2>Create a New Auction</h2>

        <form action="add_auction.php" method="POST" enctype="multipart/form-data">
            <label>Title</label>
            <input type="text" name="title" required>

            <label>Starting Bid</label>
            <input type="number" name="starting_bid" min="1" required>

            <label>Start Time</label>
            <input type="datetime-local" name="start_time" required>

            <label>End Time</label>
            <input type="datetime-local" name="end_time" required>

            <label>Description</label>
            <textarea name="description" rows="4" required></textarea>

            <label>Upload Image</label>
            <input type="file" name="image" accept="image/*" required>

            <button type="submit" class="submit-btn">Create Auction</button>
        </form>

        <a href="seller_dashboard.php" class="back-btn">⬅ Back to Dashboard</a>
    </div>

</body>
</html>
