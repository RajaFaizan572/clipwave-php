<?php
$host = "clipwave.mysql.database.azure.com";
$user = "clipwave";  // use full format
$password = "Mydata12";
$database = "clipwave";
$port = 3306;

// SSL certificate path (download BaltimoreCyberTrustRoot.crt.pem)
$ssl_cert = __DIR__ . "/BaltimoreCyberTrustRoot.crt.pem";

$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, $ssl_cert, NULL, NULL);
mysqli_real_connect($conn, $host, $user, $password, $database, $port, NULL, MYSQLI_CLIENT_SSL);

if (mysqli_connect_errno($conn)) {
    die("Failed to connect: " . mysqli_connect_error());
} else {
    echo "✅ Connected to Azure MySQL!";
}
?>
