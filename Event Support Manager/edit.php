<?php
session_start();
if (!$_SESSION['fname']) {
    header('Location: login.php');
}

if (($_SESSION['utype'] != 'a')) {
    header('index.php');
}

require('connect.php');

// Event is being edited
if (isset($_POST['etype'])) {
    // Checks and Add event
    $stime = $_POST['stime'];
    $etime = $_POST['etime'];
    // Checks the times
    if ($stime < $etime) {
        // Continue to add event
        DB::update('events', array(
            'starttime' => $stime,
            'endtime' => $etime,
            'etype' => trim(stripslashes(htmlspecialchars($_POST['etype']))),
            'ename' => trim(stripslashes(htmlspecialchars($_POST['ename']))),
            'place' => trim(stripslashes(htmlspecialchars($_POST['place'])))
        ), "eid = %s", $_GET['eid']);
        // Update successful
        $status = 1;
    } else {
        // Times are wrong
        $status = 2;
    }
}

// Removes events
if (isset($_GET['remove'])) {
    $eid = $_GET['remove'];
    // Removes all attendances first - no chance for constraint error
    DB::delete('userevents', 'eid=%s', $eid);
    // Then remove the event
    DB::delete('events', 'eid=%s', $eid);
}



?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Event Edit</title>
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
                    <li class="nav-item active">
                        <a class="nav-link" href="index.php">Home <span class="sr-only">(current)</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="events.php">Events</a>
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
                  <li class="nav-item">
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

    <h1 class="text-center heading my-4">Event Edit</h1>

    <?php

    if (isset($_GET['eid'])) {
        $eid = $_GET['eid'];
        $row = DB::queryfirstrow('Select * from events where eid = %s', $eid);
        if ($row) {
            echo '
            <div class="modal" tabindex="-1" role="dialog" id="myModal">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content text-center">
                <div class="modal-header text-center">
                    <div class="col-12">
                        <h3 class="text-center modal-title col-12 heading">
                            Edit Event
                        </h3>
                        <br/>';

            if (isset($status)) {
                if ($status == 1) {
                    echo '
            <div class="row">
               <div class="mx-auto col-10 col-sm-10 col-md-10 col-lg-8 text-center">
                  <div class="alert" role="alert">
                        Event has been updated
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                           <span aria-hidden="true">&times;</span>
                        </button>
                     </div>
                  </div> 
               </div>
            ';
                } else {
                    echo '
            <div class="row">
               <div class="mx-auto col-10 col-sm-10 col-md-10 col-lg-8 text-center">
                  <div class="alert" role="alert">
                        Event times are incorrect
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                           <span aria-hidden="true">&times;</span>
                        </button>
                     </div>
                  </div> 
               </div>
            ';
                }
            }

            echo '</div>
               </div>
                <div class="modal-body">
                    <div class="col-lg-5 col-8 text-center mx-auto">
                        <form method="post" action="edit.php?eid=' . $eid . '" name="eventedit">
                                <div class="form-group my-3 py-1">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="fas fa-th-large fa-2x"></i></span>
                                        </div>
                                        <input type="text" autocomplete="false" class="form-control"
                                            placeholder="Category:" aria-label="Event Category" value="' . $row['etype'] . '" required name="etype"
                                            list="cats" />';

            echo '<datalist id="cats">';
            $cats = DB::query('Select distinct etype from events');
            foreach ($cats as $cat) {
                echo '<option value="' . $cat['etype'] . '">' . $cat['etype'] . '</option>';
            }

            echo '</datalist>
    </div>
    </div>
    <div class="form-group my-3 py-1">
        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text">
                    <i class="far fa-clock fa-2x"></i></span>
            </div>
            <input type="text" class="form-control" placeholder="Start Time:" aria-label="Start Time" required
                name="stime" id="dt1">
        </div>
    </div>
    <div class="form-group my-3 py-1">
        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text">
                    <i class="far fa-clock fa-2x"></i></span>
            </div>
            <input type="text" class="form-control" placeholder="End Time:" aria-label="End Time" required name="etime"
                id="dt2">
        </div>
    </div>
    <div class="form-group my-3 py-1">
        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text">
                    <i class="fas fa-info fa-2x" style="padding-right:10px;padding-left:10px;"></i></span>
            </div>
            <input type="text" class="form-control" placeholder="Event Name:" aria-label="Event Name" required
                name="ename"  value="' . $row['ename'] . '" />
        </div>
    </div>
    <div class="form-group my-3 py-1">
        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text">
                    <i class="fas fa-map-marker-alt fa-2x" style="padding-right:4px;padding-left:4px;"></i></span>
            </div>
            <input type="text" autocomplete="false" class="form-control" placeholder="Location:"
                aria-label="Event Location" required name="place" list="places"  value="' . $row['place'] . '" />';
            echo '<datalist id="places">';
            $placelist = DB::query('Select distinct place from events');
            foreach ($placelist as $place) {
                echo '<option value="' . ucfirst($place['place']) . '">' . ucfirst($place['place']) . '</option>';
            }

            echo '</datalist>
    </div>
    </div>
    <input type="submit" class="tbutton mt-3 addb" style="color: #fff" value="Update Event" />
    </form>
    </div>
    <a class="tbutton mx-2 mt-3" style="color: #fff; display: inline-block;" href="edit.php">
        <i class="fas fa-times"></i> Close
    </a>
    </div>
    </div>
    </div>
    </div>

    
    <script type="text/JavaScript">
        $(document).ready(function() {
         $("#myModal").modal("show");
         });
         $(document).ready(function() {
        $("#dt1").focus(function() {
            $(this).attr({
                type: \'datetime-local\'
            });
        });
    });
    $(document).ready(function() {
        $("#dt2").focus(function() {
            $(this).attr({
                type: \'datetime-local\'
            });
        });
    });
      </script>
    ';
        } else {
            header('Location: edit.php');
        }
    }

    $events = DB::query('Select * from events where endtime > CURDATE()');

    if ($events) {

        echo '
    <div class="container">
        <div class="table-responsive table-striped">
            <table class="table ver1">
                <thead>
                    <tr>
                        <th scope="col">Event Name</th>
                        <th scope="col">Event Type</th>
                        <th scope="col">Place</th>
                        <th scope="col">Start time</th>
                        <th scope="col">End time</th>
                        <th scope="col">Edit Event</th>
                        <th scope="col">Remove Event</th>
                    </tr>
                </thead>
                <tbody>
                    ';

        for ($a = 0; $a < count($events); $a++) {
            echo '
        <tr>
        <td>' . $events[$a]['ename'] . '</td>
        <td>' . $events[$a]['etype'] . '</td>
        <td>' . $events[$a]['place'] . '</td>
        <td>' . (new DateTime($events[$a]['starttime']))->format('d/m G:i') . '</td>
        <td>' . (new DateTime($events[$a]['endtime']))->format('d/m G:i') . '</td>
        <td><a href="edit.php?eid=' . $events[$a]['eid'] . '">Edit Event</a></td>
        <td><a href="edit.php?remove=' . $events[$a]['eid'] . '">Remove Event</a></td>
        </tr>
        ';
        }
        echo '
    </tbody>
            </table>
        </div>  
    </div>
    ';
    } else {
        echo '<p class="text-center lead">There is no ongoing or upcoming events to edit</p>';
    } ?>

</body>

</html>