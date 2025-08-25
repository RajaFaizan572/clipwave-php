<?php
session_start();
include('config.php'); // Database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'You must be logged in to like a video.']);
    exit();
}

// Get video ID from the AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $video_id = $data['videoId'];
    $user_id = $_SESSION['user_id'];

    // Check if the user has already liked the video
    $check_like = $conn->prepare("SELECT * FROM likes WHERE user_id = ? AND video_id = ?");
    $check_like->bind_param("ii", $user_id, $video_id);
    $check_like->execute();
    $result = $check_like->get_result();

    if ($result->num_rows == 0) {
        // User has not liked the video, so we insert a new like record
        $insert_like = $conn->prepare("INSERT INTO likes (user_id, video_id) VALUES (?, ?)");
        $insert_like->bind_param("ii", $user_id, $video_id);
        $insert_like->execute();

        // Update the likes count for the video
        $update_likes = $conn->prepare("UPDATE videos SET likes_count = likes_count + 1 WHERE id = ?");
        $update_likes->bind_param("i", $video_id);
        $update_likes->execute();

        // Return the updated likes count and action ("like")
        $query = "SELECT likes_count FROM videos WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $video_id);
        $stmt->execute();
        $stmt->bind_result($likes_count);
        $stmt->fetch();

        echo json_encode(['likes' => $likes_count, 'action' => 'like']);
    } else {
        // User has already liked the video, so we remove the like
        $delete_like = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND video_id = ?");
        $delete_like->bind_param("ii", $user_id, $video_id);
        $delete_like->execute();

        // Update the likes count for the video
        $update_likes = $conn->prepare("UPDATE videos SET likes_count = likes_count - 1 WHERE id = ?");
        $update_likes->bind_param("i", $video_id);
        $update_likes->execute();

        // Return the updated likes count and action ("unlike")
        $query = "SELECT likes_count FROM videos WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $video_id);
        $stmt->execute();
        $stmt->bind_result($likes_count);
        $stmt->fetch();

        echo json_encode(['likes' => $likes_count, 'action' => 'unlike']);
    }
}
?>
