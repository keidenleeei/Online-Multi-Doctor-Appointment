<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>About - MedLink Appointment System</title>
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
        <a class="active" href="about.php">About</a>
        <a href="features.php">Features</a>
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
      <h1 style="color: var(--accent); margin: 0; font-size: 2.5rem; font-weight: 700;">About Our System</h1>
      <p style="color: var(--muted); margin-top: 0.5rem;">A simple appointment system for patients and doctors</p>
    </div>

    <section class="grid-2 page-grid">
      <article class="card glass-card">
        <h3 style="color: var(--accent); margin-bottom: 1rem; font-weight: 600;">The problem we solve</h3>
        <p>Many clinics still rely on manual appointment booking methods such as phone calls or paper records. That approach creates long wait times, double-booking mistakes, and scattered appointment data.</p>
      </article>

      <article class="card glass-card">
        <h3 style="color: var(--accent-2); margin-bottom: 1rem; font-weight: 600;">Our goal</h3>
        <p>MedLink gives patients, doctors, and admins one place to manage scheduling, booking history, and doctor profiles without extra complexity.</p>
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
