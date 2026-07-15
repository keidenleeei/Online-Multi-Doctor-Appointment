<?php

session_start();

include "config.php";

if(!isset($_SESSION['user_id'])){

    header("Location: login.php");
    exit();

}

if($_SESSION['role']!="admin"){

    die("Access Denied.");

}

$doctor_id = $_GET['doctor_id'];

// Update doctor information

if(isset($_POST['update_doctor'])){


    $full_name = $_POST['full_name'];

    $email = $_POST['email'];

    $phone = $_POST['phone'];

    $specialization = $_POST['specialization'];

    $experience = $_POST['experience'];

    $consultation_fee = $_POST['consultation_fee'];



    // Get user_id

    $user_sql = "

    SELECT user_id

    FROM doctors

    WHERE doctor_id='$doctor_id'

    ";


    $user_result = mysqli_query($conn,$user_sql);

    $user_data = mysqli_fetch_assoc($user_result);


    $user_id = $user_data['user_id'];



    // Update users table

    mysqli_query($conn,"

    UPDATE users

    SET

    full_name='$full_name',

    email='$email',

    phone='$phone'

    WHERE user_id='$user_id'

    ");




    // Update doctors table

    mysqli_query($conn,"

    UPDATE doctors

    SET

    specialization='$specialization',

    experience='$experience',

    consultation_fee='$consultation_fee'

    WHERE doctor_id='$doctor_id'

    ");



    header("Location: admin.php");

    exit();


}

$sql = "

SELECT

users.full_name,
users.email,
users.phone,

doctors.specialization,
doctors.experience,
doctors.consultation_fee

FROM doctors

INNER JOIN users

ON doctors.user_id = users.user_id

WHERE doctors.doctor_id='$doctor_id'

";

$result = mysqli_query($conn,$sql);

$doctor = mysqli_fetch_assoc($result);


?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<title>Edit Doctor</title>

<link rel="stylesheet" href="styles.css">

</head>


<body class="page-bg">


<main class="wrap page-main">


<div class="card glass-card">


<h2>Edit Doctor</h2>



<form method="POST" action="">


<label class="field">

Full Name

<input

type="text"

name="full_name"

value="<?php echo htmlspecialchars($doctor['full_name']); ?>"

required>

</label>



<br>



<label class="field">

Email

<input

type="email"

name="email"

value="<?php echo htmlspecialchars($doctor['email']); ?>"

required>

</label>



<br>



<label class="field">

Phone

<input

type="text"

name="phone"

value="<?php echo htmlspecialchars($doctor['phone']); ?>"

required>

</label>



<br>



<label class="field">

Specialization

<input

type="text"

name="specialization"

value="<?php echo htmlspecialchars($doctor['specialization']); ?>"

required>

</label>



<br>



<label class="field">

Experience

<input

type="number"

name="experience"

value="<?php echo $doctor['experience']; ?>"

required>

</label>



<br>



<label class="field">

Consultation Fee

<input

type="number"

step="0.01"

name="consultation_fee"

value="<?php echo $doctor['consultation_fee']; ?>"

required>

</label>



<br>



<button

class="btn primary"

type="submit"

name="update_doctor">

Save Changes

</button>


<a href="admin.php" class="btn secondary">

Cancel

</a>



</form>


</div>


</main>


</body>

</html>