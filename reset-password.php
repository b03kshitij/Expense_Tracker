<?php
require_once 'config/database.php';
require_once 'includes/session.php';

if (!isset($_SESSION['reset_email'])) { header("Location: forgot-password.php"); exit; }
$email = $_SESSION['reset_email'];
$error = $success = '';
$step = 'otp'; // otp or password

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['verify_otp'])) {
        $otp = implode('', $_POST['otp'] ?? []);
        $stmt = $conn->prepare("SELECT id, otp, otp_expiry FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        if ($user && $user['otp'] === $otp && strtotime($user['otp_expiry']) > time()) {
            $_SESSION['reset_verified'] = true;
            $step = 'password';
        } else {
            $error = strtotime($user['otp_expiry'] ?? 'now') <= time() ? "OTP expired." : "Invalid OTP.";
        }
    } elseif (isset($_POST['reset_password'])) {
        if (!isset($_SESSION['reset_verified'])) { $error = "Please verify OTP first."; }
        else {
            $password = $_POST['password'];
            $confirm = $_POST['confirm_password'];
            if (strlen($password) < 6) { $error = "Password must be at least 6 characters."; $step = 'password'; }
            elseif ($password !== $confirm) { $error = "Passwords don't match."; $step = 'password'; }
            else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ?, otp = NULL, otp_expiry = NULL WHERE email = ?");
                $stmt->bind_param("ss", $hashed, $email);
                $stmt->execute();
                unset($_SESSION['reset_email'], $_SESSION['reset_verified']);
                $_SESSION['verified_success'] = true;
                header("Location: login.php");
                exit;
            }
        }
    }
}
if (isset($_SESSION['reset_verified'])) $step = 'password';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - ExpenseTracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card fade-in-up">
        <div class="text-center mb-4">
            <h2><?php echo $step === 'otp' ? 'Enter OTP' : 'New Password'; ?></h2>
            <p class="subtitle"><?php echo $step === 'otp' ? "OTP sent to <strong>".htmlspecialchars($email)."</strong>" : 'Create your new password'; ?></p>
        </div>
        <?php if ($error): ?><div class="alert alert-danger py-2 px-3 rounded-3" style="font-size:0.875rem;"><i class="bi bi-exclamation-circle me-1"></i><?php echo $error; ?></div><?php endif; ?>

        <?php if ($step === 'otp'): ?>
        <form method="POST">
            <div class="otp-inputs mb-4">
                <?php for ($i = 0; $i < 6; $i++): ?>
                    <input type="text" name="otp[]" maxlength="1" pattern="[0-9]" inputmode="numeric" required class="otp-input">
                <?php endfor; ?>
            </div>
            <button type="submit" name="verify_otp" class="btn btn-accent w-100 py-2">Verify OTP</button>
        </form>
        <?php else: ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control" required minlength="6" placeholder="Min 6 characters">
            </div>
            <div class="mb-4">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required placeholder="Re-enter password">
            </div>
            <button type="submit" name="reset_password" class="btn btn-accent w-100 py-2">Reset Password</button>
        </form>
        <?php endif; ?>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.otp-input');
    if (inputs.length) {
        inputs[0].focus();
        inputs.forEach((input, i) => {
            input.addEventListener('input', function() { this.value = this.value.replace(/[^0-9]/g, ''); if (this.value && i < 5) inputs[i+1].focus(); });
            input.addEventListener('keydown', function(e) { if (e.key === 'Backspace' && !this.value && i > 0) inputs[i-1].focus(); });
        });
    }
});
</script>
</body></html>
