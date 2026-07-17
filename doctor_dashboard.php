<?php
session_start();
include "config.php";

// 1. Move login/role verification to the very top to prevent warnings
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] != "doctor") {
    die("Access Denied.");
}

$doctor_user_id = $_SESSION['user_id'];
$doctor_id = "";

// Get doctor_id securely
$doc_stmt = $conn->prepare("SELECT doctor_id FROM doctors WHERE user_id = ?");
if ($doc_stmt) {
    $doc_stmt->bind_param("i", $doctor_user_id);
    $doc_stmt->execute();
    $doc_result = $doc_stmt->get_result();
    if ($doc_result->num_rows > 0) {
        $doctor_assoc = $doc_result->fetch_assoc();
        $doctor_id = $doctor_assoc['doctor_id'];
    } else {
        die("Doctor profile not found in system.");
    }
    $doc_stmt->close();
}

// 2. Secure Confirm Appointment Handler
if (isset($_GET['confirm'])) {
    $appointment_id = $_GET['confirm'];

    // Ensure appointment belongs to this doctor
    $confirm_stmt = $conn->prepare("UPDATE appointments SET status = 'Confirmed' WHERE appointment_id = ? AND doctor_id = ?");
    if ($confirm_stmt) {
        $confirm_stmt->bind_param("ii", $appointment_id, $doctor_id);
        $confirm_stmt->execute();
        $confirm_stmt->close();
    }

    header("Location: doctor_dashboard.php");
    exit();
}

// 3. Secure Cancel Appointment Handler
if (isset($_GET['cancel'])) {
    $appointment_id = $_GET['cancel'];

    // Ensure appointment belongs to this doctor
    $cancel_stmt = $conn->prepare("UPDATE appointments SET status = 'Cancelled' WHERE appointment_id = ? AND doctor_id = ?");
    if ($cancel_stmt) {
        $cancel_stmt->bind_param("ii", $appointment_id, $doctor_id);
        $cancel_stmt->execute();
        $cancel_stmt->close();
    }

    header("Location: doctor_dashboard.php");
    exit();
}

