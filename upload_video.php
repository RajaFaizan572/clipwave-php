
<?php
session_start();
include('config.php');
include('header.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// For Azure App Service, we need to check specific paths for uploads
$uploads = 'uploads';
// Alternative path for Azure if needed: $uploads = 'D:\home\site\wwwroot\uploads';

if (!is_dir($uploads)) {
    if (!mkdir($uploads, 0755, true)) {
        die('Error: Failed to create upload directory.');
    }
} elseif (!is_writable($uploads)) {
    die('Error: Upload directory is not writable.');
}

function toBytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $num  = (int)$val;
    switch ($last) {
        case 'g': $num *= 1024;
        case 'm': $num *= 1024;
        case 'k': $num *= 1024;
    }
    return $num;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title      = trim($_POST['title'] ?? '');
    $genre      = trim($_POST['genre'] ?? '');
    $age_rating = trim($_POST['age_rating'] ?? '');
    $file       = $_FILES['video_file'] ?? null;

    // Validate form fields
    if (empty($title) || empty($genre) || empty($age_rating)) {
        die('Error: All fields are required.');
    }

    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE   => 'File exceeds server limit.',
            UPLOAD_ERR_FORM_SIZE  => 'File exceeds form limit. Please check Azure configuration.',
            UPLOAD_ERR_PARTIAL    => 'Partial upload.',
            UPLOAD_ERR_NO_FILE    => 'No file uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temp dir.',
            UPLOAD_ERR_CANT_WRITE => 'Disk write failed.',
            UPLOAD_ERR_EXTENSION  => 'Upload blocked by extension.',
        ];
        die('Upload failed: '.($errors[$file['error']] ?? 'Unknown error'));
    }

    // Size policy - Azure might have different limits
    $serverMax = min(
        toBytes(ini_get('upload_max_filesize')),
        toBytes(ini_get('post_max_size'))
    );
    $appMax = 500 * 1024 * 1024; // 500MB app policy
   
    if ($file['size'] > min($serverMax, $appMax)) {
        die('File too large. Maximum size allowed: ' .
            round(min($serverMax, $appMax) / (1024*1024)) . 'MB. ' .
            'Please check your Azure PHP configuration.');
    }

    // MIME validate (mp4 only)
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    $allowed_mimes = ['video/mp4', 'video/mpeg', 'application/octet-stream'];
   
    if (!in_array($mime, $allowed_mimes)) {
        die('Invalid file type. Only MP4 videos allowed. Detected: ' . $mime);
    }

    // Check file extension
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($file_ext !== 'mp4') {
        die('Invalid file extension. Only .mp4 files allowed.');
    }

    // Save file
    $safeBase = preg_replace('/[^A-Za-z0-9.-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
    $target   = $uploads . '/' . uniqid('vid_', true) . '_' . $safeBase . '.mp4';

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        die('Could not save file. Check directory permissions.');
    }

    // DB insert (mysqli)
    $user_id = $_SESSION['user_id'];
    $url     = 'uploads/' . basename($target);

    $stmt = $conn->prepare("INSERT INTO videos (title, genre, age_rating, url, user_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssi', $title, $genre, $age_rating, $url, $user_id);
   
    if ($stmt->execute()) {
        echo 'Video uploaded successfully: ' . htmlspecialchars($url);
    } else {
        // Delete the uploaded file if DB insert failed
        unlink($target);
        die('Database error: Could not save video information.');
    }
   
    $stmt->close();
}
?>

<!-- Upload form -->
<div class="container">
    <h2>Upload Video</h2>
    <p>Maximum file size: <?php echo round(min(
        toBytes(ini_get('upload_max_filesize')),
        toBytes(ini_get('post_max_size')),
        500 * 1024 * 1024
    ) / (1024*1024)); ?>MB</p>
    <p>If you need to upload larger files, please check the Azure PHP configuration.</p>
   
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="MAX_FILE_SIZE" value="524288000">
        <div class="form-group">
            <input type="text" name="title" placeholder="Video Title" required>
        </div>
        <div class="form-group">
            <input type="text" name="genre" placeholder="Video Genre" required>
        </div>
        <div class="form-group">
            <input type="text" name="age_rating" placeholder="Age Rating" required>
        </div>
        <div class="form-group">
            <input type="file" name="video_file" accept="video/mp4" required>
        </div>
        <button type="submit">Upload Video</button>
    </form>
</div>

<?php include('footer.php'); ?>
