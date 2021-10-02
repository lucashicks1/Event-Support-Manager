<?php
session_start();
if (isset($_SESSION['fname'])) {
    header('Location: index.php');
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Terrace Support - Home</title>
    <!-- Bootstrap css-->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" />
    <!-- Bootstrap JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,400;0,700;1,400;1,700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.1/css/all.min.css" />
    <!-- Local Files -->
    <link rel="stylesheet" href="css/main.css" />
    <link rel="shortcut icon" href="favicon.ico">
</head>

<body>

    <?php


    if (isset($_POST['username'])) {
        $username = trim(stripslashes(htmlspecialchars($_POST['username'])));
        $pass = hash('sha256', $_POST['password']);
        $email = ($username . '@terrace.qld.edu.au');

        require('connect.php');

        // CHECK USERBASE TO FIND USER
        $info = DB::queryFirstRow('select * from users where (email = %s) OR (uid = %s AND utype IN ("a","o")) ', $email, $username);

        if (!$info) {
            $a = 1;
            session_destroy();
        } else {
            if ($pass == $info['password']) {
                $_SESSION['uid'] = $info['uid'];
                $_SESSION['utype'] = $info['utype'];
                // $_SESSION['password'] = $info['password'];
                $_SESSION['fname'] = $info['fname'];
                $_SESSION['lname'] = $info['lname'];
                $_SESSION['yr'] = $info['yr'];
                $_SESSION['tutg'] = $info['tutg'];
                $_SESSION['house'] = $info['house'];
                $_SESSION['email'] = $info['email'];
                $b = 1;
            } else {
                $a = 1;
                session_destroy();
            }
        }
    }
    ?>


    <h1 class="text-center heading my-4">Login</h1>


    <div class="container align-items-center mx-auto text-center m-4">
        <div class="row">
            <div class="mx-auto col-8 col-sm-8 col-md-6 col-lg-4">

                <?php
                if (isset($a)) {
                    echo '
                        <div class="alert" role="alert">
                           Incorrect username or password
                           <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                           </button>
                        </div> 
                     ';
                } elseif (isset($b)) {
                    echo '
               <div class="alert" role="alert">
                  You are now logged in
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                     <span aria-hidden="true">&times;</span>
                  </button>
               </div> 
               <script>
			        setTimeout(function(){window.location.href = "index.php";},1500);
			      </script>

            ';
                }
                ?>

                <form method="post" action="login.php" name="login" id="login">
                    <div class="form-group my-3 py-1">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="far fa-user fa-2x"></i></span>
                            </div>
                            <input type="text" class="form-control" placeholder="Username:" aria-label="Username"
                                required name="username" />
                        </div>
                    </div>
                    <div class="form-group my-3 py-1">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fas fa-lock fa-2x"></i></span>
                            </div>
                            <input type="password" class="form-control" placeholder="Password:" aria-label="Password"
                                required name="password" />
                        </div>
                    </div>

                    <button type="submit" class="tbutton mx-2" style="color: #fff;" name="submit" form="login">
                        <i class="fas fa-user-check fa-lg formicon"></i>
                        Login
                    </button>
                </form>
                <a href="register.php">
                    <p class="lead py-2 my-2">Create Account</p>
                </a>
                <a href="forgot.php">
                    <p class="lead py-1 my-1">Forgot Password?</p>
                </a>
            </div>
        </div>
    </div>


</body>

</html>