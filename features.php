<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Features - Bird Safety Appointment System</title>
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
          Bird Safety
          <small>Appointment System</small>
        </span>
      </a>
      <nav class="nav">
        <a href="index.php">Home</a>
        <a href="about.php">About</a>
        <a class="active" href="features.php">Features</a>
        <a href="doctors.php">Doctors</a>
        <a href="booking.php">Booking</a>
        <?php if(isset($_SESSION['user_id'])){ ?>
          <a href="dashboard.php">Dashboard</a>
        <?php } ?>
      </nav>
    </div>
  </header>

  <main class="wrap page-main">
    <div style="text-align: center; margin-bottom: 2rem;">
      <h1 style="color: var(--accent); margin: 0; font-size: 2.5rem; font-weight: 700;">System Features</h1>
      <p style="color: var(--muted); margin-top: 0.5rem;">Powerful digital capabilities built for clinical efficiency</p>
    </div>

    <section class="grid-2 page-grid">
      <article class="card glass-card">
        <h3 style="color: var(--accent); margin-bottom: 1rem; font-weight: 600;">🔐 Secure Access</h3>
        <p>Owners register pet/patient accounts and sign in using secure hashed credentials. System roles (Patient, Doctor, Admin) enforce access privileges and dashboard visibility, keeping clinical data safe.</p>
      </article>

      <article class="card glass-card">
        <h3 style="color: var(--accent-2); margin-bottom: 1rem; font-weight: 600;">📅 Live Scheduling</h3>
        <p>Doctors list their available time slots, and patients book them instantly. When a booking is submitted, the slot is locked and saved, eliminating double-bookings and scheduling conflicts.</p>
      </article>

      <article class="card glass-card">
        <h3 style="color: var(--accent); margin-bottom: 1rem; font-weight: 600;">📋 Status Monitoring</h3>
        <p>Both patients and clinic staff get transparent real-time updates. Check the progress of booking statuses as they shift from Pending to Confirmed/Approved, Completed, or Cancelled.</p>
      </article>

      <article class="card glass-card">
        <h3 style="color: var(--accent-2); margin-bottom: 1rem; font-weight: 600;">💻 Complete Workspaces</h3>
        <p>Designed workflows for doctors to adjust calendars and view histories, patients to review personal visits, and administrators to coordinate doctor lists, fee structures, and registrations.</p>
      </article>
    </section>
  </main>

  <footer class="footer">
    <div class="wrap">
      <p>&copy; <?php echo date('Y'); ?> Bird Safety Appointment System. All rights reserved.</p>
    </div>
  </footer>
</body>
</html>
