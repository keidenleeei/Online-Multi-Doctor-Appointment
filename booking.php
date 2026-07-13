<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Booking - Online Multi Doctor Appointment System</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body class="page-bg">
  <div class="page-glow page-glow-a"></div>
  <div class="page-glow page-glow-b"></div>

  <header class="site-header content-header">
    <div class="wrap header-inner">
      <a class="brand" href="index.html"><span class="brand-mark">OMD</span><span>Online Multi Doctor<small>Appointment System</small></span></a>
      <nav class="nav">
        <a href="index.html">Home</a><a href="doctors.html">Doctors</a><a class="active" href="booking.html">Booking</a><a href="dashboard.html">Dashboard</a>
      </nav>
    </div>
  </header>

  <main class="wrap page-main">
    <section class="grid-2 page-grid">
      <article class="form-card glass-card">
        <h3>Booking form</h3>
        <form>
          <label class="field">Patient name <input type="text" placeholder="Your name" /></label>
          <label class="field">Email <input type="email" placeholder="name@example.com" /></label>
          <label class="field">Doctor
            <select>
              <option>Dr. Aminah Tan</option>
              <option>Dr. Raj Kumar</option>
              <option>Dr. Wei Ling</option>
              <option>Dr. Farid Nordin</option>
            </select>
          </label>
          <div class="grid-2">
            <label class="field">Date <input type="date" /></label>
            <label class="field">Time <input type="time" /></label>
          </div>
          <label class="field">Reason <textarea placeholder="Short reason for visit"></textarea></label>
          <div class="actions">
            <button class="btn primary" type="button">Submit</button>
            <button class="btn secondary" type="button">Cancel</button>
          </div>
        </form>
      </article>

      <article class="card glass-card">
        <h3>Simple booking flow</h3>
        <ol class="list">
          <li>Patient chooses doctor</li>
          <li>Selects date and time</li>
          <li>System saves record</li>
          <li>Doctor and admin can view it</li>
        </ol>
      </article>
    </section>
  </main>
</body>
</html>
