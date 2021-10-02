<?php
session_start();
require('connect.php');
if (!$_SESSION['fname']) {
    header('Location: login.php');
}
if ($_SESSION['utype'] != 'a') {
    header('Location: index.php');
}

// If adding an event
if (isset($_POST['etype'])) {
    // Checks and Add event
    $stime = $_POST['stime'];
    $etime = $_POST['etime'];
    if ($stime < $etime) {
        // Continue to add event
        DB::insert('events', array(
            'starttime' => $stime,
            'endtime' => $etime,
            'etype' => trim(stripslashes(htmlspecialchars($_POST['etype']))),
            'ename' => trim(stripslashes(htmlspecialchars($_POST['ename']))),
            'place' => trim(stripslashes(htmlspecialchars($_POST['place'])))
        ));
        // Event has been added
        $status = 1;
    } else {
        // Incorrect times
        $status = 2;
    }
}

// If user is being added
if (isset($_POST['fname'])) {
    // Cleaning inputs
    $uid = trim(stripslashes(htmlspecialchars($_POST['uid'])));
    // Checking if uid is already being used
    $tcheck = DB::queryFirstField('Select uid from users where uid = %s', $uid);
    if (!$tcheck) {
        $email =
            trim(stripslashes(htmlspecialchars($_POST['email'])));
        $emailcheck = DB::queryFirstField('Select uid from users where email = %s', $email);
        // Checking if email is already being used
        if (!$emailcheck) {
            // Hasing passwordss
            $pw = hash('sha256', $_POST['pass']);
            $cpw = hash('sha256', $_POST['cpass']);
            if ($pw == $cpw) {
                DB::insert('users', array(
                    'uid' => $uid,
                    'utype' => 'o',
                    'password' => $pw,
                    'lname' => trim(stripslashes(htmlspecialchars($_POST['lname']))),
                    'fname' => trim(stripslashes(htmlspecialchars($_POST['fname']))),
                    'email' => $email
                ));
                // Official added
                $status = 3;
            } else {
                // Passwords dont match
                $status = 4;
            }
        } else {
            // Email being used
            $status = 5;
        }
    } else {
        // ID is being used
        $status = 6;
    }
}

