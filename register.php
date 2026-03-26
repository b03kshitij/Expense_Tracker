<?php
$pageTitle = 'Register';
require_once 'config/database.php';
require_once 'includes/session.php';
require_once __DIR__ . '/config/mailer.php';

if (isLoggedIn()) { header("Location: index.php"); exit; }

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $otpExpiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, otp, otp_expiry) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $email, $hashedPassword, $otp, $otpExpiry);
            /* 
            if ($stmt->execute()) {
                // Send OTP via email
                $subject = "ExpenseTracker - Email Verification OTP";
                $message = "Hello $name,\n\nYour OTP for email verification is: $otp\n\nThis OTP expires in 10 minutes.\n\nThank you!";
                $headers = "From: noreply@expensetracker.com\r\nContent-Type: text/plain; charset=UTF-8";
                //mail($email, $subject, $message, $headers);
                sendOTP($email, $otp);

                $_SESSION['verify_email'] = $email;
                header("Location: verify-otp.php");
                exit;
            } 
            */
            if ($stmt->execute()) 
            {

                require_once __DIR__ . '/config/mailer.php';

                sendOTP($email, $otp); // ✅ use PHPMailer

                $_SESSION['verify_email'] = $email;
                header("Location: verify-otp.php");
                exit;
            }
            else {
                $error = "Registration failed. Please try again.";
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - ExpenseTracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card fade-in-up">
        <div class="text-center mb-4">
            <i class="bi bi-wallet2 fs-1" style="color: var(--accent);"></i>
            <h2 class="mt-2">Create Account</h2>
            <p class="subtitle">Start tracking your expenses today</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 px-3 rounded-3" style="font-size:0.875rem;">
                <i class="bi bi-exclamation-circle me-1"></i><?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="registerForm">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="full_name" class="form-control" placeholder="John Doe" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="john@example.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                <div class="invalid-feedback">Please enter a valid email address.</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="position-relative">
                    <input type="password" name="password" id="password" class="form-control" placeholder="Min 6 characters" required minlength="6">
                    <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-secondary" onclick="togglePassword('password', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Re-enter password" required>
            </div>
            <button type="submit" class="btn btn-accent w-100 py-2">
                <i class="bi bi-person-plus me-2"></i>Create Account
            </button>
        </form>

        <p class="text-center mt-3 mb-0" style="font-size:0.875rem;">
            Already have an account? <a href="login.php" class="text-decoration-none" style="color:var(--accent);font-weight:600;">Sign In</a>
        </p>
    </div>
</div>

<script>
function togglePassword(id, btn) {
    const input = document.getElementById(id);
    const icon = btn.querySelector('i');
    if (input.type === 'password') { input.type = 'text'; icon.className = 'bi bi-eye-slash'; }
    else { input.type = 'password'; icon.className = 'bi bi-eye'; }
}

// Client-side email validation
document.querySelector('input[name="email"]').addEventListener('blur', function() {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (this.value && !re.test(this.value)) {
        this.classList.add('is-invalid');
    } else {
        this.classList.remove('is-invalid');
    }
});
</script>
</body>
</html>
