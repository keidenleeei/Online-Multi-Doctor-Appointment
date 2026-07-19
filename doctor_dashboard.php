<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (($_SESSION['role'] ?? '') !== 'doctor') {
    die('Access Denied.');
}

$doctor_user_id = (int)$_SESSION['user_id'];
$doctor_id = 0;
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';

$doc_stmt = $conn->prepare("SELECT doctor_id FROM doctors WHERE user_id = ?");
if ($doc_stmt) {
    $doc_stmt->bind_param('i', $doctor_user_id);
    $doc_stmt->execute();
    $doc_result = $doc_stmt->get_result();
    if ($doc_result->num_rows > 0) {
        $doctor_assoc = $doc_result->fetch_assoc();
        $doctor_id = (int)$doctor_assoc['doctor_id'];
    } else {
        die('Doctor profile not found in system.');
    }
    $doc_stmt->close();
}

if (isset($_GET['confirm'])) {
    $appointment_id = (int)$_GET['confirm'];
    $confirm_stmt = $conn->prepare("UPDATE appointments SET status = 'Confirmed' WHERE appointment_id = ? AND doctor_id = ? AND status = 'Pending'");
    if ($confirm_stmt) {
        $confirm_stmt->bind_param('ii', $appointment_id, $doctor_id);
        $confirm_stmt->execute();
        $confirm_stmt->close();
    }
    header('Location: doctor_dashboard.php');
    exit();
}

if (isset($_GET['complete'])) {
    $appointment_id = (int)$_GET['complete'];
    $complete_stmt = $conn->prepare("UPDATE appointments SET status = 'Completed' WHERE appointment_id = ? AND doctor_id = ? AND status IN ('Confirmed', 'Approved')");
    if ($complete_stmt) {
        $complete_stmt->bind_param('ii', $appointment_id, $doctor_id);
        $complete_stmt->execute();
        $complete_stmt->close();
    }
    header('Location: doctor_dashboard.php');
    exit();
}

if (isset($_GET['cancel'])) {
    $appointment_id = (int)$_GET['cancel'];
    $cancel_stmt = $conn->prepare("UPDATE appointments SET status = 'Cancelled' WHERE appointment_id = ? AND doctor_id = ? AND status = 'Pending'");
    if ($cancel_stmt) {
        $cancel_stmt->bind_param('ii', $appointment_id, $doctor_id);
        $cancel_stmt->execute();
        $cancel_stmt->close();
    }
    header('Location: doctor_dashboard.php');
    exit();
}

