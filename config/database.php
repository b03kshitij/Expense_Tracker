<?php

$database_url = getenv("DATABASE_URL");

//echo getenv("DATABASE_URL");
//exit;

if (!$database_url) {
    die("DATABASE_URL not set");
}

$db = parse_url($database_url);

$host = $db['host'] ?? null;
$user = $db['user'] ?? null;
$pass = $db['pass'] ?? null;
$dbname = isset($db['path']) ? ltrim($db['path'], '/') : null;
$port = $db['port'] ?? 3306;

$conn = new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>