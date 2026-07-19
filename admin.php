<?php
session_start();
include "config.php";

// 1. Move Auth check to the very top
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] != "admin") {
    die("Access Denied.");
}

$alert_message = "";
$alert_type = "";

// 2. Secure Delete Doctor Handler
if (isset($_GET['delete_doctor'])) {
    $doctor_id = $_GET['delete_doctor'];

    // Find corresponding user_id
    $find_stmt = $conn->prepare("SELECT user_id FROM doctors WHERE doctor_id = ?");
    if ($find_stmt) {
        $find_stmt->bind_param("i", $doctor_id);
        $find_stmt->execute();
        $find_result = $find_stmt->get_result();
        
        if ($find_result->num_rows > 0) {
            $doctor = $find_result->fetch_assoc();
            $user_id = $doctor['user_id'];

            // Delete doctor record
            $del_doc_stmt = $conn->prepare("DELETE FROM doctors WHERE doctor_id = ?");
            $del_doc_stmt->bind_param("i", $doctor_id);
            $del_doc_stmt->execute();
            $del_doc_stmt->close();

            // Delete user account
            $del_user_stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            $del_user_stmt->bind_param("i", $user_id);
            $del_user_stmt->execute();
            $del_user_stmt->close();

            $alert_message = "Doctor account deleted successfully.";
            $alert_type = "success";
        }
        $find_stmt->close();
    }
}

