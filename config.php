<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "clipwave.mysql.database.azure.com";
$user = "clipwave@clipwave";   // ✅ full format
$password = "Mydata12";
$database = "clipwave";
$port = 3306;

$ssl_cert = __DIR__ . "DigiCertGlobalRootCA.crt.pem";

$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, $ssl_cert, NULL, NULL);

if (!mysqli_real_connect($conn, $host, $user, $password, $database, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("❌ Connection failed: (" . mysqli_connect_errno() . ") " . mysqli_connect_error());
} else {
    echo "✅ Connected successfully!";
}
?>
