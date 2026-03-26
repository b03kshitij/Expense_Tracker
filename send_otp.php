<?php

// DEBUG
//echo "API HIT"; 
//exit;



session_start();

header("Content-Type: application/json");

// include mailer
require_once __DIR__ . '/config/mailer.php';

// get JSON data from frontend
$data = json_decode(file_get_contents("php://input"), true);

// validate email
if (!isset($data['email']) || empty($data['email'])) {
    echo json_encode([
        "success" => false,
        "message" => "Email is required"
    ]);
    exit;
}

$email = trim($data['email']);

//Add email format validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid email format"
    ]);
    exit;
}

// generate 6-digit OTP
$otp = rand(100000, 999999);

// store in session
$_SESSION['otp'] = $otp;
$_SESSION['otp_email'] = $email;

// send OTP
if (sendOTP($email, $otp)) {
    echo json_encode([
        "success" => true,
        "message" => "OTP sent successfully"
    ]);
    exit;
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to send OTP"
    ]);
    exit;
}