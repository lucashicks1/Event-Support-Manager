<?php
session_start();
if (!$_SESSION['fname']) {
    header('Location: login.php');
} elseif ($_SESSION['utype'] != 's') {
    header('Location: index.php');
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Terrace Support - Attendances</title>
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
                        <a class="nav-link" href="events.php">Events</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="houses.php">Houses</a>
                    </li>
                    <li class="nav-item active">
                        <a class="nav-link" href="Attendances.php">Attendances <span
                                class="sr-only">(current)</span></a>
                    </li>
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

    <div class="container">
        <h1 class="text-center heading my-4">Attendances</h1>
    </div>

    <?php

    // Connect to database
    require('connect.php');
    // Find events
    $events = DB::query(
        '
      select *
      from userevents u, events e
      where u.time IS NOT NULL
      AND u.uid = %s
      AND e.eid = u.eid',
        $_SESSION['uid']
    );

    echo '<p class="lead my-2 text-center">Number of attendances: ' . count($events) . '</p>';

    // If there are events available
    if ($events) {
        echo '
   <form action="events.php" method="post" id="alerts"></form>
   <div class="container">
      <div class="row h-100 justify-content-center align-items-center">
   ';
        //    Iterate through events
        for ($i = 0; $i < count($events); $i++) {
            $event = $events[$i];
            // Format dates
            $date = (new DateTime($event['starttime']));
            $edate = (new DateTime($event['endtime']));
            $time = ((new DateTime($event['time']))->format('d/m h:i a'));

            echo '
      <div class="col-sm-12 col-md-6 col-lg-4 my-4">
         <div class="card mx-auto" style="width: 18rem;">
            <div class="card-body">
               <h6 class="card-subtitle mb-2 text-muted">' . $date->format('d/m h:i a') . ' - ' . $edate->format('h:i a') . '</h6>
               <h5 class="card-title">' . ucfirst($event['etype']) . '</h5>
               <p class="card-text card-text-name">' . $event['ename'] . '</p>
               <h6 class="card-subtitle mb-4">Attended: ' . $time . '</h6>
            </div>
         </div>
      </div>
      ';
        }
    }
    ?>


</body>

</html>