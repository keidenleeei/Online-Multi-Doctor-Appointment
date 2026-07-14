<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Online Multi Doctor Appointment System</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body class="landing-page home-page">
  <section
    class="home-hero"
    style="background-image:url('https://images.higgs.ai/?default=1&output=webp&url=https%3A%2F%2Fd8j0ntlcm91z4.cloudfront.net%2Fuser_38xzZboKViGWJOttwIXH07lWA1P%2Fhf_20260611_133301_d5f2a94a-b22e-4e4a-a6b6-eacdddf1f5b0.png&w=1280&q=85');"
  >
    <div class="home-hero__veil"></div>

    <header class="home-nav animate-fade-down">
      <div class="wrap home-nav__inner">
        <a class="home-brand" href="index.html" aria-label="Online Multi Doctor Appointment System home">
          <svg class="home-brand__icon" viewBox="0 0 256 256" aria-hidden="true">
            <path fill="currentColor" d="M144 256 L27.598 256 L144 139.598 Z M256 207.5 L200 256 L200 56 L0 56 L48 0 L256 0 Z M0 204.402 L0 112 L92.402 112 Z" />
          </svg>
          <span>
            Bird safety
            <small>Appointment System</small>
          </span>
        </a>

        <nav class="home-nav__links" aria-label="Main navigation">

    <a class="active" href="index.php">Home</a>

    <a href="doctors.php">Doctors</a>

    <a href="booking.php">Booking</a>

    <?php if(isset($_SESSION['user_id'])){ ?>

        <a href="dashboard.php">Dashboard</a>

    <?php } ?>

</nav>

        <?php if(isset($_SESSION['user_id'])){ ?>

    <a class="home-nav__cta" href="logout.php">
        Logout
    </a>

<?php }else{ ?>

    <a class="home-nav__cta" href="login.php">
        Login
    </a>

<?php } ?>

        </div>
    </header>

    <div class="home-hero__content wrap">
      <div class="home-hero__spacer home-hero__spacer--top"></div>

      <div class="home-copy">
        <?php if(isset($_SESSION['user_id'])){ ?>

<p style="
margin-bottom:15px;
font-size:18px;
font-weight:600;
color:#111827;
">

Welcome,
<?php echo htmlspecialchars($_SESSION['name']); ?>

</p>

<?php } ?>
        <h1 class="home-title">
          <span class="animate-fade-up">Book faster.</span>
          <span class="animate-fade-up delay-1">Manage appointments effortlessly.</span>
        </h1>

        <form class="home-search animate-fade-up delay-2" action="booking.php">
          <input
            type="text"
            placeholder="Which doctor do you need today?"
            aria-label="Search doctor"
          />
          <button type="submit" aria-label="Go to booking page">Go</button>
        </form>

        <p class="home-desc animate-fade-up delay-3">
          Fast bookings for patients, doctors, and clinic staff in one clean system.
        </p>

        <div class="home-actions animate-fade-up delay-4">

<?php if(isset($_SESSION['user_id'])){ ?>

    <a class="home-btn home-btn--dark" href="dashboard.php">
        Dashboard
    </a>

    <a class="home-btn home-btn--light" href="booking.php">
        Book Appointment
    </a>

<?php }else{ ?>

    <a class="home-btn home-btn--dark" href="login.php">
        Login
    </a>

    <a class="home-btn home-btn--light" href="booking.php">
        Book Appointment
    </a>

<?php } ?>

</div>
      </div>

      <div class="home-hero__spacer home-hero__spacer--middle"></div>

      <div class="home-dashboard animate-hero-rise" aria-label="Clinic dashboard preview">
        <div class="home-dashboard__window">
          <div class="home-dashboard__bar">
            <div class="home-dots" aria-hidden="true">
              <span></span><span></span><span></span>
            </div>
            <div class="home-url">CareNest Clinic workspace</div>
            <div class="home-bar-icons" aria-hidden="true">
              <span>R</span>
              <span>S</span>
              <span>+</span>
            </div>
          </div>

          <div class="home-dashboard__body">
            <aside class="home-sidebar">
              <div class="home-sidebar__brand">
                <span class="home-workspace-badge">C</span>
                <div>
                  <strong>CareNest</strong>
                  <small>Clinic workspace</small>
                </div>
              </div>

              <div class="home-sidebar__nav">
                <span>Overview</span>
                <span>Patients</span>
                <span>Appointments</span>
              </div>

              <div class="home-sidebar__cards">
                <div>
                  <strong>Ready to release</strong>
                  <p>Booking flow</p>
                </div>
                <div>
                  <strong>Ready to release</strong>
                  <p>Doctor schedule</p>
                </div>
              </div>
            </aside>

            <section class="home-main">
              <div class="home-main__head">
                <div>
                  <strong>CareNest</strong>
                  <p>Online multi doctor appointment system</p>
                </div>
                <a class="home-main__button" href="dashboard.php">Open dashboard</a>
              </div>

              <div class="home-stats">
                <div><span>4</span><small>Doctors</small></div>
                <div><span>12</span><small>Slots today</small></div>
                <div><span>28</span><small>Bookings</small></div>
                <div><span>3</span><small>Roles</small></div>
              </div>

              <div class="home-cards">
                <article>
                  <strong>Patient flow</strong>
                  <p>Register, log in, choose doctor, and book a slot.</p>
                </article>
                <article>
                  <strong>Doctor flow</strong>
                  <p>Update availability and review appointment history.</p>
                </article>
                <article>
                  <strong>Admin flow</strong>
                  <p>Manage users, records, and booking logs in one place.</p>
                </article>
              </div>

              <div class="home-inbox">
                <div class="home-inbox__row home-inbox__head">
                  <span>Appointment</span>
                  <span>Doctor</span>
                  <span>Status</span>
                </div>
                <div class="home-inbox__row">
                  <span>Fever check</span>
                  <span>Dr. Aminah</span>
                  <span class="status-draft">Confirmed</span>
                </div>
                <div class="home-inbox__row">
                  <span>Child review</span>
                  <span>Dr. Wei Ling</span>
                  <span class="status-live">Live</span>
                </div>
                <div class="home-inbox__row">
                  <span>Heart check</span>
                  <span>Dr. Raj</span>
                  <span class="status-draft">Pending</span>
                </div>
              </div>
            </section>
          </div>
        </div>
      </div>

      <div class="home-hero__spacer home-hero__spacer--bottom"></div>
    </div>

    <img
      class="home-grass"
      src="https://res.cloudinary.com/dy5er7kv5/image/upload/q_auto/f_auto/v1781191264/grass_eam204.png"
      alt=""
      aria-hidden="true"
    />
  </section>
</body>
</html>
