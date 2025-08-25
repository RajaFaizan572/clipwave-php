<?php
$host = 'localhost';
$dbname = 'clipwave';
$username = 'root'; // Change this to your MySQL username
$password = ''; // Change this to your MySQL password

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
