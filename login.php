<?php

session_start();

include "config.php";


$error = "";


if(isset($_POST['login'])){


    $email = $_POST['email'];
    $password = $_POST['password'];


    $sql = "SELECT * FROM users 
            WHERE email='$email' 
            AND password='$password'";


    $result = mysqli_query($conn, $sql);


    if(mysqli_num_rows($result) > 0){


        $user = mysqli_fetch_assoc($result);


        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];


        header("Location: dashboard.php");
        exit();


    }else{

        $error = "Invalid email or password";

    }

}

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login</title>

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


            <h2>Sign In</h2>


            <p class="login-subtitle">
                Login to your account
            </p>



            <?php if($error != "") { ?>

                <p style="color:red;">
                    <?php echo $error; ?>
                </p>

            <?php } ?>




            <form method="POST" action="login.php">



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




                <div class="login-options">


                    <label>

                        <input type="checkbox">

                        Remember me

                    </label>



                    <a href="#">
                        Forgot Password?
                    </a>



                </div>





                <button 
                class="login-btn"
                type="submit"
                name="login">

                    Login

                </button>




            </form>




            <div class="login-divider">

                OR

            </div>




            <button class="login-google">

                Continue with Google

            </button>





            <p class="login-register">


                Don't have an account?


                <a href="register.php">
                Register
                 </a>


            </p>



        </div>


    </div>


</section>



</body>

</html>