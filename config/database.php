<?php

$DATABASE_URL = "mysql://root:wpcngszFbJyUgeTwSuidMOcguufeSnsL@crossover.proxy.rlwy.net:58793/railway";

$db = parse_url($DATABASE_URL);

if ($db === false) {
    die("Invalid DATABASE URL");
}

$host = $db['host'];
$user = $db['user'];
$pass = $db['pass'];
$dbname = ltrim($db['path'], '/');
$port = isset($db['port']) ? $db['port'] : 3306;

$conn = new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>