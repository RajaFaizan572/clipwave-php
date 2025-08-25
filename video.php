<?php
session_start();
include('config.php');
include('header.php');

// Get the video ID from the URL
$video_id = $_GET['id'];

// Fetch the specific video details
$query = "SELECT * FROM videos WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $video_id);
$stmt->execute();
$video_result = $stmt->get_result();
$video = $video_result->fetch_assoc();

// Check if the user has liked this video (to initialize the like/unlike button)
$user_id = $_SESSION['user_id'] ?? null;
$liked_query = "SELECT * FROM likes WHERE user_id = ? AND video_id = ?";
$liked_stmt = $conn->prepare($liked_query);
$liked_stmt->bind_param("ii", $user_id, $video_id);
$liked_stmt->execute();
$liked_result = $liked_stmt->get_result();
$has_liked = $liked_result->num_rows > 0;

// Increment the view count for the video
$update_view_count = $conn->prepare("UPDATE videos SET view_count = view_count + 1 WHERE id = ?");
$update_view_count->bind_param("i", $video_id);
$update_view_count->execute();

// Fetch comments for the video
$comments_query = "SELECT c.comment_text, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.video_id = ?";
$comments_stmt = $conn->prepare($comments_query);
$comments_stmt->bind_param("i", $video_id);
$comments_stmt->execute();
$comments_result = $comments_stmt->get_result(); // This stores the result in $comments_result

?>

<div class="container">
    <div class="video-detail">
        <h3><?php echo $video['title']; ?></h3>
        <video width="640" height="360" controls>
            <source src="uploads/<?php echo $video['url']; ?>" type="video/mp4">
        </video>

        <div class="like-share-comments">
            <!-- Like/Unlike Button -->
            <button id="likeButton-<?php echo $video['id']; ?>" onclick="likeVideo(<?php echo $video['id']; ?>)">
                <i class="fas fa-thumbs-<?php echo $has_liked ? 'down' : 'up'; ?>"></i> 
                <?php echo $has_liked ? 'Unlike' : 'Like'; ?>
            </button>
            <span id="like-count-<?php echo $video['id']; ?>"><?php echo $video['likes_count']; ?> Likes</span>

            <!-- Share Buttons with Icons -->
            <div>
                <button onclick="shareVideoFacebook(<?php echo $video['id']; ?>)">
                    <i class="fab fa-facebook-square"></i> Share on Facebook
                </button>
                <button onclick="shareVideoTwitter(<?php echo $video['id']; ?>)">
                    <i class="fab fa-twitter-square"></i> Share on Twitter
                </button>
                <button onclick="shareVideoWhatsApp(<?php echo $video['id']; ?>)">
                    <i class="fab fa-whatsapp"></i> Share on WhatsApp
                </button>
                <button onclick="shareVideoInstagram(<?php echo $video['id']; ?>)">
                    <i class="fab fa-instagram"></i> Share on Instagram
                </button>
            </div>

            <!-- Comment Section -->
            <h4>Comments</h4>
            <form method="POST" action="comment_video.php">
                <textarea name="comment" placeholder="Add a comment..." required></textarea><br>
                <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                <button type="submit">Post Comment</button>
            </form>

            <!-- Display Comments -->
            <?php 
            if ($comments_result->num_rows > 0) {
                while ($comment = $comments_result->fetch_assoc()): ?>
                    <div class="comment">
                        <strong><?php echo $comment['username']; ?>:</strong>
                        <p><?php echo $comment['comment_text']; ?></p>
                    </div>
                <?php endwhile;
            } else {
                echo "<p>No comments yet. Be the first to comment!</p>";
            }
            ?>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<!-- JavaScript for Like and Share -->
<script>
// Like/Unlike Button functionality with AJAX
function likeVideo(videoId) {
    fetch('like_video.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ videoId: videoId })  // Send the videoId as JSON
    })
    .then(response => response.json())  // Parse the JSON response
    .then(data => {
        if (data.likes !== undefined) {
            // Update the like count on the page
            document.getElementById('like-count-' + videoId).innerText = data.likes + ' Likes';

            // Toggle the button text and icon between "Like" and "Unlike"
            const button = document.getElementById('likeButton-' + videoId);
            if (data.action === 'like') {
                button.innerHTML = '<i class="fas fa-thumbs-down"></i> Unlike';
            } else if (data.action === 'unlike') {
                button.innerHTML = '<i class="fas fa-thumbs-up"></i> Like';
            }
        } else if (data.error) {
            // If there's an error (e.g., already liked), show an alert
            alert(data.error);
        }
    })
    .catch(error => console.error('Error:', error));  // Log any error to the console
}
</script>
<script>
// Share on Facebook
function shareVideoFacebook(videoId) {
    const videoUrl = window.location.href;  // Get current page URL (video URL)
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(videoUrl)}`, '_blank', 'width=600,height=400');
}

// Share on Twitter
function shareVideoTwitter(videoId) {
    const videoUrl = window.location.href;  // Get current page URL (video URL)
    window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(videoUrl)}&text=Check out this amazing video on Clipwave!`, '_blank', 'width=600,height=400');
}

// Share on WhatsApp
function shareVideoWhatsApp(videoId) {
    const videoUrl = window.location.href;  // Get current page URL (video URL)
    window.open(`https://wa.me/?text=${encodeURIComponent("Check out this amazing video on Clipwave: " + videoUrl)}`, '_blank', 'width=600,height=400');
}

// Share on Instagram (Instagram does not support web sharing directly, so we open Instagram's home page)
function shareVideoInstagram(videoId) {
    const videoUrl = window.location.href;  // Get current page URL (video URL)
    alert("Instagram does not support direct sharing from the web. Please share on the Instagram app.");
    window.open(`https://www.instagram.com`, '_blank');
}
</script>
