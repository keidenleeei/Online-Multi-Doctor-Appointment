<?php

session_start();

include "config.php";

// Delete doctor

if(isset($_GET['delete_doctor'])){

    $doctor_id = $_GET['delete_doctor'];

    // Find corresponding user_id
    $sql = "
    SELECT user_id
    FROM doctors
    WHERE doctor_id='$doctor_id'
    ";

    $result = mysqli_query($conn,$sql);

    $doctor = mysqli_fetch_assoc($result);

    $user_id = $doctor['user_id'];

    // Delete doctor record
    mysqli_query($conn,"
    DELETE FROM doctors
    WHERE doctor_id='$doctor_id'
    ");

    // Delete user account
    mysqli_query($conn,"
    DELETE FROM users
    WHERE user_id='$user_id'
    ");

    header("Location: admin.php");
    exit();

}


if(!isset($_SESSION['user_id'])){

    header("Location: login.php");
    exit();

}

if($_SESSION['role'] != "admin"){

    die("Access Denied.");

}

// Add Doctor

if(isset($_POST['add_doctor'])){

    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $phone = $_POST['phone'];
    $specialization = $_POST['specialization'];
    $experience = $_POST['experience'];
    $consultation_fee = $_POST['consultation_fee'];

    // Check email

    $check = mysqli_query($conn,"
    SELECT *
    FROM users
    WHERE email='$email'
    ");

    if(mysqli_num_rows($check)>0){

        echo "<script>alert('Email already exists.');</script>";

    }else{

        // Insert into users

        mysqli_query($conn,"
        INSERT INTO users
        (
            full_name,
            email,
            password,
            phone,
            role
        )
        VALUES
        (
            '$full_name',
            '$email',
            '$password',
            '$phone',
            'doctor'
        )
        ");

        $user_id = mysqli_insert_id($conn);

        // Insert into doctors

        mysqli_query($conn,"
        INSERT INTO doctors
        (
            user_id,
            specialization,
            experience,
            consultation_fee
        )
        VALUES
        (
            '$user_id',
            '$specialization',
            '$experience',
            '$consultation_fee'
        )
        ");

        header("Location: admin.php");
        exit();

    }

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

$doctor_sql = "

SELECT

doctors.doctor_id,

users.full_name,

doctors.specialization,

doctors.experience,

doctors.consultation_fee

FROM doctors

INNER JOIN users

ON doctors.user_id = users.user_id

";

$doctor_result = mysqli_query($conn,$doctor_sql);

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

<th>Name</th>

<th>Specialization</th>

<th>Experience</th>

<th>Fee</th>

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

<hr style="margin:40px 0;">

<h2>Doctors List</h2>

<table>

<tr>

<th>Name</th>

<th>Specialization</th>

<th>Experience</th>

<th>Fee</th>

</tr>

<?php

while($doctor=mysqli_fetch_assoc($doctor_result)){

?>

<tr>

<td>

Dr.

<?php echo htmlspecialchars($doctor['full_name']); ?>

</td>

<td>

<?php echo htmlspecialchars($doctor['specialization']); ?>

</td>

<td>

<?php echo htmlspecialchars($doctor['experience']); ?>

Years

</td>

<td>

RM

<?php echo number_format($doctor['consultation_fee'],2); ?>

</td>

<td>

<a
href="admin.php?delete_doctor=<?php echo $doctor['doctor_id']; ?>"
class="btn secondary"
onclick="return confirm('Delete this doctor?');">

Delete

</a>

</td>

</tr>

<?php

}

?>

</table>
<hr style="margin:40px 0;">

<h2>Add Doctor</h2>

<form method="POST" action="admin.php">

<label>

Full Name

<input
type="text"
name="full_name"
required>

</label>

<br><br>

<label>

Email

<input
type="email"
name="email"
required>

</label>

<br><br>

<label>

Password

<input
type="password"
name="password"
required>

</label>

<br><br>

<label>

Phone

<input
type="text"
name="phone"
required>

</label>

<br><br>

<label>

Specialization

<input
type="text"
name="specialization"
required>

</label>

<br><br>

<label>

Experience

<input
type="number"
name="experience"
required>

</label>

<br><br>

<label>

Consultation Fee

<input
type="number"
step="0.01"
name="consultation_fee"
required>

</label>

<br><br>

<button
class="btn primary"
type="submit"
name="add_doctor">

Add Doctor

</button>

</form>


</div>

</main>

</body>

</html>