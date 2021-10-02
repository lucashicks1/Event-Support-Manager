<?php
session_start();
if (!$_SESSION['fname']) {
    header('Location: login.php');
}
require('connect.php');

if ((isset($_GET['h'])) && ($_SESSION['utype'] != 's')) {
    $h = $_GET['h'];
    $test = DB::query('Select * from userbase where house = %s', $h);
    if ($test) {
        $num = DB::queryFirstField('
    Select COUNT(*) from userevents as e, users as u
    where house = %s AND time IS NOT NULL AND e.uid = u.uid
    
    ', $h);

        $tutg = DB::query(
            '
      select u.tutg, count(*) as c
      from users u, userevents e
      where time IS NOT NULL
      AND e.uid = u.uid
      AND u.house = %s
      GROUP BY tutg
      ORDER BY tutg',
            $h
        );

        $years = DB::query(
            '
   select u.yr, count(*) as c
   from users u, userevents e
   where time IS NOT NULL
   AND e.uid = u.uid
   AND u.house = %s
   GROUP BY yr
   ORDER BY yr',
            $h
        );

        echo '
      <div class="modal" tabindex="-1" role="dialog" id="myModal">
         <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content text-center">
               <div class="modal-header text-center">
                  <div class="col-12">
                     <h3 class="text-center modal-title col-12 heading">House Summary</h3>
                     <h6 class="text-center modal-title my-0">' . $h . ' House</h6>
                     <h6 class="text-center modal-title my-0" style="font-weight:600;">Number Attended: ' . $num . '</h6>
                  </div>
               </div>
               <div class="modal-body">';
        if ($num != 0) {
            echo '
                  <div class="col-12 text-center">
                     <h4 class="modal-title my-0" style="font-weight:600;">Year Levels</h4>
                     <div class="container">
                        <div class="row align-items-center mx-auto">';
            for ($j = 0; $j < count($years); $j++) {
                echo '
                           <div class="col-sm-6 col-md-4 mx-auto">
                              <p>Year ' . $years[$j]['yr'] . ': ' . $years[$j]['c'] . '</p>
                           </div>
                           ';
            }
            echo '
                        </div>
                     </div>
                  </div>
                  <div class="col-12 text-center">
                     <h4 class="modal-title my-0" style="font-weight:600;">Tutor Groups</h4>
                     <div class="container">
                     <div class="row align-items-center mx-auto">';
            for ($k = 0; $k < count($tutg); $k++) {
                echo '
                        <div class="col-sm-6 col-md-4 mx-auto">
                           <p>' . $h . ' ' . substr($tutg[$k]['tutg'], 1) . ': ' . $tutg[$k]['c'] . '</p>
                        </div>
                        ';
            }
            echo '
                     </div>
                     </div>
                  </div>';
        } else {
            echo '<h6 class="text-center modal-title my-0">No one from ' . $h . ' house has attended</h6>';
        }
        echo '
                <a class="tbutton mx-2 mt-3" style="color:#fff;display:inline-block;" href="sview.php?h=' . $h . '">
                     <i class="fas fa-ellipsis-h"></i>
                     View Students
                  </a>
                  <br/>
                  <a class="tbutton mx-2 mt-3" style="color:#fff;display:inline-block;" href="houses.php">
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
        header('Location: houses.php');
    }
} elseif (isset($_GET['h'])) {
    header('Location: houses.php');
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Terrace Support - Houses</title>
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
                    <li class="nav-item active">
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
    <h1 class="text-center heading my-4">House Leaderboards</h1>

    <?php

    require('connect.php');

    $houses = DB::query('
    Select distinct year(time) y, house, count(*) as c
    from userevents e, users u
    where 
    time IS NOT NULL 
    AND
    u.uid = e.uid
    AND
    YEAR(CURDATE()) = year(time)
    GROUP BY u.house
    ORDER BY c DESC
    
    ');


    if ($houses) {
        if ($_SESSION['utype'] == 's') {
            echo '
            <div class="container">
            <div class="table-responsive table-striped">
                <table class="table ver1">
                    <thead>
                        <tr>
                            <th scope="col">Ranking</th>
                            <th scope="col">House</th>
                            <th scope="col">Points</th>
                        </tr>
                    </thead>
                    <tbody>
            ';
            $rank = 1;
            $prev_rank = $rank;
            for ($j = 0; $j < count($houses); $j++) {
                echo '<tr>';
                if ($j == 0) {
                    echo '<td>' . $rank . '</td>';
                } elseif ($houses[$j]['c'] != $houses[$j - 1]['c']) {
                    $rank++;
                    $prev_rank = $rank;
                    echo '<td>' . $rank . '</td>';
                } else {
                    $rank;
                    echo '<td>' . $prev_rank . '</td>';
                }
                echo '
                
                <td>' . $houses[$j]['house'] . '</td>
                <td>' . $houses[$j]['c'] . '</td>
                </tr>
                ';
            }
            // Iteration

            echo '
            </tbody>
                </table>
            </div>
        </div>';
        } else {
            echo '
            <div class="container">
            <div class="table-responsive table-striped">
                <table class="table ver1">
                    <thead>
                        <tr>
                            <th scope="col">Ranking</th>
                            <th scope="col">House</th>
                            <th scope="col">Points</th>
                            <th scope="col">Summary</th>
                        </tr>
                    </thead>
                    <tbody>
            ';
            $rank = 1;
            $prev_rank = $rank;
            for ($j = 0; $j < count($houses); $j++) {
                echo '<tr>';
                if ($j == 0) {
                    echo '<td>' . $rank . '</td>';
                } elseif ($houses[$j]['c'] != $houses[$j - 1]['c']) {
                    $rank++;
                    $prev_rank = $rank;
                    echo '<td>' . $rank . '</td>';
                } else {
                    $rank;
                    echo '<td>' . $prev_rank . '</td>';
                }
                echo '
                
                <td>' . $houses[$j]['house'] . '</td>
                <td>' . $houses[$j]['c'] . '</td>
                <td><a href="houses.php?h=' . $houses[$j]['house'] . '">Click Here</a></td>
                </tr>
                ';
            }
            // Iteration

            echo '
            </tbody>
                </table>
            </div>
        </div>';
        }
    } else {
        echo '<p class="leading my-2 tet-center">There are currently no attendances</p>';
    }


    if (isset($_GET['h'])) {
        echo '
      <script type="text/JavaScript">
        $(document).ready(function() { $("#myModal").modal("show"); });
    </script>
      ';
    }
    ?>

</body>

</html>