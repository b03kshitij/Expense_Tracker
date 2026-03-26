<?php

$DATABASE_URL = "mysql://appuser:AppUser%40123@crossover.proxy.rlwy.net:3306/railway";

$db = parse_url($DATABASE_URL);

if ($db === false) {
    die("Invalid DATABASE URL");
}

$host = $db['host'];
$user = $db['user'];
$pass = $db['pass'];
$dbname = ltrim($db['path'], '/');
$port = isset($db['port']) ? $db['port'] : 3306;

// Debug (temporary)
echo "Host: " . $host . "<br>";
echo "User: " . $user . "<br>";
echo "Pass: " . $pass . "<br>";
echo "DB: " . $dbname . "<br>";
echo "Port: " . $port . "<br>";
exit;

$conn = new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

?>