<?php
session_start();
include('config.php');
include('header.php');

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php'); exit;
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
  $title     = trim($_POST['title'] ?? '');
  $genre     = trim($_POST['genre'] ?? '');
  $age_rating= trim($_POST['age_rating'] ?? '');
  $file      = $_FILES['video_file'] ?? null;

  if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    $errors = [
      UPLOAD_ERR_INI_SIZE=>'File exceeds server limit.',
      UPLOAD_ERR_FORM_SIZE=>'File exceeds form limit.',
      UPLOAD_ERR_PARTIAL=>'Partial upload.',
      UPLOAD_ERR_NO_FILE=>'No file uploaded.',
      UPLOAD_ERR_NO_TMP_DIR=>'Missing temp dir.',
      UPLOAD_ERR_CANT_WRITE=>'Disk write failed.',
      UPLOAD_ERR_EXTENSION=>'Upload blocked by extension.',
    ];
    exit('Upload failed: '.($errors[$file['error']] ?? 'Unknown error'));
  }

  // Size policy (<= server limit)
  $serverMax = min(toBytes(ini_get('upload_max_filesize')), toBytes(ini_get('post_max_size')));
  $appMax    = 500 * 1024 * 1024; // 500MB app policy
  if ($file['size'] > min($serverMax, $appMax)) {
    exit('File too large.');
  }

  // MIME validate (mp4 only)
  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mime  = $finfo->file($file['tmp_name']);
  if ($mime !== 'video/mp4') {
    exit('Invalid file type. Only MP4 allowed.');
  }

  // Save file
  $uploads = '/uploads';

  if (!is_dir($uploads)) mkdir($uploads, 0755, true);

  $safeBase = preg_replace('/[^A-Za-z0-9.-]/', '', pathinfo($file['name'], PATHINFO_FILENAME));
  $target   = $uploads.'/'.uniqid('vid_', true).'_'.$safeBase.'.mp4';

  if (!move_uploaded_file($file['tmp_name'], $target)) {
    exit('Could not save file.');
  }

  // DB insert (mysqli)
  $user_id = $_SESSION['user_id'];
  $url     = 'uploads/'.basename($target);

  $stmt = $conn->prepare("INSERT INTO videos (title, genre, age_rating, url, user_id) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param('ssssi', $title, $genre, $age_rating, $url, $user_id);
  $stmt->execute();
  $stmt->close();

  echo 'Video uploaded successfully: '.$url;
}
?>

<!-- Upload form -->
<div class="container">
  <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="MAX_FILE_SIZE" value="524288000">
    <input type="text"   name="title"      placeholder="Video Title" required><br>
    <input type="text"   name="genre"      placeholder="Video Genre" required><br>
    <input type="text"   name="age_rating" placeholder="Age Rating"  required><br>
    <input type="file"   name="video_file" accept="video/mp4" required><br>
    <button type="submit">Upload Video</button>
  </form>
</div>

<?php include('footer.php'); ?>
