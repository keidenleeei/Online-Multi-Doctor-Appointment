<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (($_SESSION['role'] ?? '') !== "admin") {
    die("Access Denied.");
}

if (!isset($_GET['doctor_id'])) {
    header("Location: admin.php");
    exit();
}

$doctor_id = (int)$_GET['doctor_id'];

if (isset($_POST['update_doctor'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $specialization = trim($_POST['specialization']);
    $experience = (int)$_POST['experience'];
    $consultation_fee = (float)$_POST['consultation_fee'];

    $user_stmt = $conn->prepare("SELECT user_id FROM doctors WHERE doctor_id = ?");
    if ($user_stmt) {
        $user_stmt->bind_param("i", $doctor_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();

        if ($user_result->num_rows > 0) {
            $user_data = $user_result->fetch_assoc();
            $user_id = (int)$user_data['user_id'];

            $update_user_stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE user_id = ?");
            if ($update_user_stmt) {
                $update_user_stmt->bind_param("sssi", $full_name, $email, $phone, $user_id);
                $update_user_stmt->execute();
                $update_user_stmt->close();
            }

            $update_doc_stmt = $conn->prepare("UPDATE doctors SET specialization = ?, experience = ?, consultation_fee = ? WHERE doctor_id = ?");
            if ($update_doc_stmt) {
                $update_doc_stmt->bind_param("sidi", $specialization, $experience, $consultation_fee, $doctor_id);
                $update_doc_stmt->execute();
                $update_doc_stmt->close();
            }

            header("Location: admin.php?updated=1");
            exit();
        }

        $user_stmt->close();
    }
}

$doctor = null;
$detail_stmt = $conn->prepare("
    SELECT users.full_name, users.email, users.phone,
           doctors.specialization, doctors.experience, doctors.consultation_fee
    FROM doctors
    INNER JOIN users ON doctors.user_id = users.user_id
    WHERE doctors.doctor_id = ?
");

if ($detail_stmt) {
    $detail_stmt->bind_param("i", $doctor_id);
    $detail_stmt->execute();
    $detail_result = $detail_stmt->get_result();
    if ($detail_result->num_rows > 0) {
        $doctor = $detail_result->fetch_assoc();
    } else {
        die("Doctor profile not found.");
    }
    $detail_stmt->close();
} else {
    die("Database query error.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Doctor - MedLink Appointment System</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body class="page-bg">
  <div class="page-glow page-glow-a"></div>
  <div class="page-glow page-glow-b"></div>

  <header class="site-header content-header">
    <div class="wrap header-inner">
      <a class="brand" href="index.php">
        <span class="brand-mark">ML</span>
        <span>
          MedLink
          <small>Appointment System</small>
        </span>
      </a>

      <nav class="nav">
        <a href="index.php">Home</a>
        <a href="about.php">About</a>
        <a href="features.php">Features</a>
        <a href="doctors.php">Doctors</a>
        <a href="dashboard.php">Dashboard</a>
        <a class="active" href="admin.php">Admin</a>
        <a href="logout.php" style="color: var(--danger);">Logout</a>
      </nav>
    </div>
  </header>

  <main class="wrap page-main" style="max-width: 680px;">
    <div style="margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
      <div>
        <h1 style="color: var(--accent); margin: 0; font-weight: 700;">Edit Doctor</h1>
        <p style="color: var(--muted); margin-top: 0.25rem;">Modify specialist profiles and contact information.</p>
      </div>
      <a href="admin.php" class="btn secondary" style="min-height: 2.5rem; padding: 0.25rem 1rem; border-radius: 8px; font-size: 0.88rem;">&larr; Back</a>
    </div>

    <article class="card glass-card">
      <form method="POST" action="">
        <label class="field">
          Full Name
          <input type="text" name="full_name" value="<?php echo htmlspecialchars($doctor['full_name']); ?>" required />
        </label>

        <label class="field">
          Email Address
          <input type="email" name="email" value="<?php echo htmlspecialchars($doctor['email']); ?>" required />
        </label>

        <label class="field">
          Phone Number
          <input type="text" name="phone" value="<?php echo htmlspecialchars($doctor['phone']); ?>" required />
        </label>

        <label class="field">
          Clinical Specialization
          <input type="text" name="specialization" value="<?php echo htmlspecialchars($doctor['specialization']); ?>" required />
        </label>

        <label class="field">
          Years of Experience
          <input type="number" name="experience" value="<?php echo htmlspecialchars($doctor['experience']); ?>" required />
        </label>

        <label class="field">
          Consultation Fee (RM)
          <input type="number" step="0.01" name="consultation_fee" value="<?php echo htmlspecialchars($doctor['consultation_fee']); ?>" required />
        </label>

        <div class="actions" style="margin-top: 1.5rem;">
          <button class="btn primary" type="submit" name="update_doctor" style="border-radius: 12px; flex: 1;">Save Changes</button>
          <a href="admin.php" class="btn secondary" style="border-radius: 12px; flex: 1;">Cancel</a>
        </div>
      </form>
    </article>
  </main>

  <footer class="footer" style="margin-top: 5rem;">
    <div class="wrap">
      <p>&copy; <?php echo date('Y'); ?> MedLink Appointment System. All rights reserved.</p>
    </div>
  </footer>
</body>
</html>
