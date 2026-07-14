<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard - Bird Safety Appointment System</title>
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

            <a href="doctors.php">Doctors</a>

            <a href="booking.php">Booking</a>

            <a class="active" href="dashboard.php">Dashboard</a>

            <a href="logout.php">Logout</a>

        </nav>

    </div>

</header>


<main class="wrap page-main">

    <!-- Welcome Card -->

    <section class="card glass-card" style="margin-bottom:20px;">

        <h2>
            Welcome,
            <?php echo htmlspecialchars($_SESSION['name']); ?>
        </h2>

        <p>
            Role :
            <strong>
                <?php echo ucfirst($_SESSION['role']); ?>
            </strong>
        </p>

        <p>
            You have successfully logged in.
        </p>

    </section>



    <section class="grid-2 page-grid">

        <article class="card glass-card">

            <h3>Dashboard Overview</h3>

            <p>
                The dashboard shows booking status, upcoming appointments,
                and quick actions for clinic staff.
            </p>

            <div class="stats" style="margin-top:1rem">

                <div class="stat">
                    <strong>28</strong>
                    <span class="muted">Bookings</span>
                </div>

                <div class="stat">
                    <strong>4</strong>
                    <span class="muted">Doctors</span>
                </div>

                <div class="stat">
                    <strong>3</strong>
                    <span class="muted">Roles</span>
                </div>

            </div>

        </article>



        <article class="card glass-card">

            <h3>Role Actions</h3>

            <ul class="list">

                <li>Patient: Register, login and book appointments.</li>

                <li>Doctor: Update availability and manage schedules.</li>

                <li>Admin: Manage users, doctors and bookings.</li>

            </ul>

        </article>

    </section>

</main>

</body>
</html>