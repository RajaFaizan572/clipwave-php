<?php
session_start();
include('config.php');
include('header.php');

// Only allow creators to upload
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $genre = $_POST['genre'];
    $age_rating = $_POST['age_rating'];
    $file = $_FILES['video_file'];

    $allowed_types = ['video/mp4'];
    if (!in_array($file['type'], $allowed_types)) {
        echo "Invalid file type. Only MP4 videos are allowed.";
        exit();
    }

    // Move the uploaded video
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($file['name']);
    move_uploaded_file($file['tmp_name'], $target_file);

    // Insert video metadata
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("INSERT INTO videos (user_id, title, genre, age_rating, url) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $title, $genre, $age_rating, $file['name']);
    $stmt->execute();

    echo "Video uploaded successfully!";
}

?>

<!-- Upload Video Form -->
<div class="container">
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Video Title" required><br>
        <input type="text" name="genre" placeholder="Video Genre" required><br>
        <input type="text" name="age_rating" placeholder="Age Rating" required><br>
        <input type="file" name="video_file" accept="video/mp4" required><br>
        <button type="submit">Upload Video</button>
    </form>
</div>

<?php include('footer.php'); ?>
