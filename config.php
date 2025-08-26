<?php
// Database configuration
$host = 'clipwave.mysql.database.azure.com';
$username = 'clipwave@clipwave';
$password = 'Mydata12';
$database = 'clipwave';
$port = 3306;

// Path to SSL certificate
$ssl_cert_path = '/home/site/wwwroot/DigiCertGlobalRootCA.crt.pem';

// Create a new mysqli object
$conn = mysqli_init();

// Set SSL parameters
mysqli_ssl_set($conn, NULL, NULL, $ssl_cert_path, NULL, NULL);

// Connect to the database
mysqli_real_connect(
    $conn,
    $host,
    $username,
    $password,
    $database,
    $port,
    MYSQLI_CLIENT_SSL
);

// Check connection
if (mysqli_connect_errno()) {
    die('Connection failed: ' . mysqli_connect_error());
}
?>
