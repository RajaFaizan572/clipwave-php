<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "clipwave.mysql.database.azure.com";
$user = "ASP-clipwave-ae23";   // ✅ full format
$password = "Mydata12";
$database = "clipwave";
$port = 3306;

$ssl_cert = __DIR__ . "DigiCertGlobalRootCA.crt.pem";

$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, $ssl_cert, NULL, NULL);
mysqli_real_connect($conn, $host, $user, $password, $database, $port, NULL, MYSQLI_CLIENT_SSL);

if (mysqli_connect_errno($conn)) {
    die("❌ Failed to connect: " . mysqli_connect_error());
} else {
    echo "✅ Connected to Azure MySQL!";
}
?>
