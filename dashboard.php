<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Cancel appointment securely with prepared statement
if (isset($_GET['cancel'])) {
    $appointment_id = $_GET['cancel'];

    $cancel_stmt = $conn->prepare("
        UPDATE appointments 
        SET status = 'Cancelled' 
        WHERE appointment_id = ? 
          AND patient_id = ? 
          AND status = 'Pending'
    ");
    if ($cancel_stmt) {
        $cancel_stmt->bind_param("ii", $appointment_id, $user_id);
        $cancel_stmt->execute();
        $cancel_stmt->close();
    }

    header("Location: dashboard.php");
    exit();
}

// Count user's appointments securely
$booking_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM appointments WHERE patient_id = ?");
$total_bookings = 0;
if ($booking_stmt) {
    $booking_stmt->bind_param("i", $user_id);
    $booking_stmt->execute();
    $booking_result = $booking_stmt->get_result();
    $booking_data = $booking_result->fetch_assoc();
    $total_bookings = $booking_data['total'];
    $booking_stmt->close();
}

// Count registered doctors
$doctor_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM doctors");
$doctor_data = mysqli_fetch_assoc($doctor_result);
$total_doctors = $doctor_data['total'];

// Get user's appointments securely
$appointment_stmt = $conn->prepare("
    SELECT appointments.appointment_id, users.full_name, doctors.specialization, appointments.appointment_date, appointments.status 
    FROM appointments 
    INNER JOIN doctors ON appointments.doctor_id = doctors.doctor_id 
    INNER JOIN users ON doctors.user_id = users.user_id 
    WHERE appointments.patient_id = ? 
    ORDER BY appointments.appointment_date DESC, appointments.appointment_id DESC
");
$appointments = null;
if ($appointment_stmt) {
    $appointment_stmt->bind_param("i", $user_id);
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
        <span class="brand-mark">BS</span>
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
        <?php if ($_SESSION['role'] == "patient") { ?>
          <a href="booking.php">Booking</a>
        <?php } ?>
        <a class="active" href="dashboard.php">Dashboard</a>
        <?php if ($_SESSION['role'] == "admin") { ?>
          <a href="admin.php">Admin</a>
        <?php } ?>
        <a href="logout.php" style="color: var(--danger);">Logout</a>
      </nav>
    </div>
  </header>

  <main class="wrap page-main">
    <!-- Welcome Card Redesign -->
    <section class="card glass-card" style="margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; gap: 1.5rem; flex-wrap: wrap;">
      <div>
        <h2 style="margin: 0; color: var(--accent); font-weight: 700;">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>
        <p style="margin: 0.25rem 0 0; color: var(--muted); font-size: 0.95rem;">You are logged in as a <strong><?php echo ucfirst($_SESSION['role']); ?></strong>.</p>
      </div>
      <div>
        <a href="booking.php" class="btn primary" style="border-radius: 12px; min-height: 2.8rem; font-size: 0.9rem;">Book New Appointment</a>
      </div>
    </section>

    <section class="grid-2 page-grid">
      <!-- Overview & Action Block -->
      <article class="card glass-card">
        <h3 style="color: var(--accent); margin-bottom: 1rem; font-weight: 600;">Workspace Overview</h3>
        <p>Review active booking analytics, find clinic doctors, and update schedules.</p>

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
            <strong style="font-size: 1rem; text-transform: uppercase; font-family: inherit; margin-top: 0.75rem; display: block; color: var(--accent-2);"><?php echo htmlspecialchars($_SESSION['role']); ?></strong>
            <span class="muted">User Role</span>
          </div>
        </div>
      </article>

      <!-- Roles Guidelines -->
      <article class="card glass-card">
        <h3 style="color: var(--accent-2); margin-bottom: 1rem; font-weight: 600;">System Actions</h3>
        <ul class="list" style="display: grid; gap: 0.75rem;">
          <li><strong>Patient Workflow:</strong> Search specialists, choose slots, submit booking requests, and cancel pending sessions.</li>
          <li><strong>Doctor Workflow:</strong> Manage availability schedules, review patient booking histories, and approve/cancel appointments.</li>
          <li><strong>Admin Workflow:</strong> Add/edit specialist profiles, monitor clinical appointments, and manage patient accounts.</li>
        </ul>
      </article>
    </section>

    <!-- My Appointments Card Grid -->
    <section class="card glass-card" style="margin-top: 2rem;">
      <h3 style="color: var(--accent); margin-bottom: 1.5rem; font-weight: 600;">My Booking History</h3>

      <?php if ($appointments && $appointments->num_rows > 0) { ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.25rem;">
          <?php while ($row = $appointments->fetch_assoc()) { ?>
            <div class="card" style="background: var(--bg); border: 1px solid var(--line); display: grid; gap: 0.5rem; padding: 1.25rem; border-radius: 12px; box-shadow: none;">
              <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem;">
                <h4 style="margin: 0; font-size: 1.1rem; font-weight: 600; color: var(--text);">Dr. <?php echo htmlspecialchars($row['full_name']); ?></h4>
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

              <div style="font-size: 0.9rem; color: #475569; display: grid; gap: 0.25rem; margin: 0.5rem 0;">
                <div>Specialization: <strong><?php echo htmlspecialchars($row['specialization']); ?></strong></div>
                <div>Appt Date: <strong><?php echo date("d M Y", strtotime($row['appointment_date'])); ?></strong></div>
              </div>

              <div style="margin-top: 0.5rem;">
                <?php if ($status == "Pending") { ?>
                  <a
                    href="dashboard.php?cancel=<?php echo $row['appointment_id']; ?>"
                    class="btn secondary"
                    style="width: 100%; border-radius: 8px; min-height: 2.5rem; font-size: 0.85rem; border-color: var(--danger); color: var(--danger);"
                    onclick="return confirm('Are you sure you want to cancel this appointment?')"
                  >
                    Cancel Appointment
                  </a>
                <?php } else { ?>
                  <span
                    class="btn secondary"
                    style="width: 100%; border-radius: 8px; min-height: 2.5rem; font-size: 0.85rem; opacity: 0.55; cursor: not-allowed; background: var(--panel-2);"
                  >
                    Locked
                  </span>
                <?php } ?>
              </div>
            </div>
          <?php } ?>
        </div>
      <?php } else { ?>
        <div style="text-align: center; padding: 2rem 0; color: var(--muted);">
          <span style="font-size: 2.5rem; display: block; margin-bottom: 0.5rem;">📅</span>
          <p style="margin: 0; font-size: 0.95rem;">You haven't booked any appointments yet.</p>
          <a href="booking.php" class="btn primary" style="margin-top: 1rem; border-radius: 12px; min-height: 2.8rem; font-size: 0.88rem;">Book Your First Appointment</a>
        </div>
      <?php } ?>
    </section>
  </main>

  <footer class="footer">
    <div class="wrap">
      <p>&copy; <?php echo date('Y'); ?>  Appointment System. All rights reserved.</p>
    </div>
  </footer>
</body>
</html>
<?php
if (isset($appointment_stmt)) {
    $appointment_stmt->close();
}
?>