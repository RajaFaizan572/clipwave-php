<?php
session_start();
// include('config.php');
include('header.php');

// Fetch latest videos
$query = "SELECT * FROM videos ORDER BY id DESC LIMIT 10";
$result = $conn->query($query);

// Fetch most liked videos
$liked_query = "SELECT v.id, v.title, v.likes_count FROM videos v ORDER BY v.likes_count DESC LIMIT 5";
$liked_result = $conn->query($liked_query);
?>

<div class="container">
    <!-- Video Display Section -->
    <div class="videos">
        <?php while ($video = $result->fetch_assoc()): ?>
            <div class="video-card" onclick="window.location.href='video.php?id=<?php echo $video['id']; ?>'">
                <h3><?php echo $video['title']; ?></h3>
                <!-- Video container is clickable but the video will play normally -->
                <div class="video-wrapper">
                    <video width="320" height="240" controls>
                        <source src="uploads/<?php echo $video['url']; ?>" type="video/mp4">
                    </video>
                </div>
                <!-- Like Counter -->
                <span id="like-count-<?php echo $video['id']; ?>"><?php echo $video['likes_count']; ?> Likes</span>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- Sidebar with Most Liked Videos -->
    <div class="sidebar">
        <h3>Most Liked Videos</h3>
        <?php while ($liked_video = $liked_result->fetch_assoc()): ?>
            <div class="liked-video">
                <p><?php echo $liked_video['title']; ?> - <?php echo $liked_video['likes_count']; ?> Likes</p>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include('footer.php'); ?>
