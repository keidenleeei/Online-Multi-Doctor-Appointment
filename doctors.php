<?php

session_start();
include "config.php";


$sql = "
SELECT 
    doctors.doctor_id，
    users.full_name,
    doctors.specialization,
    doctors.experience,
    doctors.consultation_fee

FROM doctors

INNER JOIN users

ON doctors.user_id = users.user_id

WHERE users.role = 'doctor'
";


$result = mysqli_query($conn, $sql);


?>


<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="UTF-8" />

  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>Doctors - Online Multi Doctor Appointment System</title>

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

      <a href="index.php">
        Home
      </a>


      <a class="active" href="doctors.php">
        Doctors
      </a>


      <a href="booking.php">
        Booking
      </a>


      <a href="dashboard.php">
        Dashboard
      </a>


    </nav>


  </div>

</header>




<main class="wrap page-main">


<section class="grid-2 page-grid">



<?php if(mysqli_num_rows($result) > 0){ ?>


<?php while($doctor = mysqli_fetch_assoc($result)){ ?>


<article class="card glass-card doctor">


    <div class="doctor-top">


        <h3>
            Dr.
            <?php echo htmlspecialchars($doctor['full_name']); ?>
        </h3>


        <span class="pill good">

            <?php echo htmlspecialchars($doctor['specialization']); ?>

        </span>


    </div>



    <p>
        Experience:
        <?php echo htmlspecialchars($doctor['experience']); ?>
        Years
    </p>



    <p>

        Consultation Fee:

        RM
        <?php echo number_format($doctor['consultation_fee'],2); ?>

    </p>



    <a 
href="booking.php?doctor_id=<?php echo $doctor['doctor_id']; ?>" 
class="home-btn home-btn--dark">

    Book Appointment

</a>



</article>



<?php } ?>



<?php }else{ ?>


<article class="card glass-card">

    <h3>No Doctors Available</h3>

    <p>
        There are currently no doctors registered in the system.
    </p>

</article>


<?php } ?>



</section>


</main>



</body>

</html>