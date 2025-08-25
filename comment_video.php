<?php
session_start();
include('config.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if the comment form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $video_id = $_POST['video_id'];
    $comment_text = $_POST['comment'];
    $user_id = $_SESSION['user_id'];

    // Insert comment into the database
    $stmt = $conn->prepare("INSERT INTO comments (user_id, video_id, comment_text) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $user_id, $video_id, $comment_text);
    $stmt->execute();

    // Redirect back to the video page
    header("Location: video.php?id=" . $video_id);
    exit();
}
?>
