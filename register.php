<?php
session_start();
include('config.php');
include('header.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Check if username already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<p class='error'>Username already exists. Please choose a different one.</p>";
    } else {
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (username, password_hash, email) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $password_hash, $email);
        $stmt->execute();

        // Log the user in
        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['username'] = $username;

        // Redirect to home page
        header("Location: index.php");
        exit();
    }
}
?>

<div class="container">
    <div class="form-container">
        <h2>Create an Account</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</div>

<?php include('footer.php'); ?>
