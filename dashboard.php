<?php

session_start();

include "config.php";


if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit();

}


$user_id = $_SESSION['user_id'];

// Cancel appointment

if(isset($_GET['cancel'])){

    $appointment_id = $_GET['cancel'];

    $delete_sql = "
    DELETE FROM appointments
    WHERE appointment_id='$appointment_id'
    AND patient_id='$user_id'
    ";

    mysqli_query($conn, $delete_sql);

    header("Location: dashboard.php");
    exit();

}

// Count user's appointments

$booking_sql = "

SELECT COUNT(*) AS total

FROM appointments

WHERE patient_id='$user_id'

";


$booking_result = mysqli_query($conn,$booking_sql);

$booking_data = mysqli_fetch_assoc($booking_result);

$total_bookings = $booking_data['total'];




// Count doctors

$doctor_sql = "

SELECT COUNT(*) AS total

FROM doctors

";


$doctor_result = mysqli_query($conn,$doctor_sql);

$doctor_data = mysqli_fetch_assoc($doctor_result);

$total_doctors = $doctor_data['total'];




// Get user's appointments


$appointment_sql = "

SELECT 

appointments.appointment_id,
users.full_name,
doctors.specialization,
appointments.appointment_date,
appointments.status


FROM appointments


INNER JOIN doctors

ON appointments.doctor_id = doctors.doctor_id


INNER JOIN users

ON doctors.user_id = users.user_id


WHERE appointments.patient_id='$user_id'


ORDER BY appointments.appointment_date ASC


";


$appointments = mysqli_query($conn,$appointment_sql);

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

<strong>
<?php echo $total_bookings; ?>
</strong>

<span class="muted">
My Bookings
</span>

</div>



<div class="stat">

<strong>
<?php echo $total_doctors; ?>
</strong>

<span class="muted">
Doctors
</span>

</div>



<div class="stat">

<strong>
<?php echo $_SESSION['role']; ?>
</strong>

<span class="muted">
Role
</span>

</div>


</div>

<div style="margin-top:20px;">

<h3>My Appointments</h3>


<?php if(mysqli_num_rows($appointments)>0){ ?>


<?php while($row=mysqli_fetch_assoc($appointments)){ ?>


<div class="card glass-card">


<p>
Doctor:
<strong>
Dr. <?php echo htmlspecialchars($row['full_name']); ?>
</strong>
</p>


<p>
Specialization:
<?php echo htmlspecialchars($row['specialization']); ?>
</p>


<p>
Date:
<?php echo htmlspecialchars($row['appointment_date']); ?>
</p>


<p>
Status:
<strong>
<?php echo htmlspecialchars($row['status']); ?>
</strong>
</p>

<a
href="dashboard.php?cancel=<?php echo $row['appointment_id']; ?>"
class="btn secondary"
onclick="return confirm('Are you sure you want to cancel this appointment?');">

Cancel Appointment

</a>

</div>


<?php } ?>


<?php }else{ ?>


<p>
No appointments yet.
</p>


<?php } ?>


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