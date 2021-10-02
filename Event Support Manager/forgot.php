<?php
session_start();
require_once('mail/SMTP.php');
require_once('mail/PHPMailer.php');
require_once('mail/Exception.php');
require('connect.php');

use \PHPMailer\PHPMailer\PHPMailer;
use \PHPMailer\PHPMailer\Exception;

if (isset($_SESSION['fname'])) {
   header('Location: index.php');
}

function ccode(int $a)
{
   echo '

   <div class="container align-items-center mx-auto text-center m-4">
        <div class="row">
            <div class="mx-auto col-8 col-sm-8 col-md-6 col-lg-4">';
   if ($a == 1) {
      echo '
               <div class="alert" role="alert">
                  That security code is incorrect
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                     <span aria-hidden="true">&times;</span>
                  </button>
               </div>';
   } else {
      echo '
         
               <div class="alert" role="alert">
                  A code has been sent to your email
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                     <span aria-hidden="true">&times;</span>
                  </button>
               </div>';
   }

   echo '            
                <form method="get" action="forgot.php" name="confirm" id="confirm">
                    <div class="form-group my-3 py-1">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                    <i class="fas fa-user fa-2x"></i
                  ></span>
                            </div>
                            <input type="text" class="form-control" placeholder="Code:" aria-label="code" required name="code"/>
                        </div>
                    </div>
                    <button type="submit" class="tbutton mx-2" style="color: #fff;" name="submit" form="confirm">
              <i class="fas fa-user-check fa-lg formicon"></i>
              Reset Password
            </button>
                </form>
            </div>
        </div>
    </div>
   ';
}


?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <title>Terrace Support - Forgot Password</title>
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

    <h1 class="text-center heading my-4">Forgot Password</h1>

    <?php
   if (isset($_POST['submit'])) {
      // trim inputs
      $username = trim(stripslashes(htmlspecialchars($_POST['username'])));
      $pw = hash('sha256', $_POST['pass']);
      $cpw = hash('sha256', $_POST['cpass']);
      $email = ($username . '@terrace.qld.edu.au');

      $userinfo = DB::queryFirstRow('select * from users where email = %s', $email);
      if (!$userinfo) {
         ccode(0);
         // echo '
         // <div class="container">
         //    <div class="mx-auto col-8 col-sm-8 col-md-6 col-lg-4 text-center">
         //       <div class="alert" role="alert">
         //          An password reset email has been sent
         //          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
         //             <span aria-hidden="true">&times;</span>
         //          </button>
         //       </div> 
         //    </div>
         // </div>
         //    ';
      } else {
         if ($pw == $cpw) {
            // Save the details in the session
            $_SESSION['fdets'] = [$username, $pw, $email];
            // Generates a random code that will be sent in the email
            $_SESSION['fcode'] = substr(md5(microtime()), rand(0, 26), 5);

            //Send email with code attached

            $mail = new PHPMailer(true); // Passing `true` enables exceptions

            try {
               //settings
               $mail->SMTPDebug = 0; // Enable verbose debug output
               $mail->isSMTP(); // Set mailer to use SMTP
               $mail->Host = 'smtp.gmail.com';
               $mail->SMTPAuth = true; // Enable SMTP authentication
               $mail->Username = 'gtdigisol2020@gmail.com'; // SMTP username
               $mail->Password = 'securepassword'; // SMTP password
               $mail->SMTPSecure = 'ssl';
               $mail->Port = 465;

               $mail->setFrom('support@terrace.qld.edu.au', 'Terrace Support Program');

               //recipient
               $mail->addAddress($email, $userinfo['fname'] . ' ' . $userinfo['lname']);
               // Add a recipient

               //content
               $mail->isHTML(true); // Set email format to HTML
               $mail->Subject = 'Password Reset - Terrace Support Program';
               $mail->Body = 'Your confirmation code to reset your password is: ' . $_SESSION['fcode'];
               $mail->AltBody =
                  'Your confirmation code to reset your password is: ' . $_SESSION['fcode'];

               $mail->send();
            } catch (Exception $e) {
               echo 'Message could not be sent.';
               echo 'Mailer Error: ' . $mail->ErrorInfo;
            }

            // Display a confirmation code form
            ccode(0);
            $codeform = True;
         } else {
            echo '
            <div class="container">
            <div class="mx-auto col-8 col-sm-8 col-md-6 col-lg-4 text-center">
               <div class="alert" role="alert">
                  Passwords don\'t match
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                     <span aria-hidden="true">&times;</span>
                  </button>
               </div> 
            </div>
         </div>
            ';
         }
      }
   }

   if (isset($_GET['code'])) {
      if (!isset($_SESSION['fdets'])) {
         header('Location: register.php');
      } else {
         if (trim(stripslashes(htmlspecialchars($_GET['code']))) == $_SESSION['fcode']) {
            session_destroy();
            DB::update('users', [
               'password' => $_SESSION['fdets'][1]
            ], "email = %s", $_SESSION['fdets'][2]);
            $codeform = True;
            echo '
            <div class="container">
            <div class="mx-auto col-8 col-sm-8 col-md-6 col-lg-4 text-center">
               <div class="alert" role="alert">
                  Your password has been reset
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                     <span aria-hidden="true">&times;</span>
                  </button>
               </div> 
            </div>
         </div>
            ';
            echo '<script>
			        setTimeout(function(){window.location.href = "index.php";},1500);
			      </script>';
         } else {
            ccode(1);
            $codeform = True;
         }
      }
   }

   if (!isset($codeform)) {
      echo '

<div class="container align-items-center mx-auto text-center m-4">
        <div class="row">
            <div class="mx-auto col-8 col-sm-8 col-md-6 col-lg-4">
                <form method="post" action="forgot.php" name="forgot" id="forgot">
                    <div class="form-group my-3 py-1">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                    <i class="far fa-user fa-2x"></i
                  ></span>
                            </div>
                            <input type="text" class="form-control" placeholder="Username:" aria-label="Username" required name="username"/>
                        </div>
                    </div>
                    <div class="form-group my-3 py-1">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                    <i class="fas fa-lock fa-2x"></i
                  ></span>
                            </div>
                            <input type="password" class="form-control" placeholder="Password:" aria-label="Password" required name="pass"/>
                        </div>
                    </div>
                    <div class="form-group my-3 py-1">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                    <i class="fas fa-lock fa-2x"></i
                  ></span>
                            </div>
                            <input type="password" class="form-control" placeholder="Confirm Password:" aria-label="Confirm Password" required name="cpass"/>
                        </div>
                    </div>

                    <button type="submit" class="tbutton mx-2" style="color: #fff;" name="submit" form="forgot">
              <i class="fas fa-user-check fa-lg formicon"></i>
              Send Code
            </button>
                </form>
                <a href="login.php">
                    <p class="lead py-3 my-3">Login Instead</p>
                </a>
            </div>
        </div>
    </div>
    ';
   }

   ?>


</body>

</html>