$status_counts = ['Pending' => 0, 'Confirmed' => 0, 'Cancelled' => 0, 'Completed' => 0];
$count_stmt = $conn->prepare("SELECT status, COUNT(*) AS total FROM appointments WHERE doctor_id = ? GROUP BY status");
if ($count_stmt) {
    $count_stmt->bind_param('i', $doctor_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    while ($row = $count_result->fetch_assoc()) {
        $status_counts[$row['status']] = (int)$row['total'];
    }
    $count_stmt->close();
}

$upcoming_stmt = $conn->prepare("SELECT appointments.appointment_id, appointments.appointment_date, appointments.status, patient.full_name AS patient_name, schedules.start_time, schedules.end_time FROM appointments INNER JOIN users AS patient ON appointments.patient_id = patient.user_id INNER JOIN schedules ON appointments.schedule_id = schedules.schedule_id WHERE appointments.doctor_id = ? AND appointments.status IN ('Pending', 'Confirmed', 'Approved') ORDER BY appointments.appointment_date ASC, schedules.start_time ASC LIMIT 1");
$upcoming_appointment = null;
if ($upcoming_stmt) {
    $upcoming_stmt->bind_param('i', $doctor_id);
    $upcoming_stmt->execute();
    $upcoming_result = $upcoming_stmt->get_result();
    if ($upcoming_result->num_rows > 0) {
        $upcoming_appointment = $upcoming_result->fetch_assoc();
    }
    $upcoming_stmt->close();
}

$app_sql = "SELECT appointments.appointment_id, appointments.appointment_date, appointments.status, patient.full_name AS patient_name, schedules.start_time, schedules.end_time FROM appointments INNER JOIN users AS patient ON appointments.patient_id = patient.user_id INNER JOIN schedules ON appointments.schedule_id = schedules.schedule_id WHERE appointments.doctor_id = ?";
if ($status_filter !== '') {
    $app_sql .= " AND appointments.status = ?";
}
$app_sql .= " ORDER BY appointments.appointment_date DESC, schedules.start_time ASC";

$app_stmt = $conn->prepare($app_sql);
$appointments = null;
if ($app_stmt) {
    if ($status_filter !== '') {
        $app_stmt->bind_param('is', $doctor_id, $status_filter);
    } else {
        $app_stmt->bind_param('i', $doctor_id);
    }
    $app_stmt->execute();
    $appointments = $app_stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Doctor Dashboard - MedLink Appointment System</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body class="page-bg">
  <div class="page-glow page-glow-a"></div>
  <div class="page-glow page-glow-b"></div>

  <header class="site-header content-header">
    <div class="wrap header-inner">
      <a class="brand" href="index.php">
        <span class="brand-mark">ML</span>
        <span>MedLink<small>Appointment System</small></span>
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
    <section class="card glass-card" style="margin-bottom: 2rem;">
      <h2 style="margin: 0; color: var(--accent); font-weight: 700;">Welcome, Dr. <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>
      <p style="margin: 0.25rem 0 0; color: var(--muted); font-size: 0.95rem;">Workspace to monitor appointments and confirm bookings.</p>
    </section>

    <section class="grid-2 page-grid">
      <article class="card glass-card">
        <h3 style="color: var(--accent); margin-bottom: 1rem; font-weight: 600;">Appointment Snapshot</h3>
        <div class="stats" style="margin-top: 1rem;">
          <div class="stat"><strong><?php echo $status_counts['Pending']; ?></strong><span class="muted">Pending</span></div>
          <div class="stat"><strong><?php echo $status_counts['Confirmed']; ?></strong><span class="muted">Confirmed</span></div>
          <div class="stat"><strong><?php echo $status_counts['Cancelled']; ?></strong><span class="muted">Cancelled</span></div>
          <div class="stat"><strong><?php echo $status_counts['Completed']; ?></strong><span class="muted">Completed</span></div>
        </div>
      </article>

      <article class="card glass-card">
        <h3 style="color: var(--accent-2); margin-bottom: 1rem; font-weight: 600;">Next Appointment</h3>
        <?php if ($upcoming_appointment) { ?>
          <p style="margin: 0 0 0.35rem; color: var(--muted); font-size: 0.88rem;">Upcoming patient visit</p>
          <strong style="display: block; margin-bottom: 0.35rem; color: var(--text);">Patient: <?php echo htmlspecialchars($upcoming_appointment['patient_name']); ?></strong>
          <div style="font-size: 0.9rem; color: #475569; display: grid; gap: 0.2rem;">
            <span><?php echo date('d M Y', strtotime($upcoming_appointment['appointment_date'])); ?></span>
            <span><?php echo date('h:i A', strtotime($upcoming_appointment['start_time'])) . ' - ' . date('h:i A', strtotime($upcoming_appointment['end_time'])); ?></span>
            <span>Status: <?php echo htmlspecialchars($upcoming_appointment['status']); ?></span>
          </div>
        <?php } else { ?>
          <p style="margin: 0; color: var(--muted);">No upcoming appointments yet.</p>
        <?php } ?>
      </article>
    </section>

    <section class="card glass-card" style="margin-top: 2rem;">
      <h3 style="color: var(--accent); margin-bottom: 1.5rem; font-weight: 600;">My Appointment Requests</h3>

      <form method="GET" action="doctor_dashboard.php" style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1.25rem;">
        <select name="status" style="min-width: 220px; border-radius: 999px;">
          <option value="">All Status</option>
          <option value="Pending" <?php if ($status_filter === 'Pending') echo 'selected'; ?>>Pending</option>
          <option value="Confirmed" <?php if ($status_filter === 'Confirmed') echo 'selected'; ?>>Confirmed</option>
          <option value="Completed" <?php if ($status_filter === 'Completed') echo 'selected'; ?>>Completed</option>
          <option value="Cancelled" <?php if ($status_filter === 'Cancelled') echo 'selected'; ?>>Cancelled</option>
        </select>
        <button class="btn primary" type="submit" style="min-height: 2.8rem;">Filter</button>
        <?php if ($status_filter !== '') { ?>
          <a href="doctor_dashboard.php" class="btn secondary" style="min-height: 2.8rem; display: inline-flex; align-items: center; justify-content: center;">Reset</a>
        <?php } ?>
      </form>

      <?php if ($appointments && $appointments->num_rows > 0) { ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 1.25rem;">
          <?php while ($row = $appointments->fetch_assoc()) { ?>
            <?php
              $status = $row['status'];
              $status_class = 'status-pending';
              if ($status === 'Confirmed' || $status === 'Approved') {
                  $status_class = 'status-approved';
              } elseif ($status === 'Completed') {
                  $status_class = 'status-completed';
              } elseif ($status === 'Cancelled') {
                  $status_class = 'status-cancelled';
              }
            ?>
            <div class="card" style="background: var(--bg); border: 1px solid var(--line); display: grid; gap: 0.5rem; padding: 1.25rem; border-radius: 12px; box-shadow: none;">
              <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem;">
                <h4 style="margin: 0; font-size: 1.1rem; font-weight: 600; color: var(--text);">Patient: <?php echo htmlspecialchars($row['patient_name']); ?></h4>
                <span class="status-pill <?php echo $status_class; ?>"><?php echo htmlspecialchars($status); ?></span>
              </div>

              <div style="font-size: 0.9rem; color: #475569; display: grid; gap: 0.25rem; margin: 0.5rem 0; padding-bottom: 0.5rem; border-bottom: 1px solid var(--line);">
                <div>Date: <strong><?php echo date('d M Y', strtotime($row['appointment_date'])); ?></strong></div>
                <div>Time: <strong><?php echo date('h:i A', strtotime($row['start_time'])) . ' - ' . date('h:i A', strtotime($row['end_time'])); ?></strong></div>
              </div>

              <div>
                <?php if ($row['status'] === 'Pending') { ?>
                  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                    <a href="doctor_dashboard.php?confirm=<?php echo (int)$row['appointment_id']; ?>" class="btn primary" style="border-radius: 8px; min-height: 2.4rem; padding: 0.25rem 0.75rem; font-size: 0.85rem;">Confirm</a>
                    <a href="doctor_dashboard.php?cancel=<?php echo (int)$row['appointment_id']; ?>" class="btn secondary" style="border-radius: 8px; min-height: 2.4rem; padding: 0.25rem 0.75rem; font-size: 0.85rem; color: var(--danger); border-color: rgba(239, 68, 68, 0.2);" onclick="return confirm('Cancel this appointment?')">Cancel</a>
                  </div>
                <?php } elseif ($row['status'] === 'Confirmed' || $row['status'] === 'Approved') { ?>
                  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                    <a href="doctor_dashboard.php?complete=<?php echo (int)$row['appointment_id']; ?>" class="btn primary" style="border-radius: 8px; min-height: 2.4rem; padding: 0.25rem 0.75rem; font-size: 0.85rem;">Complete</a>
                    <span class="btn secondary" style="border-radius: 8px; min-height: 2.4rem; padding: 0.25rem 0.75rem; font-size: 0.85rem; opacity: 0.55; cursor: not-allowed; background: var(--panel-2);">Locked</span>
                  </div>
                <?php } else { ?>
                  <span class="btn secondary" style="width: 100%; border-radius: 8px; min-height: 2.4rem; font-size: 0.85rem; opacity: 0.55; cursor: not-allowed; background: var(--panel-2);">Action Locked</span>
                <?php } ?>
              </div>
            </div>
          <?php } ?>
        </div>
      <?php } else { ?>
        <div style="text-align: center; padding: 3rem 0; color: var(--muted);">
          <span style="font-size: 2.5rem; display: block; margin-bottom: 0.5rem;">Calendar</span>
          <p style="margin: 0; font-size: 0.95rem;">No appointments found on your calendar.</p>
          <a href="doctor_schedule.php" class="btn primary" style="margin-top: 1rem; border-radius: 12px; min-height: 2.8rem; font-size: 0.88rem;">Manage Time Schedules</a>
        </div>
      <?php } ?>
    </section>
  </main>

  <footer class="footer" style="margin-top: 5rem;">
    <div class="wrap">
      <p>&copy; <?php echo date('Y'); ?> MedLink Appointment System. All rights reserved.</p>
    </div>
  </footer>

  <?php
  if (isset($app_stmt)) {
      $app_stmt->close();
  }
  ?>
</body>
</html>