// Admin has uploaded a file
if (isset($_FILES['filea'])) {
    // Minimise error reporting
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
    require('connect.php');
    // Get file information
    $file_name = $_FILES['filea']['name'];
    $file_size = $_FILES['filea']['size'];
    $file_tmp = $_FILES['filea']['tmp_name'];
    $file_type = $_FILES['filea']['type'];
    $file_ext = strtolower(end(explode('.', $_FILES['filea']['name'])));
    // Check if csv file extension
    if ($file_ext == 'csv') {
        // Open file
        $h = fopen($file_tmp, "r");
        $array = [];
        // Open the file for reading
        if (($h = fopen($file_tmp, "r")) !== FALSE) {
            // Each line in the file is converted into an individual array that we call $data
            // The items of the array are comma separated
            while (($data = fgetcsv($h, 1000, ",")) !== FALSE) {
                // Each individual array is being pushed into the nested array
                $array[] = $data;
            }
            // Close the file
            fclose($h);
        }
        // Get the table the admin is entering into
        // Then enter into that table
        $table = $_POST['table'];
        if ($table == "events") {
            for ($l = 0; $l < count($array); $l++) {
                DB::insert('events', array(
                    'starttime' => $array[$l][0], 'endtime' => $array[$l][1], 'etype' => $array[$l][2], 'ename' => $array[$l][3], 'place' => $array[$l][4]
                ));
            }
        } elseif ($table == "userbase") {
            for ($l = 0; $l < count($array); $l++) {
                DB::insert("userbase", array('sid' => $array[$l][0], 'lname' => $array[$l][1], 'fname' => $array[$l][2], 'yr' => $array[$l][3], 'tutg' => $array[$l][4], 'house' => $array[$l][5], 'email' => $array[$l][6]));
            }
        } else {
            for ($l = 0; $l < count($array); $l++) {
                DB::insert("users", array('uid' => $array[$l][0], 'utype' => $array[$l][1], 'password' => hash('sha256', $array[$l][2]), 'fname' => $array[$l][3], 'lname' => $array[$l][4], 'yr' => $array[$l][5], 'tutg' => $array[$l][6], 'house' => $array[$l][7], 'email' => $array[$l][8]));
            }
        }
    } else {
        echo 'File is not a csv file';
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Terrace Support - Dashboard</title>
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

    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="imgs/Terrace.png" width="270" height="100%" alt="" class="img-fluid">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span>Menu</span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="events.php">Events <span class="sr-only">(current)</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="houses.php">Houses</a>
                    </li>
                    <?php
                    if ($_SESSION['utype'] == 's') {
                        echo '
                  <li class="nav-item">
                     <a class="nav-link" href="Attendances.php">Attendances</a>
                  </li>
                  ';
                    } elseif ($_SESSION['utype'] == 'a') {
                        echo '
                  <li class="nav-item active">
                     <a class="nav-link" href="dashboard.php">Dashboard</a>
                  </li>
                  ';
                    }
                    ?>
                    <li class="nav-item cta dropdown">
                        <a class="nav-link" id="navbarDropdown" role="button" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            <i class="far fa-user fa-lg"></i>
                            <?php
                            echo $_SESSION['fname'];
                            ?>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="logout.php">LOG OUT</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <h1 class="text-center heading my-4">Admin Dashboard</h1>
    <?php

    if (isset($status)) {
        if ($status == 1) {
            echo '
            <div class="row text-center">
        <div class="mx-auto col-8 col-sm-8 col-md-6 col-lg-4">
            <div class="alert" role="alert">
                Event has been added
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    </div>
            ';
        } elseif ($status == 2) {
            echo '
            <div class="row text-center">
        <div class="mx-auto col-8 col-sm-8 col-md-6 col-lg-4">
            <div class="alert" role="alert">
                Event times are incorrect
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    </div>
            ';
        } elseif ($status == 3) {
            echo '
            <div class="row text-center">
        <div class="mx-auto col-8 col-sm-8 col-md-6 col-lg-4">
            <div class="alert" role="alert">
                Official has been added
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    </div>
            ';
        } elseif ($status == 4) {
            echo '
            <div class="row text-center">
        <div class="mx-auto col-8 col-sm-8 col-md-6 col-lg-4">
            <div class="alert" role="alert">
                Passwords don\'t match
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    </div>
            ';
        } elseif ($status == 5) {
            // email
            echo '
            <div class="row text-center">
        <div class="mx-auto col-8 col-sm-8 col-md-6 col-lg-4">
            <div class="alert" role="alert">
                Email is already being used
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    </div>
            ';
        } else {
            echo '
            <div class="row text-center">
        <div class="mx-auto col-8 col-sm-8 col-md-6 col-lg-4">
            <div class="alert" role="alert">
                That ID is already used
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    </div>
            ';
        }
    }

    ?>

    <div class="row text-center mx-auto align-items-center">
        <div class="col-12 col-sm-10 offset-sm-1 col-lg-10 offset-lg-1">
            <div class="row mx-auto text-center">
                <div class="mx-auto col-sm-12 col-md-10 col-lg-5 text-center mx-auto">
                    <div class="row">
                        <div class="col-10 offset-1 col-lg-8 offset-lg-2 my-4">
                            <h3 class="text-center">Add Event</h3>
                            <form method="post" action="dashboard.php" name="eventadd" id="eventadd">
                                <div class="form-group my-3 py-1">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="fas fa-th-large fa-2x"></i></span>
                                        </div>
                                        <input type="text" autocomplete="false" class="form-control"
                                            placeholder="Category:" aria-label="Event Category" required name="etype"
                                            list="cats" />
                                        <?php
                                        echo '<datalist id="cats">';
                                        $cats = DB::query('Select distinct etype from events');
                                        foreach ($cats as $cat) {
                                            echo '<option value="' . $cat['etype'] . '">' . $cat['etype'] . '</option>';
                                        }

                                        echo '</datalist>';
                                        ?>
                                    </div>
                                </div>
                                <div class="form-group my-3 py-1">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="far fa-clock fa-2x"></i></span>
                                        </div>
                                        <input type="text" class="form-control" placeholder="Start Time:"
                                            aria-label="Start Time" required name="stime" id="dt1">
                                    </div>
                                </div>
                                <div class="form-group my-3 py-1">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="far fa-clock fa-2x"></i></span>
                                        </div>
                                        <input type="text" class="form-control" placeholder="End Time:"
                                            aria-label="End Time" required name="etime" id="dt2">
                                    </div>
                                </div>
                                <div class="form-group my-3 py-1">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="fas fa-info fa-2x"
                                                    style="padding-right:10px;padding-left:10px;"></i></span>
                                        </div>
                                        <input type="text" class="form-control" placeholder="Event Name:"
                                            aria-label="Event Name" required name="ename" />
                                    </div>
                                </div>
                                <div class="form-group my-3 py-1">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="fas fa-map-marker-alt fa-2x"
                                                    style="padding-right:4px;padding-left:4px;"></i></span>
                                        </div>
                                        <input type="text" autocomplete="false" class="form-control"
                                            placeholder="Location:" aria-label="Event Location" required name="place"
                                            list="places" />
                                        <?php
                                        echo '<datalist id="places">';
                                        $placelist = DB::query('Select distinct place from events');
                                        foreach ($placelist as $place) {
                                            echo '<option value="' . ucfirst($place['place']) . '">' . ucfirst($place['place']) . '</option>';
                                        }

                                        echo '</datalist>';
                                        ?>
                                    </div>
                                </div>

                                <button type="submit" class="tbutton mx-2" style="color: #fff;" name="eadd"
                                    form="eventadd">
                                    <i class="fas fa-plus fa-lg formicon"></i>
                                    Add Event
                                </button>
                            </form>
                            <button class="tbutton edit-events my-2" href="edit.php"><a href="edit.php">Edit
                                    Events</a></button>
                        </div>
                    </div>
                </div>
                <div class="mx-auto col-sm-12 col-md-10 col-lg-5 text-center mx-auto">
                    <div class="row">
                        <div class="col-10 offset-1 col-lg-8 offset-lg-2 my-4">
                            <h3 class="text-center">Add Official</h3>
                            <form method="post" action="dashboard.php" name="officialadd" id="officialadd">
                                <div class="form-group my-3 py-1">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="fas fa-hashtag fa-2x"
                                                    style="padding-right:2px;padding-left:2px;"></i></span>
                                        </div>
                                        <input type="text" class="form-control" placeholder="Teacher ID:"
                                            aria-label="Teacher ID" required name="uid" />
                                    </div>
                                </div>
                                <div class="form-group my-3 py-1">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="far fa-user fa-2x"
                                                    style="padding-right:2px;padding-left:2px;"></i></span>
                                        </div>
                                        <input type="text" class="form-control" placeholder="First Name:"
                                            aria-label="First Name" required name="fname" />
                                    </div>
                                </div>
                                <div class="form-group my-3 py-1">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="far fa-user fa-2x"
                                                    style="padding-right:2px;padding-left:2px;"></i></span>
                                        </div>
                                        <input type="text" class="form-control" placeholder="Last Name:"
                                            aria-label="Last Name" required name="lname" />
                                    </div>
                                </div>
                                <div class="form-group my-3 py-1">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="fas fa-lock fa-2x"
                                                    style="padding-right:2px;padding-left:2px;"></i></span>
                                        </div>
                                        <input type="password" class="form-control" placeholder="Password:"
                                            aria-label="Password" required name="pass" />
                                    </div>
                                </div>
                                <div class="form-group my-3 py-1">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="fas fa-lock fa-2x"
                                                    style="padding-right:2px;padding-left:2px;"></i></span>
                                        </div>
                                        <input type="password" class="form-control" placeholder="Confirm Password:"
                                            aria-label="Confirm Password" required name="cpass" />
                                    </div>
                                </div>
                                <div class="form-group my-3 py-1">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="far fa-envelope fa-2x"></i></span>
                                        </div>
                                        <input type="email" class="form-control" placeholder="Email address:"
                                            aria-label="Email" required name="email" />

                                    </div>
                                </div>


                                <button type="submit" class="tbutton mx-2" style="color: #fff;" name="oadd"
                                    form="officialadd">
                                    <i class="fas fa-user-check fa-lg formicon"></i>
                                    Register
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="mx-auto col-sm-12 col-md-10 col-lg-5 text-center mx-auto">
                    <div class="row">
                        <div class="col-10 offset-1 col-lg-8 offset-lg-2 my-4">
                            <h3 class="text-center">Database Import</h3>
                            <form method="post" action="" name="fileimport" id="import" enctype="multipart/form-data">
                                <div class="form-group my-3 py-1 mx-auto text-center">
                                    <div class="input-group mb-3 test mx-auto text-center">
                                        <select class="text-center mx-auto px-2 mt-4" required name="table">
                                            <option value="" disabled selected style="color: #787575"><span
                                                    class="placeholder-s"> Database
                                                    Table </span>
                                            </option>
                                            <option value="events">Events</option>
                                            <option value="userbase">Userbase</option>
                                            <option value="users">Users</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group my-3 py-1">
                                    <div class="input-group mb-3">
                                        <div class="file-upload text-center mx-auto">
                                            <input class="file-upload__input" type="file" name="filea" id="file"
                                                required>
                                            <button class="tbutton file-upload__button" type="button"><span
                                                    class="file-input-text">File</span></button>
                                            <span class="file-upload__label" style="color:red;"></span>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="tbutton mx-2" style="color: #fff;" name="oadd"
                                    form="import">
                                    <i class="fas fa-upload fa-lg formicon"></i>
                                    Import
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        $("#dt1").focus(function() {
            $(this).attr({
                type: 'datetime-local'
            });
        });
    });
    $(document).ready(function() {
        $("#dt2").focus(function() {
            $(this).attr({
                type: 'datetime-local'
            });
        });
    });
    Array.prototype.forEach.call(
        document.querySelectorAll(".file-upload__button"),
        function(button) {
            const hiddenInput = button.parentElement.querySelector(
                ".file-upload__input"
            );
            const label = button.parentElement.querySelector(".file-input-text");
            const defaultLabelText = "Select File";

            // Set default text for label
            label.textContent = defaultLabelText;
            label.title = defaultLabelText;

            button.addEventListener("click", function() {
                hiddenInput.click();
            });

            hiddenInput.addEventListener("change", function() {
                const filenameList = Array.prototype.map.call(hiddenInput.files, function(
                    file
                ) {
                    return file.name;
                });

                label.textContent = filenameList;
                label.title = label.textContent;
            });
        }
    );
    </script>

</body>

</html>