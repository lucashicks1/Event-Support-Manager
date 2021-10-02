<?php
session_start();
if (!$_SESSION['fname']) {
   header('Location: login.php');
}

require('connect.php');
date_default_timezone_set('Australia/Brisbane');

if (isset($_POST['uid'])) {
   $uid = $_POST['uid'];
   $eid = $_GET['add'];
   $usert = DB::queryFirstField('Select uid from users where uid = %s AND utype = "s"', $uid);
   // If the user exists
   if ($usert) {
      $check = DB::queryFirstrow('Select eid from events where starttime < CURDATE() AND endtime > CURDATE() AND eid = %s', $eid);
      if (!$check) {
         // Checked if they have attended the event
         $esearch = DB::queryFirstField('Select eid from userevents where uid = %s AND eid = %s AND time IS NOT NULL', $uid, $eid);
         if (!$esearch) {
            // If they haven't attended the event
            $levent = DB::queryfirstrow('
         Select endtime, place from userevents u, events e where uid = %s ORDER BY time DESC', $uid);
            // If they have attended the event (possibility for place or time clashes)
            if ($levent) {
               $place = DB::queryfirstfield('Select place from events where eid = %s', $eid);
               $datea = (new DateTime($levent['endtime']));
               $current = (new Datetime());
               $placec = $levent['place'];
               // If the current date is ahead of the finishing date of the last attended event
               // OR they are in the same place all good
               if (($datea < $current) || ($place == $placec)) {
                  // UPDATE USEREVENT ENTRY
                  $a = DB::queryFirstField('Select * from userevents where uid = %s AND eid = %s', $uid, $eid);
                  // If the user has had an entry in the userevents table for just alerts - means that you update rather than inserting
                  if ($a) {
                     DB::update('userevents', ['time' => $current], "eid = %s AND uid = %s", $eid, $uid);
                  } else {
                     DB::insert('userevents', array(
                        'uid' => $uid,
                        'eid' => $eid,
                        'time' => $current,
                        'alerts' => 0,
                        'sent' => 0
                     ));
                  }
                  $status = 0;
               } else {
                  // CANT ATTEND TWO DIFFERENT EVENTS AT THE SAME TIME THAT ARE NOT AT THE SAME PLACE
                  $status = 1;
               }
            } else {
               $current = (new Datetime());
               DB::insert('userevents', array(
                  'uid' => $uid,
                  'eid' => $eid,
                  'time' => $current,
                  'alerts' => 0,
                  'sent' => 0
               ));
               $status = 0;
            }
         } else {
            // Already attending the event
            $status = 2;
         }
      } else {
         // Event hasn't started yet / already over
         $status = 4;
      }
   } else {
      // Not a real snumber
      $status = 3;
   }
} elseif (isset($_POST['uidr'])) {
   $uid = $_POST['uidr'];
   $eid = $_GET['remove'];
   $usert = DB::query('Select * from users where uid = %s AND utype = "s"', $uid);
   if ($usert) {
      $esearch = DB::queryFirstRow('Select * from userevents where uid = %s AND eid = %s AND time IS NOT NULL', $uid, $eid);
      if ($esearch) {
         DB::delete('userevents', 'uid=%s AND eid=%s', $uid, $eid);
         $status = 0;
      } else {
         $status = 1;
      }
   } else {
      $status = 2;
   }
}

if ((isset($_GET['add'])) && ($_SESSION['utype'] != 's')) {
   $eid = $_GET['add'];
   $results = DB::query('Select * from events where eid = %s', $eid);
   if ($results) {
      echo '
      <div class="modal" tabindex="-1" role="dialog" id="myModal">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content text-center">
                <div class="modal-header text-center">
                    <div class="col-12">
                        <h3 class="text-center modal-title col-12 heading">
                            Add Student
                        </h3>';
      if (isset($status)) {
         if ($status == 0) {
            echo '
            <div class="row">
               <div class="mx-auto col-10 col-sm-10 col-md-10 col-lg-8 text-center">
                  <div class="alert" role="alert">
                        Student is now attending
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                           <span aria-hidden="true">&times;</span>
                        </button>
                     </div>
                  </div> 
               </div>
            ';
         } elseif ($status == 1) {
            echo '
            <div class="row">
               <div class="mx-auto col-10 col-sm-10 col-md-10 col-lg-8 text-center">
                  <div class="alert" role="alert">
                        Student is already attending another event
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                           <span aria-hidden="true">&times;</span>
                        </button>
                     </div>
                  </div> 
               </div>
            ';
         } elseif ($status == 2) {
            echo '
            <div class="row">
               <div class="mx-auto col-10 col-sm-10 col-md-10 col-lg-8 text-center">
                  <div class="alert" role="alert">
                        Student is already attending this event
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                           <span aria-hidden="true">&times;</span>
                        </button>
                     </div>
                  </div> 
               </div>
            ';
         } elseif ($status == 3) {
            echo '
            <div class="row">
               <div class="mx-auto col-10 col-sm-10 col-md-10 col-lg-8 text-center">
                  <div class="alert" role="alert">
                        Student Number not registered to support program
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                           <span aria-hidden="true">&times;</span>
                        </button>
                     </div>
                  </div> 
               </div>
            ';
         } elseif ($status == 4) {
            echo '
            <div class="row">
               <div class="mx-auto col-10 col-sm-10 col-md-10 col-lg-8 text-center">
                  <div class="alert" role="alert">
                        Event hasn\'t started / already finished
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
                        <form method="post" action="events.php?add=' . $eid . '" name="add">
                            <div class="form-group my-3 py-1">
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                        <i class="fas fa-user-plus fa-2x red"></i
                        ></span>
                                    </div>
                                    <input type="text" class="form-control" placeholder="Student Number:" aria-label="Student Number" required name="uid" autofocus />
                                </div>
                            </div>
                            <input type="submit" class="tbutton mt-3 addb" style="color: #fff" value="Add Student"/>
                            </form>
                        </div>
                    <a class="tbutton mx-2 mt-3" style="color: #fff; display: inline-block;" href="events.php">
                        <i class="fas fa-times"></i> Close
                    </a>
                </div>
            </div>
        </div>
    </div>
      ';
   } else {
      header('Location: events.php');
   }
} elseif (isset($_GET['add'])) {
   header('Location:events.php');
} elseif ((isset($_GET['remove'])) && ($_SESSION['utype'] != 's')) {
   $eid = $_GET['remove'];
   $results = DB::query('Select * from events where eid = %s', $eid);
   if ($results) {
      echo '
      <div class="modal" tabindex="-1" role="dialog" id="myModal">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content text-center">
                <div class="modal-header text-center">
                    <div class="col-12">
                        <h3 class="text-center modal-title col-12 heading">
                            Remove Student
                        </h3>';
      if (isset($status)) {
         if ($status == 0) {
            echo '
            <div class="row">
               <div class="mx-auto col-10 col-sm-10 col-md-10 col-lg-8 text-center">
                  <div class="alert" role="alert">
                        Student has been removed
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                           <span aria-hidden="true">&times;</span>
                        </button>
                     </div>
                  </div> 
               </div>
            ';
         } elseif ($status == 1) {
            echo '
            <div class="row">
               <div class="mx-auto col-10 col-sm-10 col-md-10 col-lg-8 text-center">
                  <div class="alert" role="alert">
                        Student hasn\'t attended this event
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
                        Incorrect Student Number
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                           <span aria-hidden="true">&times;</span>
                        </button>
                     </div>
                  </div> 
               </div>
            ';
         }
      }
      echo '   
            </div>
               </div>
                <div class="modal-body">
                    <div class="col-lg-5 col-8 text-center mx-auto">
                        <form method="post" action="events.php?remove=' . $eid . '" name="remove">
                            <div class="form-group my-3 py-1">
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                        <i class="fas fa-user-plus fa-2x red"></i
                        ></span>
                                    </div>
                                    <input type="text" class="form-control" placeholder="Student Number:" aria-label="Student Number" required name="uidr" autofocus />
                                </div>
                            </div>
                            <input type="submit" class="tbutton mt-3 addb" style="color: #fff" value="Remove Student"/>
                            </form>
                        </div>
                    <a class="tbutton mx-2 mt-3" style="color: #fff; display: inline-block;" href="events.php">
                        <i class="fas fa-times"></i> Close
                    </a>
                </div>
            </div>
        </div>
    </div>
      ';
   } else {
      header('Location: events.php');
   }
} elseif (isset($_GET['remove'])) {
   header('Location:events.php');
}



if (isset($_POST['alertsb'])) {
   if ($_SESSION['utype'] == 's') {
      require('connect.php');
      $eid = $_POST['alertsb'];
      $search = DB::queryfirstrow('select * from userevents where eid = %s AND uid = %s', $eid, $_SESSION['uid']);
      if (!$search) {
         DB::insert('userevents', ['uid' => $_SESSION['uid'], 'eid' => $eid, 'alerts' => 1, 'sent' => 0]);
      } else {
         if ($search['alerts'] == 1) {
            DB::update('userevents', ['alerts' => 0], "uid=%s AND eid=%s", $_SESSION['uid'], $eid);
         } else {
            DB::update('userevents', ['alerts' => 1], "uid=%s AND eid=%s", $_SESSION['uid'], $eid);
         }
      }
   } else {
      header('Location: events.php');
   }
}
if (isset($_GET['sum'])) {
   if (!$_SESSION['utype'] != 's') {
      require('connect.php');
      // GETS THE EVENT NAME
      $eid = $_GET['sum'];
      $name = DB::queryFirstField('Select ename from events where eid = %s', $eid);
      // echo $name;

      // FINDS THE TOTAL NUMBER ATTENDED
      $num = DB::queryFirstField('Select COUNT(*) from userevents where eid = %s AND time IS NOT NULL', $eid);
      // echo 'Number Attended:  '.$num;

      // HOUSES
      $houses = DB::query(
         '
      select u.house, count(*) as c
      from users u, userevents e
      where time IS NOT NULL
      AND e.eid = %s
      AND e.uid = u.uid
      GROUP BY house
      ORDER BY house',
         $eid
      );

      $years = DB::query(
         '
   select u.yr, count(*) as c
   from users u, userevents e
   where time IS NOT NULL
   AND e.eid = %s
   AND e.uid = u.uid
   GROUP BY yr
   ORDER BY yr',
         $eid
      );
      echo '
      <div class="modal" tabindex="-1" role="dialog" id="myModal">
         <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content text-center">
               <div class="modal-header text-center">
                  <div class="col-12">
                     <h3 class="text-center modal-title col-12 heading">Event Summary</h3>
                     <h6 class="text-center modal-title my-0">' . $name . '</h6>
                     <h6 class="text-center modal-title my-0" style="font-weight:600;">Number Attended: ' . $num . '</h6>
                  </div>
               </div>
               <div class="modal-body">';
      if ($num != 0) {
         echo '
                  <div class="col-12 text-center">
                     <h4 class="modal-title my-0" style="font-weight:600;">Houses</h4>
                     <div class="container">
                        <div class="row align-items-center mx-auto">';
         for ($j = 0; $j < count($houses); $j++) {
            echo '
                           <div class="col-sm-6 col-md-4 mx-auto">
                              <p>' . ucfirst($houses[$j]['house']) . ': ' . $houses[$j]['c'] . '</p>
                           </div>
                           ';
         }
         echo '
                        </div>
                     </div>
                  </div>
                  <div class="col-12 text-center">
                     <h4 class="modal-title my-0" style="font-weight:600;">Year Levels</h4>
                     <div class="container">
                     <div class="row align-items-center mx-auto">';
         for ($k = 0; $k < count($years); $k++) {
            echo '
                        <div class="col-sm-6 col-md-4 mx-auto">
                           <p>Year ' . $years[$k]['yr'] . ': ' . $years[$k]['c'] . '</p>
                        </div>
                        ';
         }
         echo '
                     </div>
                     </div>
                  </div>';
      } else {
         echo '<h6 class="text-center modal-title my-0">There are no attendances for this event at this time.</h6>';
      }
      echo '
                  <a class="tbutton mx-2 mt-3" style="color:#fff;display:inline-block;" href="events.php">
                     <i class="fas fa-times"></i>
                     Close
                  </a>
               </div>
            </div>
         </div>
      </div>
      </div>

';
   } else {
      header('Location: events.php');
   }
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
    <title>Terrace Support - Events</title>
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
                    <li class="nav-item active">
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

    <div class="sport banner"></div>


    <?php
   require('connect.php');

   $events = DB::query('Select * from events where endtime > now() AND starttime < now()');

   if ($events) {
      echo '
   <form action="events.php" method="post" id="alerts"></form>
   <div class="container">
   <h1 class="text-center heading my-4">Ongoing Events</h1>
      <div class="row justify-content-center align-items-center">
   ';
      for ($i = 0; $i < count($events); $i++) {
         $event = $events[$i];
         $date = (new DateTime($event['starttime']));
         $edate = (new DateTime($event['endtime']));

         echo '
      <div class="col-sm-12 col-md-6 col-lg-4 my-4">
         <div class="card mx-auto" style="width: 18rem;">
            <div class="card-body">
               <h6 class="card-subtitle mb-2 text-muted">' . $date->format('d/m G:i') . ' - ' . $edate->format('d/m G:i') . '</h6>
               <h5 class="card-title">' . ucfirst($event['etype']) . '</h5>
               <p class="card-text card-text-name">' . $event['ename'] . '</p>
               <p class="card-text card-text-name">' . $event['place'] . '</p>
               <h6 class="card-subtitle mb-4">Ongoing</h6>
               <div class="text-center">';
         if ($_SESSION['utype'] != 's') {
            echo '
                  <div align="center" class="mb-4 my-3">
                     <button type="button" class="tbutton mx-2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"  style="color:#fff;display:inline-block;">
                     <i class="fas fa-user-edit"></i>
                     Edit
                     </button>
                     <div class="dropdown-menu">
                      <a class="dropdown-item" href="events.php?add=' . $event['eid'] . '">Add Student</a>
                      <a class="dropdown-item" href="events.php?remove=' . $event['eid'] . '">Remove Student</a>
                    </div>
                     <a class="tbutton mx-2 mt-3" style="color:#fff;display:inline-block;" href="events.php?sum=' . $event['eid'] . '">
                        <i class="fas fa-ellipsis-h fa-lg"></i>
                        Summary
                     </a>
                  </div>
                  ';
         }
         echo '
               </div>
            </div>
         </div>
      </div>
      ';
      }
   }

   $uevents = DB::query('Select * from events where endtime > now() AND starttime > now()');

   if ($uevents) {
      echo '
   <form action="events.php" method="post" id="alerts"></form>
   <div class="container">
   <h1 class="text-center heading my-4">Upcoming Events</h1>
      <div class="row justify-content-center align-items-center">
   ';
      for ($i = 0; $i < count($uevents); $i++) {
         $event = $uevents[$i];
         $date = (new DateTime($event['starttime']));
         $edate = (new DateTime($event['endtime']));



         echo '
      <div class="col-sm-12 col-md-6 col-lg-4 my-4">
         <div class="card mx-auto" style="width: 18rem;">
            <div class="card-body">
               <h6 class="card-subtitle mb-2 text-muted">' . $date->format('d/m G:i') . ' - ' . $edate->format('d/m G:i') . '</h6>
               <h5 class="card-title">' . ucfirst($event['etype']) . '</h5>
               <p class="card-text card-text-name">' . $event['ename'] . '</p>
               <p class="card-text card-text-name">' . $event['place'] . '</p>
               <h6 class="card-subtitle mb-4">Upcoming</h6>
               <div class="text-center">';
         if ($_SESSION['utype'] == 's') {
            $options = DB::queryFirstField('Select alerts from userevents where uid = %s AND eid = %s', $_SESSION['uid'], $event['eid']);
            if ($options) {
               if ($options == 1) {
                  echo '
                        <button type="submit" class="tbutton mx-2" style="color:#fff;" form="alerts" name="alertsb" value="' . $event['eid'] . '"><i class="fas fa-bell fa-lg"></i>
                        Alerts On</button>
                        ';
               } else {
                  echo '
                        <button type="submit" class="tbutton mx-2" style="color:#fff;" form="alerts" name="alertsb" value="' . $event['eid'] . '"><i class="far fa-bell fa-lg"></i>
                        Alerts Off</button>
                        ';
               }
            } else {
               echo '
                     <button type="submit" class="tbutton mx-2" style="color:#fff;" form="alerts" name="alertsb" value="' . $event['eid'] . '"><i class="far fa-bell fa-lg"></i>
                     Alerts Off</button>
                     ';
            }
         }
         echo '
               </div>
            </div>
         </div>
      </div>
      ';
      }
   }

   $pevents = DB::query('Select * from events where endtime < now()');

   if (($pevents) && ($_SESSION['utype'] != 's')) {
      echo '
   <form action="events.php" method="post" id="alerts"></form>
   <div class="container">
   <h1 class="text-center heading my-4">Past Events</h1>
      <div class="row justify-content-center align-items-center">
   ';
      for ($i = 0; $i < count($pevents); $i++) {
         $event = $pevents[$i];
         $date = (new DateTime($event['starttime']));
         $edate = (new DateTime($event['endtime']));

         echo '
      <div class="col-sm-12 col-md-6 col-lg-4 my-4">
         <div class="card mx-auto" style="width: 18rem;">
            <div class="card-body">
               <h6 class="card-subtitle mb-2 text-muted">' . $date->format('d/m G:i') . ' - ' . $edate->format('d/m G:i') . '</h6>
               <h5 class="card-title">' . ucfirst($event['etype']) . '</h5>
               <p class="card-text card-text-name">' . $event['ename'] . '</p>
               <p class="card-text">' . $event['place'] . '</p>
               <h6 class="card-subtitle mb-4">Finished</h6>
               <div class="text-center">';
         if ($_SESSION['utype'] != 's') {
            echo '
                  <div align="center" class="mb-4 my-3">
                     <a class="tbutton mx-2 mt-3" style="color:#fff;display:inline-block;" href="events.php?sum=' . $event['eid'] . '">
                        <i class="fas fa-ellipsis-h fa-lg"></i>
                        Summary
                     </a>
                  </div>
                  ';
         }
         echo '
               </div>
            </div>
         </div>
      </div>
      ';
      }
   }

   if ((!$pevents) && (!$uevents) && (!$pevents)) {
      echo '
      <div class="container">
   <h1 class="text-center heading my-4">Upcoming Events</h1>';
   }



   if (isset($_GET['sum'])) {
      if (!$_SESSION['utype'] != 's') {
         echo '
      <script type="text/JavaScript">
         $(document).ready(function() {
         $("#myModal").modal("show");
         });
      </script>
      ';
      }
   } elseif (isset($_GET['add'])) {
      echo '
      <script type="text/JavaScript">
        $(document).ready(function() { $("#myModal").modal("show"); }); $(".modal").on("shown.bs.modal", function() { $(this).find("[autofocus]").focus(); });
    </script>
      ';
   } elseif (isset($_GET['remove'])) {
      echo '
      <script type="text/JavaScript">
        $(document).ready(function() { $("#myModal").modal("show"); }); $(".modal").on("shown.bs.modal", function() { $(this).find("[autofocus]").focus(); });
    </script>
      ';
   }
   ?>

</body>

</html>