<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$role = $_SESSION['role'] ?? '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';

if (isset($_GET['cancel'])) {
    $appointment_id = (int)$_GET['cancel'];
    $cancel_stmt = $conn->prepare("UPDATE appointments SET status = 'Cancelled' WHERE appointment_id = ? AND patient_id = ? AND status = 'Pending'");
    if ($cancel_stmt) {
        $cancel_stmt->bind_param('ii', $appointment_id, $user_id);
        $cancel_stmt->execute();
        $cancel_stmt->close();
    }
    header('Location: dashboard.php');
    exit();
}

$booking_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM appointments WHERE patient_id = ?");
$total_bookings = 0;
if ($booking_stmt) {
    $booking_stmt->bind_param('i', $user_id);
    $booking_stmt->execute();
    $booking_data = $booking_stmt->get_result()->fetch_assoc();
    $total_bookings = (int)$booking_data['total'];
    $booking_stmt->close();
}

$status_counts = ['Pending' => 0, 'Confirmed' => 0, 'Cancelled' => 0, 'Completed' => 0];
$status_stmt = $conn->prepare("SELECT status, COUNT(*) AS total FROM appointments WHERE patient_id = ? GROUP BY status");
if ($status_stmt) {
    $status_stmt->bind_param('i', $user_id);
    $status_stmt->execute();
    $status_result = $status_stmt->get_result();
    while ($row = $status_result->fetch_assoc()) {
        $status_counts[$row['status']] = (int)$row['total'];
    }
    $status_stmt->close();
}

$doctor_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM doctors");
$doctor_data = $doctor_result ? mysqli_fetch_assoc($doctor_result) : ['total' => 0];
$total_doctors = (int)$doctor_data['total'];

$next_stmt = $conn->prepare("SELECT appointments.appointment_id, users.full_name, doctors.specialization, appointments.appointment_date, appointments.status, schedules.start_time, schedules.end_time FROM appointments INNER JOIN doctors ON appointments.doctor_id = doctors.doctor_id INNER JOIN users ON doctors.user_id = users.user_id INNER JOIN schedules ON appointments.schedule_id = schedules.schedule_id WHERE appointments.patient_id = ? AND appointments.status IN ('Pending', 'Confirmed', 'Approved') ORDER BY appointments.appointment_date ASC, schedules.start_time ASC LIMIT 1");
$next_appointment = null;
if ($next_stmt) {
    $next_stmt->bind_param('i', $user_id);
    $next_stmt->execute();
    $next_result = $next_stmt->get_result();
    if ($next_result->num_rows > 0) {
        $next_appointment = $next_result->fetch_assoc();
    }
    $next_stmt->close();
}

$appointment_sql = "SELECT appointments.appointment_id, users.full_name, doctors.specialization, appointments.appointment_date, appointments.status, schedules.start_time, schedules.end_time FROM appointments INNER JOIN doctors ON appointments.doctor_id = doctors.doctor_id INNER JOIN users ON doctors.user_id = users.user_id INNER JOIN schedules ON appointments.schedule_id = schedules.schedule_id WHERE appointments.patient_id = ?";
if ($status_filter !== '') {
    $appointment_sql .= " AND appointments.status = ?";
}
$appointment_sql .= " ORDER BY appointments.appointment_date DESC, appointments.appointment_id DESC";

