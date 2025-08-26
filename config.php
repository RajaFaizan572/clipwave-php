<?php
$host = 'clipwave.mysql.database.azure.com';
$dbname = 'clipwave';
$username = 'clipwave'; // Change this to your MySQL username
$password = 'Mydata12'; // Change this to your MySQL password

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
