<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
session_start();
//meno=person priezvisko=person rok=games miesto=games typ=games disciplína=placement
if (isset($_GET['id']) && (int)$_GET['id'] > 0) {
    $id = $_GET['id'];
    $query = "SELECT * FROM person as p WHERE p.id = :id";
    $statement = $db->prepare($query);
    $statement->bindParam(':id', $id, PDO::PARAM_STR);
    $statement->execute();
    $person = $statement->fetch(PDO::FETCH_ASSOC);

    $query = "SELECT g.type, g.year, g.city, g.country, pl.placing, pl.discipline FROM placement as pl JOIN games AS g ON pl.games_id = g.id WHERE pl.person_id = :id";
    $statement = $db->prepare($query);
    $statement->bindParam(':id', $id, PDO::PARAM_STR);
    $statement->execute();
    $victories = $statement->fetchALL(PDO::FETCH_ASSOC);
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="top_best.css" />
    <link rel="stylesheet" href="styles-main.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
    <script src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css" />
    <title>Podrobný prehľad</title>
</head>

<body>
    <header class="bg-dark">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark container-fluid">
            <?php
            if (isset($_SESSION['user']) && $_SESSION['user']['id'] > 0) {
                echo '<a class="navbar-brand" href="#">' . $_SESSION['user']['name'] . '</a>';
            }
            ?>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="best_athletes.php">Naj olympionici</a>
                    </li>
                    <li class="nav-item active">
                        <a class="nav-link" href="privatnazona.php">Info<span class="sr-only">(current)</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="privatnazona.php">Admin panel</a>
                    </li>
                    <?php
                   if (isset($_SESSION['user']) && $_SESSION['user']['id'] > 0) {
                    echo '<li class="nav-item"><a class="nav-link" href="user_history.php">História úprav</a></li>';
                    echo '<li class="nav-item"><a class="nav-link" href="login.php?logout=1">Odhlásiť sa</a></li>';
                } else {
                        echo '<li class="nav-item"><a class="nav-link" href="login.php?login=1">Prihlásiť sa</a></li>';
                    }
                    ?>
                </ul>
            </div>
        </nav>
        <hgroup>
            <h1>Podrobný prehľad</h1>
        </hgroup>
    </header>
    <main>
        <div id="info">
            <h2>Osobné údaje</h2>
            <div id="content">
                <?php
                echo '<label for="firstname">Meno:</label>' .
                    '<input type="text" id="firstname" name="fname" value="' . $person['name'] . '" disabled><br>' .
                    '<label for="lastname">Priezvisko:</label>' .
                    '<input type="text" id="lastname" name="lname" value="' . $person['surname'] . '" disabled><br>' .
                    '<label for="birth_day">Dátum narodenia:</label>' .
                    '<input type="text" id="birth_day" name="birth_day" value="' . $person['birth_day'] . '" disabled><br>' .
                    '<label for="birth_place">Miesto narodenia:</label>' .
                    '<input type="text" id="birth_place" name="birth_place" value="' . $person['birth_place'] . '" disabled><br>' .
                    '<label for="birth_country">Krajina pôvodu:</label>' .
                    '<input type="text" id="birth_country" name="birth_country" value="' . $person['birth_country'] . '" disabled><br>';
                ?>
            </div>
        </div>
        <div id="victories">

            <div class="container justify-content-center">
                <h2>Prehľad víťazstiev</h2>
                <table id="athletes_data" class="table table-striped table-hover mx-auto d-block">
                    <thead>
                        <th scope="col">Typ</th>
                        <th scope="col">Rok</th>
                        <th scope="col">Mesto</th>
                        <th scope="col">Krajina</th>
                        <th scope="col">Umiestnenie</th>
                        <th scope="col">Disciplína</th>
                    </thead>
                    <?php
                    foreach ($victories as $victory) {
                        echo "<tr><td>" . $victory['type'] .
                            "</td><td>" . $victory['year'] .
                            "</td><td>" . $victory['city'] .
                            "</td><td>" . $victory['country'] .
                            "</td><td>" . $victory['placing'] .
                            "</td><td>" . $victory['discipline'] .
                            "</td></tr>";
                    }
                    ?>
                </table>
            </div>
        </div>

    </main>
</body>

</html>
<script>
    $(document).ready(function() {
        $('#athletes_data').DataTable({
            "lengthMenu": [
                [10, 20, -1],
                [10, 20, "All"]
            ],
            responsive: true
        });
    });
</script>