// Fetch doctor's appointments securely using prepared statement
$appointments = null;
$app_stmt = $conn->prepare("
    SELECT appointments.appointment_id, appointments.appointment_date, appointments.status, 
           patient.full_name AS patient_name, schedules.start_time, schedules.end_time 
    FROM appointments 
    INNER JOIN users AS patient ON appointments.patient_id = patient.user_id 
    INNER JOIN schedules ON appointments.schedule_id = schedules.schedule_id 
    WHERE appointments.doctor_id = ? 
    ORDER BY appointments.appointment_date DESC, schedules.start_time ASC
");

if ($app_stmt) {
    $app_stmt->bind_param("i", $doctor_id);
    $app_stmt->execute();
    $appointments = $app_stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Doctor Dashboard - Bird Safety Appointment System</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body class="page-bg">
  <div class="page-glow page-glow-a"></div>
  <div class="page-glow page-glow-b"></div>

  <header class="site-header content-header">
    <div class="wrap header-inner">
      <a class="brand" href="index.php">
        <span class="brand-mark">BS</span>
        <span>
          Bird Safety
          <small>Appointment System</small>
        </span>
      </a>

      <nav class="nav">
        <a href="index.php">Home</a>
        <a href="doctor_dashboard.php" class="active">Dashboard</a>
        <a href="doctor_schedule.php">Manage Schedule</a>
        <a href="doctors.php">Doctors</a>
        <a href="logout.php" style="color: var(--danger);">Logout</a>
      </nav>
    </div>
  </header>

  <main class="wrap page-main">
    <!-- Welcome Card Redesign -->
    <section class="card glass-card" style="margin-bottom: 2rem;">
      <h2 style="margin: 0; color: var(--accent); font-weight: 700;">Welcome, Dr. <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>
      <p style="margin: 0.25rem 0 0; color: var(--muted); font-size: 0.95rem;">Workspace to monitor bird appointments and confirm bookings.</p>
    </section>

    <!-- Appointment Grid Redesign -->
    <section class="card glass-card">
      <h3 style="color: var(--accent); margin-bottom: 1.5rem; font-weight: 600;">My Appointment Requests</h3>

      <?php if ($appointments && $appointments->num_rows > 0) { ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 1.25rem;">
          <?php while ($row = $appointments->fetch_assoc()) { ?>
            <div class="card" style="background: var(--bg); border: 1px solid var(--line); display: grid; gap: 0.5rem; padding: 1.25rem; border-radius: 12px; box-shadow: none;">
              
              <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem;">
                <h4 style="margin: 0; font-size: 1.1rem; font-weight: 600; color: var(--text);">
                  👤 <?php echo htmlspecialchars($row['patient_name']); ?>
                </h4>
                <?php
                  $status = $row['status'];
                  $status_class = "status-pending";
                  if ($status == "Confirmed" || $status == "Approved") {
                      $status_class = "status-approved";
                  } elseif ($status == "Cancelled") {
                      $status_class = "status-cancelled";
                  }
                ?>
                <span class="status-pill <?php echo $status_class; ?>"><?php echo htmlspecialchars($status); ?></span>
              </div>

              <div style="font-size: 0.9rem; color: #475569; display: grid; gap: 0.25rem; margin: 0.5rem 0; padding-bottom: 0.5rem; border-bottom: 1px solid var(--line);">
                <div>Date: <strong><?php echo date("d M Y", strtotime($row['appointment_date'])); ?></strong></div>
                <div>Time: <strong><?php echo date("h:i A", strtotime($row['start_time'])) . ' - ' . date("h:i A", strtotime($row['end_time'])); ?></strong></div>
              </div>

              <div>
                <?php if ($row['status'] == "Pending") { ?>
                  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                    <a
                      href="doctor_dashboard.php?confirm=<?php echo $row['appointment_id']; ?>"
                      class="btn primary"
                      style="border-radius: 8px; min-height: 2.4rem; padding: 0.25rem 0.75rem; font-size: 0.85rem;"
                    >
                      Confirm
                    </a>
                    <a
                      href="doctor_dashboard.php?cancel=<?php echo $row['appointment_id']; ?>"
                      class="btn secondary"
                      style="border-radius: 8px; min-height: 2.4rem; padding: 0.25rem 0.75rem; font-size: 0.85rem; color: var(--danger); border-color: rgba(239, 68, 68, 0.2);"
                      onclick="return confirm('Cancel this appointment?')"
                    >
                      Cancel
                    </a>
                  </div>
                <?php } else { ?>
                  <span
                    class="btn secondary"
                    style="width: 100%; border-radius: 8px; min-height: 2.4rem; font-size: 0.85rem; opacity: 0.55; cursor: not-allowed; background: var(--panel-2);"
                  >
                    Action Locked
                  </span>
                <?php } ?>
              </div>

            </div>
          <?php } ?>
        </div>
      <?php } else { ?>
        <div style="text-align: center; padding: 3rem 0; color: var(--muted);">
          <span style="font-size: 2.5rem; display: block; margin-bottom: 0.5rem;">📭</span>
          <p style="margin: 0; font-size: 0.95rem;">No appointments found on your calendar.</p>
          <a href="doctor_schedule.php" class="btn primary" style="margin-top: 1rem; border-radius: 12px; min-height: 2.8rem; font-size: 0.88rem;">Manage Time Schedules</a>
        </div>
      <?php } ?>
    </section>
  </main>

  <footer class="footer" style="margin-top: 5rem;">
    <div class="wrap">
      <p>&copy; <?php echo date('Y'); ?> Bird Safety Appointment System. All rights reserved.</p>
    </div>
  </footer>
</body>
</html>
<?php
if (isset($app_stmt)) {
    $app_stmt->close();
}
?>