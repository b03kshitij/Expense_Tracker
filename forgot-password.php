<?php
$pageTitle = 'Forgot Password';
require_once 'config/database.php';
require_once 'includes/session.php';

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email.";
    } else {
        $stmt = $conn->prepare("SELECT id, full_name FROM users WHERE email = ? AND is_verified = 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($user = $result->fetch_assoc()) {
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $otpExpiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            $update = $conn->prepare("UPDATE users SET otp = ?, otp_expiry = ? WHERE id = ?");
            $update->bind_param("ssi", $otp, $otpExpiry, $user['id']);
            $update->execute();
            
            //$subject = "ExpenseTracker - Password Reset OTP";
            //$message = "Hello {$user['full_name']},\n\nYour password reset OTP: $otp\nExpires in 10 minutes.";
            //$headers = "From: noreply@expensetracker.com";
            //mail($email, $subject, $message, $headers);
            
           // require_once 'config/mailer.php';
            //sendOTP($email, $otp);


            /*if (sendOTP($email, $otp)) {
    echo "OTP SENT";
} else {
    echo "FAILED";
}
exit;*/


     require_once 'config/mailer.php';
sendOTP($email, $otp);

$_SESSION['reset_email'] = $email;
header("Location: reset-password.php");
exit;
            $_SESSION['reset_email'] = $email;
            header("Location: reset-password.php");
            exit;
        }
        $success = "If an account exists with that email, an OTP has been sent.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - ExpenseTracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card fade-in-up">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:64px;height:64px;background:#fef3c7;">
                <i class="bi bi-key fs-3" style="color: #d97706;"></i>
            </div>
            <h2>Forgot Password?</h2>
            <p class="subtitle">Enter your email to receive a reset OTP</p>
        </div>
        <?php if ($error): ?><div class="alert alert-danger py-2 px-3 rounded-3" style="font-size:0.875rem;"><i class="bi bi-exclamation-circle me-1"></i><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success py-2 px-3 rounded-3" style="font-size:0.875rem;"><i class="bi bi-check-circle me-1"></i><?php echo $success; ?></div><?php endif; ?>
        <form method="POST">
            <div class="mb-4">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="john@example.com" required>
            </div>
            <button type="submit" class="btn btn-accent w-100 py-2">Send Reset OTP</button>
        </form>
        <p class="text-center mt-3 mb-0" style="font-size:0.875rem;"><a href="login.php" class="text-decoration-none" style="color:var(--accent);">← Back to Login</a></p>
    </div>
</div>
</body></html>
