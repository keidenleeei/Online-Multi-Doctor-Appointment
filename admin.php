<?php

session_start();

include "config.php";

if(!isset($_SESSION['user_id'])){

    header("Location: login.php");
    exit();

}

if($_SESSION['role'] != "admin"){

    die("Access Denied.");

}
// Confirm appointment

if(isset($_GET['confirm'])){

    $appointment_id = $_GET['confirm'];

    $sql = "
    UPDATE appointments
    SET status='Confirmed'
    WHERE appointment_id='$appointment_id'
    ";

    mysqli_query($conn,$sql);

    header("Location: admin.php");
    exit();

}



// Cancel appointment

if(isset($_GET['cancel'])){

    $appointment_id = $_GET['cancel'];

    $sql = "
    UPDATE appointments
    SET status='Cancelled'
    WHERE appointment_id='$appointment_id'
    ";

    mysqli_query($conn,$sql);

    header("Location: admin.php");
    exit();

}

$sql = "

SELECT

appointments.appointment_id,
appointments.appointment_date,
appointments.status,

patient.full_name AS patient_name,

doctor.full_name AS doctor_name,

doctors.specialization

FROM appointments

INNER JOIN users AS patient

ON appointments.patient_id = patient.user_id

INNER JOIN doctors

ON appointments.doctor_id = doctors.doctor_id

INNER JOIN users AS doctor

ON doctors.user_id = doctor.user_id

ORDER BY appointments.appointment_date ASC

";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Admin Panel</title>

<link rel="stylesheet" href="styles.css">

</head>

<body class="page-bg">

<div class="page-glow page-glow-a"></div>
<div class="page-glow page-glow-b"></div>

<header class="site-header content-header">

<div class="wrap header-inner">

<a class="brand" href="index.php">

<span class="brand-mark">OMD</span>

<span>

Online Multi Doctor

<small>Appointment System</small>

</span>

</a>

<nav class="nav">

<a href="index.php">Home</a>

<a href="doctors.php">Doctors</a>

<a href="booking.php">Booking</a>

<a href="dashboard.php">Dashboard</a>

<a class="active" href="admin.php">Admin</a>

<a href="logout.php">Logout</a>

</nav>

</div>

</header>

<main class="wrap page-main">

<div class="card glass-card">

<h2>Admin Panel</h2>

<p>View all appointments.</p>

<table>

<tr>

<th>Patient</th>

<th>Doctor</th>

<th>Specialization</th>

<th>Date</th>

<th>Status</th>

<th>Action</th>

</tr>

<?php while($row = mysqli_fetch_assoc($result)){ ?>

<tr>

<td>

<?php echo htmlspecialchars($row['patient_name']); ?>

</td>

<td>

Dr. <?php echo htmlspecialchars($row['doctor_name']); ?>

</td>

<td>

<?php echo htmlspecialchars($row['specialization']); ?>

</td>

<td>

<?php echo htmlspecialchars($row['appointment_date']); ?>

</td>

<td>

<?php echo htmlspecialchars($row['status']); ?>

</td>

<td>

<?php

if($row['status']=="Pending"){

?>

<a
href="admin.php?confirm=<?php echo $row['appointment_id']; ?>"
class="btn primary">

Confirm

</a>

<a
href="admin.php?cancel=<?php echo $row['appointment_id']; ?>"
class="btn secondary">

Cancel

</a>

<?php

}else{

echo "-";

}

?>

</td>

</tr>

<?php } ?>

</table>

</div>

</main>

</body>

</html>