// 3. Secure Add Doctor Handler
if (isset($_POST['add_doctor'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $phone = trim($_POST['phone']);
    $specialization = trim($_POST['specialization']);
    $experience = intval($_POST['experience']);
    $consultation_fee = floatval($_POST['consultation_fee']);

    // Check if email already exists
    $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    if ($check_stmt) {
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $alert_message = "Email address already exists.";
            $alert_type = "error";
        } else {
            // Hash password securely using bcrypt
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert into users
            $user_stmt = $conn->prepare("INSERT INTO users (full_name, email, password, phone, role) VALUES (?, ?, ?, ?, 'doctor')");
            if ($user_stmt) {
                $user_stmt->bind_param("ssss", $full_name, $email, $hashed_password, $phone);
                if ($user_stmt->execute()) {
                    $new_user_id = $conn->insert_id;

                    // Insert into doctors
                    $doc_stmt = $conn->prepare("INSERT INTO doctors (user_id, specialization, experience, consultation_fee) VALUES (?, ?, ?, ?)");
                    if ($doc_stmt) {
                        $doc_stmt->bind_param("isid", $new_user_id, $specialization, $experience, $consultation_fee);
                        $doc_stmt->execute();
                        $doc_stmt->close();
                    }

                    $alert_message = "New doctor profile created successfully.";
                    $alert_type = "success";
                } else {
                    $alert_message = "Failed to create user account.";
                    $alert_type = "error";
                }
                $user_stmt->close();
            }
        }
        $check_stmt->close();
    }
}

// 4. Secure Confirm Appointment Handler
if (isset($_GET['confirm'])) {
    $appointment_id = $_GET['confirm'];

    $confirm_stmt = $conn->prepare("UPDATE appointments SET status = 'Confirmed' WHERE appointment_id = ?");
    if ($confirm_stmt) {
        $confirm_stmt->bind_param("i", $appointment_id);
        $confirm_stmt->execute();
        $confirm_stmt->close();
        $alert_message = "Appointment confirmed successfully.";
        $alert_type = "success";
    }
}

// 5. Secure Cancel Appointment Handler
if (isset($_GET['cancel'])) {
    $appointment_id = $_GET['cancel'];

    $cancel_stmt = $conn->prepare("UPDATE appointments SET status = 'Cancelled' WHERE appointment_id = ?");
    if ($cancel_stmt) {
        $cancel_stmt->bind_param("i", $appointment_id);
        $cancel_stmt->execute();
        $cancel_stmt->close();
        $alert_message = "Appointment cancelled successfully.";
        $alert_type = "success";
    }
}

// Fetch Appointments list
$appointments_sql = "
    SELECT appointments.appointment_id, appointments.appointment_date, appointments.status, 
           patient.full_name AS patient_name, doctor.full_name AS doctor_name, doctors.specialization 
    FROM appointments 
    INNER JOIN users AS patient ON appointments.patient_id = patient.user_id 
    INNER JOIN doctors ON appointments.doctor_id = doctors.doctor_id 
    INNER JOIN users AS doctor ON doctors.user_id = doctor.user_id 
    ORDER BY appointments.appointment_date DESC, appointments.appointment_id DESC
";
$appointments_result = mysqli_query($conn, $appointments_sql);

// Fetch Doctors list
$doctors_sql = "
    SELECT doctors.doctor_id, users.full_name, doctors.specialization, doctors.experience, doctors.consultation_fee 
    FROM doctors 
    INNER JOIN users ON doctors.user_id = users.user_id
    ORDER BY users.full_name ASC
";
$doctors_result = mysqli_query($conn, $doctors_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel - Bird Safety Appointment System</title>
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

  <main class="wrap page-main">
    <div style="margin-bottom: 2rem;">
      <h1 style="color: var(--accent); margin: 0; font-weight: 700;">Admin Workspace</h1>
      <p style="color: var(--muted); margin-top: 0.25rem;">Monitor clinical schedules, approve appointments, and configure doctor accounts.</p>
    </div>

    <?php if ($alert_message != "") { ?>
      <div style="padding: 12px 16px; border-radius: 8px; margin-bottom: 2rem; font-size: 0.95rem; font-weight: 500;
        <?php echo $alert_type == 'success' ? 'color: #065f46; background: #d1fae5; border: 1px solid #a7f3d0;' : 'color: #b91c1c; background: #fee2e2; border: 1px solid #fca5a5;'; ?>">
        <?php echo $alert_type == 'success' ? '✅' : '⚠️'; ?> <?php echo htmlspecialchars($alert_message); ?>
      </div>
    <?php } ?>

    <!-- Section 1: Appointments List Redesign -->
    <section class="card glass-card" style="margin-bottom: 2.5rem; overflow-x: auto;">
      <h3 style="color: var(--accent); margin-bottom: 0.5rem; font-weight: 600;">Patient Appointments</h3>
      <p style="color: var(--muted); margin-bottom: 1.25rem; font-size: 0.92rem;">Review and manage appointments submitted by patients.</p>
      
      <table>
        <thead>
          <tr>
            <th>Patient Name</th>
            <th>Doctor Name</th>
            <th>Specialization</th>
            <th>Appointment Date</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($appointments_result) > 0) { ?>
            <?php while ($row = mysqli_fetch_assoc($appointments_result)) { ?>
              <tr>
                <td style="font-weight: 600;"><?php echo htmlspecialchars($row['patient_name']); ?></td>
                <td>Dr. <?php echo htmlspecialchars($row['doctor_name']); ?></td>
                <td><span class="pill good" style="font-size: 0.78rem;"><?php echo htmlspecialchars($row['specialization']); ?></span></td>
                <td><?php echo date("d M Y", strtotime($row['appointment_date'])); ?></td>
                <td>
                  <?php
                    $status = $row['status'];
                    $status_class = "status-pending";
                    if ($status == "Confirmed") $status_class = "status-approved";
                    elseif ($status == "Cancelled") $status_class = "status-cancelled";
                  ?>
                  <span class="status-pill <?php echo $status_class; ?>"><?php echo htmlspecialchars($status); ?></span>
                </td>
                <td>
                  <?php if ($row['status'] == "Pending") { ?>
                    <div style="display: flex; gap: 0.4rem;">
                      <a href="admin.php?confirm=<?php echo $row['appointment_id']; ?>" class="btn primary" style="min-height: 2.2rem; padding: 0.25rem 0.85rem; font-size: 0.82rem; border-radius: 6px;">
                        Approve
                      </a>
                      <a href="admin.php?cancel=<?php echo $row['appointment_id']; ?>" class="btn secondary" style="min-height: 2.2rem; padding: 0.25rem 0.85rem; font-size: 0.82rem; border-radius: 6px; color: var(--danger); border-color: rgba(239, 68, 68, 0.2);" onclick="return confirm('Cancel this appointment?')">
                        Cancel
                      </a>
                    </div>
                  <?php } else { ?>
                    <span style="color: var(--muted); font-size: 0.88rem; font-weight: 500;">Closed</span>
                  <?php } ?>
                </td>
              </tr>
            <?php } ?>
          <?php } else { ?>
            <tr>
              <td colspan="6" style="text-align: center; color: var(--muted); padding: 2rem;">No appointments registered.</td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </section>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; align-items: start;">
      <!-- Section 2: Doctors List Redesign -->
      <section class="card glass-card" style="overflow-x: auto;">
        <h3 style="color: var(--accent); margin-bottom: 0.5rem; font-weight: 600;">Clinic Doctors</h3>
        <p style="color: var(--muted); margin-bottom: 1.25rem; font-size: 0.92rem;">Registered medical specialists inside the platform.</p>

        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Specialization</th>
              <th>Experience</th>
              <th>Fee</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (mysqli_num_rows($doctors_result) > 0) { ?>
              <?php while ($doctor = mysqli_fetch_assoc($doctors_result)) { ?>
                <tr>
                  <td style="font-weight: 600;">Dr. <?php echo htmlspecialchars($doctor['full_name']); ?></td>
                  <td><?php echo htmlspecialchars($doctor['specialization']); ?></td>
                  <td><?php echo htmlspecialchars($doctor['experience']); ?> Years</td>
                  <td style="font-weight: 600; color: var(--accent-2);">RM <?php echo number_format($doctor['consultation_fee'], 2); ?></td>
                  <td>
                    <div style="display: flex; gap: 0.4rem;">
                      <a href="edit_doctor.php?doctor_id=<?php echo $doctor['doctor_id']; ?>" class="btn primary" style="min-height: 2rem; padding: 0.2rem 0.65rem; font-size: 0.8rem; border-radius: 6px;">
                        Edit
                      </a>
                      <a href="admin.php?delete_doctor=<?php echo $doctor['doctor_id']; ?>" class="btn secondary" style="min-height: 2rem; padding: 0.2rem 0.65rem; font-size: 0.8rem; border-radius: 6px; color: var(--danger); border-color: rgba(239, 68, 68, 0.2);" onclick="return confirm('Are you sure you want to delete this doctor profile? This will remove their user account as well.')">
                        Delete
                      </a>
                    </div>
                  </td>
                </tr>
              <?php } ?>
            <?php } else { ?>
              <tr>
                <td colspan="5" style="text-align: center; color: var(--muted); padding: 2rem;">No doctors registered.</td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </section>

      <!-- Section 3: Add Doctor Redesign -->
      <section class="card glass-card">
        <h3 style="color: var(--accent-2); margin-bottom: 0.5rem; font-weight: 600;">Add Specialist</h3>
        <p style="color: var(--muted); margin-bottom: 1.25rem; font-size: 0.92rem;">Register a new doctor account.</p>

        <form method="POST" action="admin.php">
          <label class="field">
            Full Name
            <input type="text" name="full_name" placeholder="Dr. John Doe" required />
          </label>

          <label class="field">
            Email Address
            <input type="email" name="email" placeholder="john@omd.com" required />
          </label>

          <label class="field">
            Workspace Password
            <input type="password" name="password" placeholder="Min 6 characters" required />
          </label>

          <label class="field">
            Phone Number
            <input type="text" name="phone" placeholder="012-3456789" required />
          </label>

          <label class="field">
            Clinical Specialization
            <input type="text" name="specialization" placeholder="Avian Surgery" required />
          </label>

          <label class="field">
            Years of Experience
            <input type="number" name="experience" min="0" placeholder="e.g. 5" required />
          </label>

          <label class="field">
            Consultation Fee (RM)
            <input type="number" step="0.01" min="0" name="consultation_fee" placeholder="e.g. 80.00" required />
          </label>

          <button class="btn primary" type="submit" name="add_doctor" style="width: 100%; border-radius: 12px; margin-top: 0.5rem;">
            Add Doctor
          </button>
        </form>
      </section>
    </div>
  </main>

  <footer class="footer" style="margin-top: 4rem;">
    <div class="wrap">
      <p>&copy; <?php echo date('Y'); ?> MedLink Appointment System. All rights reserved.</p>
    </div>
  </footer>
</body>
</html>