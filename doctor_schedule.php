<?php

session_start();

include "config.php";

$message = "";


if(!isset($_SESSION['user_id'])){

    header("Location: login.php");
    exit();

}


if($_SESSION['role']!="doctor"){

    die("Access Denied.");

}


// Get doctor id

$user_id = $_SESSION['user_id'];


$sql = "

SELECT doctor_id

FROM doctors

WHERE user_id='$user_id'

";


$result = mysqli_query($conn,$sql);

$doctor = mysqli_fetch_assoc($result);

$doctor_id = $doctor['doctor_id'];


// Delete Schedule

if(isset($_GET['delete_schedule'])){


    $schedule_id = $_GET['delete_schedule'];


    $delete_sql = "

    DELETE FROM schedules

    WHERE schedule_id='$schedule_id'

    AND doctor_id='$doctor_id'

    ";


    mysqli_query($conn,$delete_sql);


    header("Location: doctor_schedule.php");

    exit();


}

// Add schedule

if(isset($_POST['add_schedule'])){


    $date = $_POST['available_date'];

    $start = $_POST['start_time'];

    $end = $_POST['end_time'];

$check_sql = "

SELECT *

FROM schedules

WHERE doctor_id='$doctor_id'

AND available_date='$date'

AND (

    '$start' < end_time

    AND

    '$end' > start_time

)

";

$check_result = mysqli_query($conn,$check_sql);

    $insert = "

    INSERT INTO schedules

    (
        doctor_id,
        available_date,
        start_time,
        end_time
    )

    VALUES

    (
        '$doctor_id',
        '$date',
        '$start',
        '$end'
    )

    ";

if(mysqli_num_rows($check_result)>0){

    $message = "Schedule already exists.";

}
else{

    if(mysqli_query($conn,$insert)){

        $message = "Schedule added successfully!";

    }
    else{

        $message = "Failed to add schedule.";

    }

}

}

// Get doctor's schedules

$schedule_sql = "

SELECT

schedule_id,
available_date,
start_time,
end_time

FROM schedules

WHERE doctor_id='$doctor_id'

ORDER BY available_date ASC

";


$schedules = mysqli_query($conn,$schedule_sql);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<title>Manage Schedule</title>

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

<a href="doctor_dashboard.php">
Dashboard
</a>


<a class="active" href="doctor_schedule.php">
Manage Schedule
</a>


<a href="logout.php">
Logout
</a>


</nav>


</div>

</header>



<main class="wrap page-main">


<div class="card glass-card">


<h2>
Manage Schedule
</h2>

<?php

if($message!=""){

    if($message=="Schedule already exists."){

        echo "<p style='color:red;'>$message</p>";

    }else{

        echo "<p style='color:green;'>$message</p>";

    }

}

?>


<h3>
Add Available Time
</h3>



<form method="POST">


<label class="field">

Date

<input

type="date"

name="available_date"

required>

</label>



<br>



<label class="field">

Start Time

<input

type="time"

name="start_time"

required>

</label>



<br>



<label class="field">

End Time

<input

type="time"

name="end_time"

required>

</label>



<br>



<button

class="btn primary"

type="submit"

name="add_schedule">

Add Schedule

</button>



</form>

<hr style="margin:40px 0;">


<h3>
My Available Schedule
</h3>



<table>


<tr>

<th>Date</th>

<th>Start Time</th>

<th>End Time</th>

<th>Action</th>

</tr>



<?php

if(mysqli_num_rows($schedules)>0){


while($row=mysqli_fetch_assoc($schedules)){


?>


<tr>


<td>

<?php echo htmlspecialchars($row['available_date']); ?>

</td>


<td>

<?php echo htmlspecialchars($row['start_time']); ?>

</td>


<td>

<?php echo htmlspecialchars($row['end_time']); ?>

</td>

<td>


<a

href="doctor_schedule.php?delete_schedule=<?php echo $row['schedule_id']; ?>"

class="btn secondary"

onclick="return confirm('Delete this schedule?');">

Delete

</a>


</td>

</tr>


<?php


}


}else{


?>


<tr>

<td colspan="3">

No schedule available.

</td>

</tr>


<?php


}

?>


</table>


</div>


</main>


</body>

</html>