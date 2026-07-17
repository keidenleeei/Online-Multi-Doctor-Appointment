<?php
session_start();
include "config.php";

$error = "";
$success = "";

if (isset($_GET['registered'])) {
    $success = "Account created successfully! Please sign in.";
}

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Prepare statement to query user by email
    $stmt = $conn->prepare("SELECT user_id, full_name, password, role FROM users WHERE email = ?");
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify password using bcrypt hash, with a plaintext fallback for pre-existing records
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on user role
                if ($_SESSION['role'] == "admin") {
                    header("Location: admin.php");
                } elseif ($_SESSION['role'] == "doctor") {
                    header("Location: doctor_dashboard.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
        $stmt->close();
    } else {
        $error = "System error. Please try again later.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bird Safety Appointment System</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
<section class="login-page">
    <div class="login-bg"></div>

    <div class="login-wrapper">
        <div class="login-left">
            <div class="login-brand">
                <svg viewBox="0 0 256 256" width="36" height="36" aria-hidden="true" style="color: var(--accent);">
                    <path fill="currentColor" d="M144 256 L27.598 256 L144 139.598 Z M256 207.5 L200 256 L200 56 L0 56 L48 0 L256 0 Z M0 204.402 L0 112 L92.402 112 Z" />
                </svg>
                <div>
                    <h2>Bird Safety</h2>
                    <small>Appointment System</small>
                </div>
            </div>

            <h1>Welcome Back</h1>
            <p>We are here to help make sure your birds are safe and healthy. Log in to manage your appointments, schedules, and patient profiles.</p>
        </div>

        <div class="login-card">
            <h2>Sign In</h2>
            <p class="login-subtitle">Login to your workspace account</p>

            <?php if ($error != "") { ?>
                <p style="color: var(--danger); background: var(--danger-light); padding: 10px; border-radius: 8px; font-size: 14px; margin-bottom: 15px; font-weight: 500;">
                    ⚠️ <?php echo htmlspecialchars($error); ?>
                </p>
            <?php } ?>

            <?php if ($success != "") { ?>
                <p style="color: var(--accent-2); background: var(--accent-2-light); padding: 10px; border-radius: 8px; font-size: 14px; margin-bottom: 15px; font-weight: 500;">
                    ✅ <?php echo htmlspecialchars($success); ?>
                </p>
            <?php } ?>

            <form method="POST" action="login.php">
                <div class="login-field">
                    <label>Email Address</label>
                    <input
                        type="email"
                        name="email"
                        placeholder="Enter your email"
                        required
                    />
                </div>

                <div class="login-field">
                    <label>Password</label>
                    <input
                        type="password"
                        name="password"
                        placeholder="Enter password"
                        required
                    />
                </div>

                <div class="login-options">
                    <label>
                        <input type="checkbox"> Remember me
                    </label>
                    <a href="#">Forgot Password?</a>
                </div>

                <button class="login-btn" type="submit" name="login">Login</button>
            </form>

            <p class="login-register">
                Don't have an account? <a href="register.php">Register</a>
            </p>
        </div>
    </div>
</section>
</body>
</html>