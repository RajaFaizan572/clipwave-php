<?php
// Database configuration
$host = 'clipwave.mysql.database.azure.com';
$username = 'clipwave'; // Azure MySQL requires the username without @servername for initial connection
$admin_username = 'clipwave@clipwave'; // Use this format for certain Azure contexts if needed
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
    $username, // Using the username without @servername here
    $password,
    $database,
    $port,
    NULL, // Use NULL instead of MYSQLI_CLIENT_SSL if socket is not needed
    MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT // Common flag for Azure
);

// Check connection
if (mysqli_connect_errno()) {
    die('Connection failed: ' . mysqli_connect_error());
}

echo "Connected successfully!";
?>
