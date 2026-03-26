<?php
$pageTitle = 'Profile';
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/session.php';
requireLogin();

$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['full_name']);
        if (empty($name)) { $error = "Name is required."; }
        else {
            $stmt = $conn->prepare("UPDATE users SET full_name = ? WHERE id = ?");
            $stmt->bind_param("si", $name, $userId);
            $stmt->execute();
            $_SESSION['user_name'] = $name;
            $success = "Profile updated.";
            $user['full_name'] = $name;
        }
    } elseif (isset($_POST['change_password'])) {
        $current = $_POST['current_password'];
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];
        if (!password_verify($current, $user['password'])) { $error = "Current password is wrong."; }
        elseif (strlen($new) < 6) { $error = "New password must be at least 6 characters."; }
        elseif ($new !== $confirm) { $error = "Passwords don't match."; }
        else {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed, $userId);
            $stmt->execute();
            $success = "Password changed successfully.";
        }
    }
}
?>
<div class="container py-4" style="max-width:640px;">
    <h1 class="h3 fw-bold mb-4 fade-in-up" style="letter-spacing:-0.02em;">Settings</h1>

    <?php if ($error): ?><div class="alert alert-danger py-2 rounded-3" style="font-size:0.875rem;"><i class="bi bi-exclamation-circle me-1"></i><?php echo $error; ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success py-2 rounded-3" style="font-size:0.875rem;"><i class="bi bi-check-circle me-1"></i><?php echo $success; ?></div><?php endif; ?>

    <div class="card mb-4 fade-in-up">
        <div class="card-header py-3"><span class="fw-semibold">Profile Information</span></div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                    <small class="text-secondary">Email cannot be changed.</small>
                </div>
                <button type="submit" name="update_profile" class="btn btn-accent">Save Changes</button>
            </form>
        </div>
    </div>

    <div class="card fade-in-up">
        <div class="card-header py-3"><span class="fw-semibold">Change Password</span></div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" class="form-control" required minlength="6">
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" name="change_password" class="btn btn-accent">Update Password</button>
            </form>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
