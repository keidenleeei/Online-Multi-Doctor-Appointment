<?php

session_start();

include "config.php";


$message = "";


// Check login

if(!isset($_SESSION['user_id'])){

    header("Location: login.php");
    exit();

}



$patient_id = $_SESSION['user_id'];
$selected_doctor = "";

if(isset($_GET['doctor_id'])){

    $selected_doctor = $_GET['doctor_id'];

}



// Submit booking

if(isset($_POST['submit_booking'])){


    $doctor_id = $_POST['doctor_id'];
    $date = $_POST['appointment_date'];



    $sql = "
    INSERT INTO appointments
    (
        patient_id,
        doctor_id,
        appointment_date,
        status
    )

    VALUES

    (
        '$patient_id',
        '$doctor_id',
        '$date',
        'Pending'
    )
    ";



    if(mysqli_query($conn,$sql)){


        $message = "Appointment booked successfully!";


    }else{


        $message = "Booking failed.";


    }


}





// Get doctors


$doctor_sql = "

SELECT 

doctors.doctor_id,
users.full_name,
doctors.specialization


FROM doctors


INNER JOIN users


ON doctors.user_id = users.user_id


WHERE users.role='doctor'

";



$doctors = mysqli_query($conn,$doctor_sql);



?>



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

<a class="active" href="booking.php">Booking</a>

<a href="dashboard.php">Dashboard</a>

</nav>


</div>

</header>






<main class="wrap page-main">


<section class="grid-2 page-grid">





<article class="form-card glass-card">


<h3>Booking form</h3>



<?php

if($message != ""){

echo "<p style='color:green;'>$message</p>";

}

?>





<form method="POST" action="booking.php">





<label class="field">

Patient ID

<input 

type="text"

value="<?php echo $_SESSION['user_id']; ?>"

disabled>

</label>






<label class="field">

Doctor


<select name="doctor_id" required>


<option value="">
Select Doctor
</option>



<?php while($doctor=mysqli_fetch_assoc($doctors)){ ?>


<option 

value="<?php echo $doctor['doctor_id']; ?>"

<?php 

if($selected_doctor == $doctor['doctor_id']){

    echo "selected";

}

?>

>


Dr.
<?php echo $doctor['full_name']; ?>

-

<?php echo $doctor['specialization']; ?>


</option>



<?php } ?>


</select>


</label>







<label class="field">

Date

<input 

type="date"

name="appointment_date"

required>


</label>








<div class="actions">


<button 
class="btn primary"

type="submit"

name="submit_booking">

Submit

</button>



<a href="index.php" class="btn secondary">

Cancel

</a>


</div>




</form>


</article>








<article class="card glass-card">


<h3>Simple booking flow</h3>


<ol class="list">


<li>Patient chooses doctor</li>


<li>Selects appointment date</li>


<li>System saves record</li>


<li>Doctor and admin can view it</li>


</ol>


</article>






</section>


</main>



</body>


</html>