<?php
define('DB_HOST', 'mysql://root:wpncgszFbJyUgeTwSuidMOcguufeSnsL@crossover.proxy.rlwy.net:58793/railway');
define('DB_USER', 'root');
define('DB_PASS', 'wpncgszFbJyUgeTwSuidMOcguufeSnsL');
define('DB_NAME', 'railway');
define('DB_PORT', 3306);

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>