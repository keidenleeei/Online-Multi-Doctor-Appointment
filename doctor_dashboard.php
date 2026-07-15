<?php

session_start();

include "config.php";

$doctor_user_id = $_SESSION['user_id'];

$sql = "
SELECT doctor_id
FROM doctors
WHERE user_id='$doctor_user_id'
";

$result = mysqli_query($conn,$sql);

$doctor = mysqli_fetch_assoc($result);

$doctor_id = $doctor['doctor_id'];

$sql = "

SELECT

appointments.appointment_id,
appointments.appointment_date,
appointments.status,

patient.full_name AS patient_name

FROM appointments

INNER JOIN users AS patient

ON appointments.patient_id = patient.user_id

WHERE appointments.doctor_id = '$doctor_id'

ORDER BY appointments.appointment_date ASC

";

// Confirm Appointment

if(isset($_GET['confirm'])){

    $appointment_id = $_GET['confirm'];

    mysqli_query($conn,"
    UPDATE appointments
    SET status='Confirmed'
    WHERE appointment_id='$appointment_id'
    ");

    header("Location: doctor_dashboard.php");
    exit();

}

// Cancel Appointment

if(isset($_GET['cancel'])){

    $appointment_id = $_GET['cancel'];

    mysqli_query($conn,"
    UPDATE appointments
    SET status='Cancelled'
    WHERE appointment_id='$appointment_id'
    ");

    header("Location: doctor_dashboard.php");
    exit();

}

$appointments = mysqli_query($conn,$sql);


if(!isset($_SESSION['user_id'])){

    header("Location: login.php");
    exit();

}


if($_SESSION['role'] != "doctor"){

    die("Access Denied.");

}

$doctor_user_id = $_SESSION['user_id'];

?>


<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<title>Doctor Dashboard</title>

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

<a href="index.php">
Home
</a>


<a href="doctor_dashboard.php" class="active">
Dashboard
</a>

<a href="doctor_schedule.php">
Manage Schedule
</a>


<a href="doctors.php">
Doctors
</a>


<a href="logout.php">
Logout
</a>


</nav>

</div>


</header>




<main class="wrap page-main">


<section class="card glass-card">


<h2>

Welcome Dr.

<?php echo htmlspecialchars($_SESSION['name']); ?>

</h2>


<h3>My Appointments</h3>

<?php

if(mysqli_num_rows($appointments)>0){

while($row=mysqli_fetch_assoc($appointments)){

?>

<div class="card glass-card" style="margin-top:20px;">

<p>

Patient:

<strong>

<?php echo htmlspecialchars($row['patient_name']); ?>

</strong>

</p>

<p>

Appointment Date:

<?php echo htmlspecialchars($row['appointment_date']); ?>

</p>

<p>

Status:

<?php

if($row['status']=="Pending"){

    echo "<strong style='color:orange;'>Pending</strong>";

}
else if($row['status']=="Cancelled"){

    echo "<strong style='color:red;'>Cancelled</strong>";

}
else{

    echo "<strong style='color:green;'>Confirmed</strong>";

}

?>

</p>

<?php

if($row['status']=="Pending"){

?>

<a
href="doctor_dashboard.php?confirm=<?php echo $row['appointment_id']; ?>"
class="btn primary">

Confirm

</a>

<a
href="doctor_dashboard.php?cancel=<?php echo $row['appointment_id']; ?>"
class="btn secondary">

Cancel

</a>

<?php

}

?>

</div>

<?php

}

}else{

?>

<p>No appointments found.</p>

<?php

}

?>


</section>


</main>


</body>

</html>