$appointment_stmt = $conn->prepare($appointment_sql);
$appointments = null;
if ($appointment_stmt) {
    if ($status_filter !== '') {
        $appointment_stmt->bind_param('is', $user_id, $status_filter);
    } else {
        $appointment_stmt->bind_param('i', $user_id);
    }
    $appointment_stmt->execute();
    $appointments = $appointment_stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard - MedLink Appointment System</title>
  <link rel="stylesheet" href="styles.css" />
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
        <a href="about.php">About</a>
        <a href="features.php">Features</a>
        <a href="doctors.php">Doctors</a>
        <?php if ($role === 'patient') { ?>
          <a href="booking.php">Booking</a>
        <?php } ?>
        <a class="active" href="dashboard.php">Dashboard</a>
        <?php if ($role === 'admin') { ?>
          <a href="admin.php">Admin</a>
        <?php } ?>
        <a href="logout.php" style="color: var(--danger);">Logout</a>
      </nav>
    </div>
  </header>

  <main class="wrap page-main">
    <section class="card glass-card" style="margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; gap: 1.5rem; flex-wrap: wrap;">
      <div>
        <h2 style="margin: 0; color: var(--accent); font-weight: 700;">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>
        <p style="margin: 0.25rem 0 0; color: var(--muted); font-size: 0.95rem;">You are logged in as a <strong><?php echo ucfirst($role); ?></strong>.</p>
      </div>
      <?php if ($role === 'patient') { ?>
        <div>
          <a href="booking.php" class="btn primary" style="border-radius: 12px; min-height: 2.8rem; font-size: 0.9rem;">Book New Appointment</a>
        </div>
      <?php } ?>
    </section>

    <section class="grid-2 page-grid">
      <article class="card glass-card">
        <h3 style="color: var(--accent); margin-bottom: 1rem; font-weight: 600;">Workspace Overview</h3>
        <p>Review your bookings, find doctors, and keep track of appointment status in one place.</p>

        <div class="stats" style="margin-top: 1.5rem;">
          <div class="stat">
            <strong><?php echo $total_bookings; ?></strong>
            <span class="muted">My Bookings</span>
          </div>
          <div class="stat">
            <strong><?php echo $total_doctors; ?></strong>
            <span class="muted">Clinic Doctors</span>
          </div>
          <div class="stat">
            <strong style="font-size: 1rem; text-transform: uppercase; font-family: inherit; margin-top: 0.75rem; display: block; color: var(--accent-2);"><?php echo htmlspecialchars($role); ?></strong>
            <span class="muted">User Role</span>
          </div>
        </div>
      </article>

      <article class="card glass-card">
        <h3 style="color: var(--accent-2); margin-bottom: 1rem; font-weight: 600;">Booking Snapshot</h3>
        <div class="stats" style="margin-top: 1rem;">
          <div class="stat">
            <strong><?php echo $status_counts['Pending']; ?></strong>
            <span class="muted">Pending</span>
          </div>
          <div class="stat">
            <strong><?php echo $status_counts['Confirmed']; ?></strong>
            <span class="muted">Confirmed</span>
          </div>
          <div class="stat">
            <strong><?php echo $status_counts['Cancelled']; ?></strong>
            <span class="muted">Cancelled</span>
          </div>
        </div>
        <?php if ($next_appointment) { ?>
          <div style="margin-top: 1.25rem; padding: 1rem; border-radius: 14px; background: var(--panel-2); border: 1px solid var(--line);">
            <p style="margin: 0 0 0.35rem; color: var(--muted); font-size: 0.88rem;">Next appointment</p>
            <strong style="display: block; margin-bottom: 0.35rem; color: var(--text);">Dr. <?php echo htmlspecialchars($next_appointment['full_name']); ?></strong>
            <div style="font-size: 0.9rem; color: #475569; display: grid; gap: 0.2rem;">
              <span><?php echo htmlspecialchars($next_appointment['specialization']); ?></span>
              <span><?php echo date('d M Y', strtotime($next_appointment['appointment_date'])); ?> | <?php echo date('h:i A', strtotime($next_appointment['start_time'])) . ' - ' . date('h:i A', strtotime($next_appointment['end_time'])); ?></span>
              <span>Status: <?php echo htmlspecialchars($next_appointment['status']); ?></span>
            </div>
          </div>
        <?php } ?>
      </article>
    </section>

    <section class="card glass-card" style="margin-top: 2rem;">
      <h3 style="color: var(--accent); margin-bottom: 1.5rem; font-weight: 600;">My Booking History</h3>

      <form method="GET" action="dashboard.php" style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1.25rem;">
        <select name="status" style="min-width: 220px; border-radius: 999px;">
          <option value="">All Status</option>
          <option value="Pending" <?php if ($status_filter === 'Pending') echo 'selected'; ?>>Pending</option>
          <option value="Confirmed" <?php if ($status_filter === 'Confirmed') echo 'selected'; ?>>Confirmed</option>
          <option value="Completed" <?php if ($status_filter === 'Completed') echo 'selected'; ?>>Completed</option>
          <option value="Cancelled" <?php if ($status_filter === 'Cancelled') echo 'selected'; ?>>Cancelled</option>
        </select>
        <button class="btn primary" type="submit" style="min-height: 2.8rem;">Filter</button>
        <?php if ($status_filter !== '') { ?>
          <a href="dashboard.php" class="btn secondary" style="min-height: 2.8rem; display: inline-flex; align-items: center; justify-content: center;">Reset</a>
        <?php } ?>
      </form>

      <?php if ($appointments && $appointments->num_rows > 0) { ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.25rem;">
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
                <h4 style="margin: 0; font-size: 1.1rem; font-weight: 600; color: var(--text);">Dr. <?php echo htmlspecialchars($row['full_name']); ?></h4>
                <span class="status-pill <?php echo $status_class; ?>"><?php echo htmlspecialchars($status); ?></span>
              </div>

              <div style="font-size: 0.9rem; color: #475569; display: grid; gap: 0.25rem; margin: 0.5rem 0;">
                <div>Specialization: <strong><?php echo htmlspecialchars($row['specialization']); ?></strong></div>
                <div>Appt Date: <strong><?php echo date('d M Y', strtotime($row['appointment_date'])); ?></strong></div>
                <div>Time: <strong><?php echo date('h:i A', strtotime($row['start_time'])) . ' - ' . date('h:i A', strtotime($row['end_time'])); ?></strong></div>
              </div>

              <div style="margin-top: 0.5rem; display: grid; gap: 0.5rem;">
                <?php if ($status === 'Pending') { ?>
                  <a href="booking.php?appointment_id=<?php echo (int)$row['appointment_id']; ?>" class="btn primary" style="width: 100%; border-radius: 8px; min-height: 2.5rem; font-size: 0.85rem;">Reschedule</a>
                  <a href="dashboard.php?cancel=<?php echo (int)$row['appointment_id']; ?>" class="btn secondary" style="width: 100%; border-radius: 8px; min-height: 2.5rem; font-size: 0.85rem; border-color: var(--danger); color: var(--danger);" onclick="return confirm('Are you sure you want to cancel this appointment?')">Cancel Appointment</a>
                <?php } else { ?>
                  <span class="btn secondary" style="width: 100%; border-radius: 8px; min-height: 2.5rem; font-size: 0.85rem; opacity: 0.55; cursor: not-allowed; background: var(--panel-2);">Locked</span>
                <?php } ?>
              </div>
            </div>
          <?php } ?>
        </div>
      <?php } else { ?>
        <div style="text-align: center; padding: 2rem 0; color: var(--muted);">
          <span style="font-size: 2.5rem; display: block; margin-bottom: 0.5rem;">Booking</span>
          <p style="margin: 0; font-size: 0.95rem;">You haven't booked any appointments yet.</p>
          <a href="booking.php" class="btn primary" style="margin-top: 1rem; border-radius: 12px; min-height: 2.8rem; font-size: 0.88rem;">Book Your First Appointment</a>
        </div>
      <?php } ?>
    </section>
  </main>

  <footer class="footer">
    <div class="wrap">
      <p>&copy; <?php echo date('Y'); ?> MedLink Appointment System. All rights reserved.</p>
    </div>
  </footer>

  <?php
  if (isset($appointment_stmt)) {
      $appointment_stmt->close();
  }
  ?>
</body>
</html>
