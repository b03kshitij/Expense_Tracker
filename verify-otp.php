<?php
$pageTitle = 'Verify Email';
require_once 'config/database.php';
require_once 'includes/session.php';

if (isLoggedIn()) { header("Location: index.php"); exit; }
if (!isset($_SESSION['verify_email'])) { header("Location: register.php"); exit; }

$email = $_SESSION['verify_email'];
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['resend'])) {
        // Resend OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $otpExpiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        $stmt = $conn->prepare("UPDATE users SET otp = ?, otp_expiry = ? WHERE email = ?");
        $stmt->bind_param("sss", $otp, $otpExpiry, $email);
        $stmt->execute();

        $subject = "ExpenseTracker - New OTP";
        $message = "Your new OTP is: $otp\n\nExpires in 10 minutes.";
        $headers = "From: noreply@expensetracker.com";
        mail($email, $subject, $message, $headers);

        $success = "A new OTP has been sent to your email.";
    } else {
        $otp = implode('', $_POST['otp'] ?? []);
        if (strlen($otp) !== 6) {
            $error = "Please enter the complete 6-digit OTP.";
        } else {
            $stmt = $conn->prepare("SELECT id, otp, otp_expiry FROM users WHERE email = ? AND is_verified = 0");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                if ($row['otp'] === $otp && strtotime($row['otp_expiry']) > time()) {
                    $update = $conn->prepare("UPDATE users SET is_verified = 1, otp = NULL, otp_expiry = NULL WHERE id = ?");
                    $update->bind_param("i", $row['id']);
                    $update->execute();
                    unset($_SESSION['verify_email']);
                    $_SESSION['verified_success'] = true;
                    header("Location: login.php");
                    exit;
                } elseif (strtotime($row['otp_expiry']) <= time()) {
                    $error = "OTP has expired. Please request a new one.";
                } else {
                    $error = "Invalid OTP. Please try again.";
                }
            } else {
                $error = "Account not found or already verified.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - ExpenseTracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card fade-in-up">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:64px;height:64px;background:#dcfce7;">
                <i class="bi bi-envelope-check fs-3" style="color: var(--accent);"></i>
            </div>
            <h2>Verify Your Email</h2>
            <p class="subtitle">Enter the 6-digit OTP sent to<br><strong><?php echo htmlspecialchars($email); ?></strong></p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 px-3 rounded-3" style="font-size:0.875rem;">
                <i class="bi bi-exclamation-circle me-1"></i><?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success py-2 px-3 rounded-3" style="font-size:0.875rem;">
                <i class="bi bi-check-circle me-1"></i><?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="otp-inputs mb-4">
                <?php for ($i = 0; $i < 6; $i++): ?>
                    <input type="text" name="otp[]" maxlength="1" pattern="[0-9]" inputmode="numeric" required class="otp-input">
                <?php endfor; ?>
            </div>
            <button type="submit" class="btn btn-accent w-100 py-2 mb-3">
                <i class="bi bi-shield-check me-2"></i>Verify OTP
            </button>
        </form>
        <form method="POST" class="text-center">
            <input type="hidden" name="resend" value="1">
            <p class="mb-0" style="font-size:0.875rem;">Didn't receive the code?
                <button type="submit" class="btn btn-link p-0 text-decoration-none" style="color:var(--accent);font-weight:600;font-size:0.875rem;">Resend OTP</button>
            </p>
        </form>
    </div>
</div>

<script>
// Auto-focus and auto-advance OTP inputs
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.otp-input');
    inputs[0].focus();
    inputs.forEach((input, index) => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value && index < inputs.length - 1) inputs[index + 1].focus();
        });
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !this.value && index > 0) inputs[index - 1].focus();
        });
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const data = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
            for (let i = 0; i < Math.min(data.length, 6); i++) {
                inputs[i].value = data[i];
            }
            inputs[Math.min(data.length, 5)].focus();
        });
    });
});
</script>
</body>
</html>
