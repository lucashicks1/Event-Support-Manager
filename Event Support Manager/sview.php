<?php
session_start();
if (!$_SESSION['fname']) {
    header('Location: login.php');
}

require('connect.php');

if (($_SESSION['utype'] == 's') || (!isset($_GET['h']))) {
    header('Location: houses.php');
} else {
    $test = DB::queryFirstRow('Select * from users where house = %s', $_GET['h']);
    if (!$test) {
        header('Location: houses.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Terrace Support - Students</title>
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

    <h1 class="text-center heading my-4">Student Summary List</h1>

    <?php
    $h = $_GET['h'];
    $people = DB::query(
        'Select DISTINCT u.uid, tutg, fname, lname,count(*) as c
        from userevents e, users u
        where e.uid = u.uid
        AND house = %s
        GROUP BY u.uid
        ORDER BY c DESC
        ',
        $h
    );
    if ($people) {
        echo '
        <div class="container">
        <div class="table-responsive table-striped">
            <table class="table ver1">
                <thead>
                    <tr>
                        <th scope="col">Ranking</th>
                        <th scope="col">Student Number</th>
                        <th scope="col">Name</th>
                        <th scope="col">Tutor Group</th>
                        <th scope="col">Points</th>
                    </tr>
                </thead>
                <tbody>
        ';
        $rank = 1;
        $prev_rank = $rank;
        for ($i = 0; $i < count($people); $i++) {
            echo '<tr>';
            if ($i == 0) {
                echo '<td>' . $rank . '</td>';
            } elseif ($people[$i]['c'] != $people[$i - 1]['c']) {
                $rank++;
                $prev_rank = $rank;
                echo '<td>' . $rank . '</td>';
            } else {
                $rank;
                echo '<td>' . $prev_rank . '</td>';
            }
            echo '
                        <td>' . $people[$i]['uid'] . '</td>
                        <td>' . ucfirst($people[$i]['fname']) . ' ' . ucfirst($people[$i]['lname']) . '</td>
                        <td>' . $h . ' ' . ucfirst(substr($people[$i]['tutg'], 1)) . '</td>
                        <td>' . $people[$i]['c'] . '</td>
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
        echo '<p class="text-center lead">No attendances for this house yet</p>';
    }


    ?>

</body>

</html>