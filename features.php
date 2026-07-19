<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Features - MedLink Appointment System</title>
  <link rel="stylesheet" href="styles.css" />
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
        <a class="active" href="features.php">Features</a>
        <a href="doctors.php">Doctors</a>
        <a href="booking.php">Booking</a>
        <?php if (isset($_SESSION['user_id'])) { ?>
          <a href="dashboard.php">Dashboard</a>
        <?php } ?>
      </nav>
    </div>
  </header>

  <main class="wrap page-main">
    <div style="text-align: center; margin-bottom: 2rem;">
      <h1 style="color: var(--accent); margin: 0; font-size: 2.5rem; font-weight: 700;">System Features</h1>
      <p style="color: var(--muted); margin-top: 0.5rem;">Simple tools for booking and managing appointments</p>
    </div>

    <section class="grid-2 page-grid">
      <article class="card glass-card">
        <h3 style="color: var(--accent); margin-bottom: 1rem; font-weight: 600;">Secure Access</h3>
        <p>Patients can register and sign in with hashed passwords. Role-based dashboards keep the interface simple for each user type.</p>
      </article>

      <article class="card glass-card">
        <h3 style="color: var(--accent-2); margin-bottom: 1rem; font-weight: 600;">Live Scheduling</h3>
        <p>Doctors list available time slots, and patients can book them instantly without manual follow-up.</p>
      </article>

      <article class="card glass-card">
        <h3 style="color: var(--accent); margin-bottom: 1rem; font-weight: 600;">Status Monitoring</h3>
        <p>Patients and staff can see bookings move from Pending to Confirmed or Cancelled.</p>
      </article>

      <article class="card glass-card">
        <h3 style="color: var(--accent-2); margin-bottom: 1rem; font-weight: 600;">Complete Workspaces</h3>
        <p>Doctors manage schedules, patients review appointments, and admins maintain doctor profiles from one place.</p>
      </article>
    </section>
  </main>

  <footer class="footer">
    <div class="wrap">
      <p>&copy; <?php echo date('Y'); ?> MedLink Appointment System. All rights reserved.</p>
    </div>
  </footer>
</body>
</html>
