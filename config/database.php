<?php
define('DB_HOST', 'crossover.proxy.rlwy.net');
define('DB_USER', 'root');
define('DB_PASS', 'wpcngszFbJyUgeTwSuidMOcguufeSnsL');
define('DB_NAME', 'railway');
define('DB_PORT', 58793);

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>