<?php
session_start();
include "config.php";

$count_docs = 0;
$count_patients = 0;
$count_appts = 0;

$doc_count_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM doctors");
if ($doc_count_query) {
    $row = mysqli_fetch_assoc($doc_count_query);
    $count_docs = (int)$row['total'];
}

$patient_count_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'patient'");
if ($patient_count_query) {
    $row = mysqli_fetch_assoc($patient_count_query);
    $count_patients = (int)$row['total'];
}

$appt_count_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM appointments WHERE status = 'Confirmed' OR status = 'Approved'");
if ($appt_count_query) {
    $row = mysqli_fetch_assoc($appt_count_query);
    $count_appts = (int)$row['total'];
}

$showcase_doctors = [];
$showcase_query = mysqli_query(
    $conn,
    "
    SELECT doctors.doctor_id, users.full_name, doctors.specialization, doctors.experience, doctors.consultation_fee
    FROM doctors
    INNER JOIN users ON doctors.user_id = users.user_id
    WHERE users.role = 'doctor'
    ORDER BY users.full_name ASC
    LIMIT 4
    "
);
if ($showcase_query) {
    while ($doc = mysqli_fetch_assoc($showcase_query)) {
        $showcase_doctors[] = $doc;
    }
}

$specializations = [];
$specs_query = mysqli_query($conn, "SELECT DISTINCT specialization FROM doctors ORDER BY specialization ASC");
if ($specs_query) {
    while ($spec = mysqli_fetch_assoc($specs_query)) {
        $specializations[] = $spec['specialization'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MedLink Appointment System</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body class="home-page">
  <header class="home-nav animate-fade-down">
    <div class="wrap home-nav__inner">
      <a class="home-brand" href="index.php" aria-label="MedLink Appointment System home">
        <svg class="home-brand__icon" viewBox="0 0 256 256" aria-hidden="true" style="color: var(--accent);">
          <path fill="currentColor" d="M144 256 L27.598 256 L144 139.598 Z M256 207.5 L200 256 L200 56 L0 56 L48 0 L256 0 Z M0 204.402 L0 112 L92.402 112 Z" />
        </svg>
        <span>
          MedLink
          <small>Appointment System</small>
        </span>
      </a>

      <nav class="home-nav__links" aria-label="Main navigation">
        <a class="active" href="index.php">Home</a>
        <a href="about.php">About</a>
        <a href="features.php">Features</a>
        <a href="doctors.php">Doctors</a>
        <a href="booking.php">Booking</a>
        <?php if (isset($_SESSION['user_id'])) { ?>
          <a href="dashboard.php">Dashboard</a>
        <?php } ?>
      </nav>

      <div class="home-nav__actions">
        <?php if (isset($_SESSION['user_id'])) { ?>
          <a class="home-nav__cta" href="logout.php">Logout</a>
        <?php } else { ?>
          <a class="home-nav__cta" href="login.php">Login / Sign Up</a>
        <?php } ?>
      </div>
    </div>
  </header>

  <section class="hero-section">
    <div class="wrap hero-inner-container">
      <div class="hero-content animate-fade-up">
        <?php if (isset($_SESSION['user_id'])) { ?>
          <span class="welcome-badge">Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>!</span>
        <?php } ?>
        <h1 class="hero-title">Simple booking for every appointment</h1>
        <p class="hero-description">
          Connect patients, doctors, and admins in one clean booking workspace for fast scheduling and easy management.
        </p>
        <div class="hero-actions">
          <a href="booking.php" class="home-btn home-btn--dark">Book Appointment</a>
          <a href="#search-section" class="home-btn home-btn--light">Find Specialist</a>
        </div>
      </div>
      <div class="hero-visual animate-hero-rise">
        <div class="hero-image-wrapper">
          <img src="homm.png" alt="Doctor and patient consultation illustration" class="hero-illustration-img" />
          <div class="floating-badge badge-top">
            <span class="badge-icon">MD</span>
            <div>
              <strong>Specialists</strong>
              <small>Verified doctors</small>
            </div>
          </div>
          <div class="floating-badge badge-bottom">
            <span class="badge-icon">BK</span>
            <div>
              <strong>Instant booking</strong>
              <small>Real-time slot updates</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="search-section" class="search-section">
    <div class="wrap">
      <div class="search-widget-container animate-fade-up">
        <h3 class="search-widget-title">Quick Search Specialists</h3>
        <form action="doctors.php" method="GET" class="search-widget-form">
          <div class="search-field">
            <span class="field-icon">#</span>
            <input
              type="text"
              name="search"
              id="search-input"
              placeholder="Search by doctor name or specialty..."
            />
          </div>
          <div class="search-field">
            <span class="field-icon">/</span>
            <select id="spec-select" onchange="updateSearchVal()">
              <option value="">All Specializations</option>
              <?php foreach ($specializations as $spec) { ?>
                <option value="<?php echo htmlspecialchars($spec); ?>"><?php echo htmlspecialchars($spec); ?></option>
              <?php } ?>
            </select>
          </div>
          <button type="submit" class="search-submit-btn">Search Doctors</button>
        </form>
      </div>
    </div>
  </section>

  <section class="features-section">
    <div class="wrap">
      <div class="section-header">
        <span class="section-tag">Features</span>
        <h2>Core booking features</h2>
        <p>A simple system for searching doctors, booking time slots, and managing appointments.</p>
      </div>

      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon-box">1</div>
          <h3>Online Booking</h3>
          <p>Review available calendars, filter doctor names, and save appointments without manual queues.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon-box">2</div>
          <h3>Multiple Doctors</h3>
          <p>Browse qualified specialists and compare profiles in one directory.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon-box">3</div>
          <h3>Flexible Schedule</h3>
          <p>Doctors control available hours and patients can choose the time that fits best.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon-box">4</div>
          <h3>Secure Records</h3>
          <p>Role-based access keeps patient records and booking history organized.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="workflow-section">
    <div class="wrap">
      <div class="section-header">
        <span class="section-tag">Process</span>
        <h2>How it works</h2>
        <p>Book a doctor in four simple steps.</p>
      </div>

      <div class="workflow-grid">
        <div class="workflow-step">
          <div class="step-num">01</div>
          <h4>Find a doctor</h4>
          <p>Search by doctor name or filter specific specializations.</p>
        </div>
        <div class="workflow-step">
          <div class="step-num">02</div>
          <h4>Select a slot</h4>
          <p>Review available calendar slots and choose a date and time.</p>
        </div>
        <div class="workflow-step">
          <div class="step-num">03</div>
          <h4>Book appointment</h4>
          <p>Confirm the booking and save it to your account.</p>
        </div>
        <div class="workflow-step">
          <div class="step-num">04</div>
          <h4>Meet the doctor</h4>
          <p>Attend the consultation at the scheduled time.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="showcase-section">
    <div class="wrap">
      <div class="section-header">
        <span class="section-tag">Showcase</span>
        <h2>Featured doctors</h2>
        <p>Meet the specialists available in the system.</p>
      </div>

      <div class="showcase-grid">
        <?php if (!empty($showcase_doctors)) { ?>
          <?php foreach ($showcase_doctors as $doc) { ?>
            <?php
              $words = explode(" ", $doc['full_name']);
              $initials = "";
              foreach ($words as $w) {
                  $initials .= strtoupper(substr($w, 0, 1));
              }
              $initials = substr($initials, 0, 2);
            ?>
            <div class="showcase-card">
              <div class="doctor-avatar-circle"><?php echo htmlspecialchars($initials); ?></div>
              <h4>Dr. <?php echo htmlspecialchars($doc['full_name']); ?></h4>
              <span class="doctor-badge"><?php echo htmlspecialchars($doc['specialization']); ?></span>
              <div class="doctor-stats-row">
                <span>Experience: <strong><?php echo (int)$doc['experience']; ?> Years</strong></span>
                <span>Fee: <strong style="color: var(--accent-2);">RM <?php echo number_format((float)$doc['consultation_fee'], 2); ?></strong></span>
              </div>
              <a href="booking.php?doctor_id=<?php echo (int)$doc['doctor_id']; ?>" class="showcase-book-btn">Book Appointment</a>
            </div>
          <?php } ?>
        <?php } else { ?>
          <div class="empty-showcase-message">
            <p>No doctor records found yet. Add doctors from the Admin page.</p>
          </div>
        <?php } ?>
      </div>
    </div>
  </section>

  <section class="stats-banner-section">
    <div class="wrap stats-banner-grid">
      <div class="stats-banner-item">
        <h3><?php echo $count_docs; ?>+</h3>
        <p>Medical Doctors</p>
      </div>
      <div class="stats-banner-item">
        <h3><?php echo $count_patients; ?>+</h3>
        <p>Registered Patients</p>
      </div>
      <div class="stats-banner-item">
        <h3><?php echo $count_appts; ?>+</h3>
        <p>Confirmed Appointments</p>
      </div>
    </div>
  </section>

  <footer class="footer-redesign">
    <div class="wrap footer-grid">
      <div class="footer-brand-col">
        <a class="footer-brand" href="index.php">
          <svg viewBox="0 0 256 256" width="32" height="32" aria-hidden="true" style="color: var(--accent);">
            <path fill="currentColor" d="M144 256 L27.598 256 L144 139.598 Z M256 207.5 L200 256 L200 56 L0 56 L48 0 L256 0 Z M0 204.402 L0 112 L92.402 112 Z" />
          </svg>
          <span>
            MedLink
            <small>Appointment System</small>
          </span>
        </a>
        <p class="footer-tagline">A simple appointment workspace for patients, doctors, and admins.</p>
      </div>
      <div class="footer-links-col">
        <h4>Quick Navigation</h4>
        <a href="index.php">Home</a>
        <a href="about.php">About Us</a>
        <a href="features.php">System Features</a>
        <a href="doctors.php">Doctors list</a>
      </div>
      <div class="footer-links-col">
        <h4>Patient Portal</h4>
        <a href="booking.php">Book Appointment</a>
        <a href="dashboard.php">Personal Dashboard</a>
        <a href="login.php">Sign In / Register</a>
      </div>
      <div class="footer-contact-col">
        <h4>Contact Clinic</h4>
        <p>Location: Main St. Hospital</p>
        <p>Phone: +60 12-345 6789</p>
        <p>Email: clinic@carenest.com</p>
        <div class="footer-socials">
          <span class="social-icon">Facebook</span>
          <span class="social-icon">WhatsApp</span>
        </div>
      </div>
    </div>
    <div class="footer-copyright-banner">
      <div class="wrap footer-copyright-inner">
        <p>&copy; <?php echo date('Y'); ?> MedLink Appointment System. All rights reserved.</p>
        <p>Streamlined care for patient health.</p>
      </div>
    </div>
  </footer>

  <script>
    function updateSearchVal() {
      var specVal = document.getElementById('spec-select').value;
      if (specVal) {
        document.getElementById('search-input').value = specVal;
      }
    }
  </script>
</body>
</html>
