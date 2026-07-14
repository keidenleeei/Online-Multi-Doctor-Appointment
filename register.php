<?php

session_start();
include "config.php";

$message = "";

if(isset($_POST['register'])){

    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if($password != $confirm_password){

        $message = "Passwords do not match.";

    }else{

        $check = mysqli_query($conn,
        "SELECT * FROM users WHERE email='$email'");

        if(mysqli_num_rows($check) > 0){

            $message = "Email already exists.";

        }else{

            $sql = "INSERT INTO users
            (full_name,email,password,phone,role)
            VALUES
            ('$full_name','$email','$password','$phone','patient')";

            if(mysqli_query($conn,$sql)){

                header("Location: login.php");
                exit();

            }else{

                $message = "Registration failed.";

            }

        }

    }

}

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register</title>

    <link rel="stylesheet" href="login.css">

</head>


<body>


<section class="login-page">


    <div class="login-bg"></div>


    <div class="login-wrapper">


        <div class="login-left">


            <div class="login-brand">

                <h2>Bird safety</h2>

                <small>Appointment System</small>

            </div>



            <h1>
                Welcome<br>
                My Bird
            </h1>



            <p>
                we are here to help you make sure your birds are safe and healthy.
            </p>


        </div>




        <div class="login-card">


            <h2>Create Account</h2>

               <p class="login-subtitle">
                Register a new patient account
              </p>



                <?php if($message != "") { ?>

                 <p style="color:red;">
                 <?php echo $message; ?>
                </p>

              <?php } ?>

           




            <form method="POST" action="register.php">




            <div class="login-field">

                 <label>Full Name</label>

                 <input
               type="text"
               name="full_name"
                placeholder="Enter your full name"
              required>

                </div>

                <div class="login-field">

                


                    <label>Email</label>


                    <input
                    type="email"
                    name="email"
                    placeholder="Enter your email"
                    required>


                </div>




                <div class="login-field">


                    <label>Password</label>


                    <input
                    type="password"
                    name="password"
                    placeholder="Enter password"
                    required>


                </div>

                <div class="login-field">

    <label>Phone Number</label>

    <input
    type="text"
    name="phone"
    placeholder="Enter your phone number"
    required>

</div>


<div class="login-field">

    <label>Confirm Password</label>

    <input
    type="password"
    name="confirm_password"
    placeholder="Confirm password"
    required>

</div>



                <button 
                class="login-btn"
                type="submit"
                name="register">

                    Create Account

                </button>




            </form>




            <div class="login-divider">

                OR

            </div>




            <button class="login-google">

                Continue with Google

            </button>





            <p class="login-register">


                Already have an account?


                <a href="login.php">
                  Login
                  </a>


            </p>



        </div>


    </div>


</section>



</body>

</html>