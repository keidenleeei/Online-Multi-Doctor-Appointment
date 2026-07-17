<?php
session_start();
include "config.php";

$message = "";

if (isset($_POST['register'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if ($password != $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        // Prepare statement to check if email exists
        $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        if ($check_stmt) {
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                $message = "Email already exists.";
            } else {
                // Securely hash the password using bcrypt
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Prepare statement to insert new patient user
                $insert_stmt = $conn->prepare("INSERT INTO users (full_name, email, password, phone, role) VALUES (?, ?, ?, ?, 'patient')");
                if ($insert_stmt) {
                    $insert_stmt->bind_param("ssss", $full_name, $email, $hashed_password, $phone);
                    if ($insert_stmt->execute()) {
                        header("Location: login.php?registered=1");
                        exit();
                    } else {
                        $message = "Registration failed. Please try again.";
                    }
                    $insert_stmt->close();
                } else {
                    $message = "System error. Please try again later.";
                }
            }
            $check_stmt->close();
        } else {
            $message = "System error. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Bird Safety Appointment System</title>
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

            <h1>Welcome to Caring Hands</h1>
            <p>We are here to help make sure your birds are safe, healthy, and happy. Schedule your veterinary visits easily.</p>
        </div>

        <div class="login-card">
            <h2>Create Account</h2>
            <p class="login-subtitle">Register a new patient account</p>

            <?php if ($message != "") { ?>
                <p style="color: var(--danger); background: var(--danger-light); padding: 10px; border-radius: 8px; font-size: 14px; margin-bottom: 15px; font-weight: 500;">
                    ⚠️ <?php echo htmlspecialchars($message); ?>
                </p>
            <?php } ?>

            <form method="POST" action="register.php">
                <div class="login-field">
                    <label>Full Name</label>
                    <input
                        type="text"
                        name="full_name"
                        placeholder="Enter your full name"
                        required
                    />
                </div>

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
                    <label>Phone Number</label>
                    <input
                        type="text"
                        name="phone"
                        placeholder="Enter your phone number"
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

                <div class="login-field">
                    <label>Confirm Password</label>
                    <input
                        type="password"
                        name="confirm_password"
                        placeholder="Confirm password"
                        required
                    />
                </div>

                <button class="login-btn" type="submit" name="register">Create Account</button>
            </form>

            <p class="login-register">
                Already have an account? <a href="login.php">Login</a>
            </p>
        </div>
    </div>
</section>
</body>
</html>