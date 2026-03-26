<?php
$pageTitle = 'Login';
require_once 'config/database.php';
require_once 'includes/session.php';


if (isLoggedIn()) { header("Location: index.php"); exit; }

$error = '';
$verified = isset($_SESSION['verified_success']);
unset($_SESSION['verified_success']);

// Check remember cookie
$rememberedEmail = $_COOKIE['remember_email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $stmt = $conn->prepare("SELECT id, full_name, email, password, is_verified FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (!$user['is_verified']) {
                $_SESSION['verify_email'] = $email;
                header("Location: verify-otp.php");
                exit;
            }
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];

                if ($remember) { setRememberCookie($user['id'], $email); }
                header("Location: index.php");
                exit;
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ExpenseTracker</title>
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
            <h2 class="mt-2">Welcome Back</h2>
            <p class="subtitle">Sign in to your expense tracker</p>
        </div>

        <?php if ($verified): ?>
            <div class="alert alert-success py-2 px-3 rounded-3" style="font-size:0.875rem;">
                <i class="bi bi-check-circle me-1"></i>Email verified successfully! You can now log in.
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 px-3 rounded-3" style="font-size:0.875rem;">
                <i class="bi bi-exclamation-circle me-1"></i><?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="john@example.com" required
                       value="<?php echo htmlspecialchars($rememberedEmail ?: ($_POST['email'] ?? '')); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="position-relative">
                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
                    <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-secondary" onclick="togglePassword('password', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember" <?php echo $rememberedEmail ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="remember" style="font-size:0.875rem;">Remember me</label>
                </div>
                <a href="forgot-password.php" class="text-decoration-none" style="color:var(--accent);font-size:0.875rem;font-weight:500;">Forgot Password?</a>
            </div>
            <button type="submit" class="btn btn-accent w-100 py-2">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
            </button>
        </form>

        <p class="text-center mt-3 mb-0" style="font-size:0.875rem;">
            Don't have an account? <a href="register.php" class="text-decoration-none" style="color:var(--accent);font-weight:600;">Sign Up</a>
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
</script>
</body>
</html>
