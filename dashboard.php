<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard - Online Multi Doctor Appointment System</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body class="page-bg">
  <div class="page-glow page-glow-a"></div>
  <div class="page-glow page-glow-b"></div>

  <header class="site-header content-header">
    <div class="wrap header-inner">
      <a class="brand" href="index.html"><span class="brand-mark">OMD</span><span>Online Multi Doctor<small>Appointment System</small></span></a>
      <nav class="nav">
        <a href="index.html">Home</a><a href="doctors.html">Doctors</a><a href="booking.html">Booking</a><a class="active" href="dashboard.html">Dashboard</a>
      </nav>
    </div>
  </header>

  <main class="wrap page-main">
    <section class="grid-2 page-grid">
      <article class="card glass-card">
        <h3>Dashboard overview</h3>
        <p>The dashboard shows booking status, upcoming appointments, and quick actions for clinic staff.</p>
        <div class="stats" style="margin-top:1rem">
          <div class="stat"><strong>28</strong><span class="muted">Bookings</span></div>
          <div class="stat"><strong>4</strong><span class="muted">Doctors</span></div>
          <div class="stat"><strong>3</strong><span class="muted">Roles</span></div>
        </div>
      </article>

      <article class="card glass-card">
        <h3>Role actions</h3>
        <ul class="list">
          <li>Patient: register, login, and book appointment</li>
          <li>Doctor: update availability and review schedules</li>
          <li>Admin: manage users, doctors, and bookings</li>
        </ul>
      </article>
    </section>
  </main>
</body>
</html>
