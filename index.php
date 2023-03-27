<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
session_start();
//meno=person priezvisko=person rok=games miesto=games typ=games disciplína=placement
$query = "SELECT p.id, p.name, p.surname, g.year, g.city, g.type, pl.discipline FROM person AS p JOIN placement AS pl ON p.id = pl.person_id JOIN games AS g ON g.id = pl.games_id";
$stmt = $db->query($query);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <title>Úvod</title>
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
                    <li class="nav-item active">
                        <a class="nav-link" href="index.php">Home <span class="sr-only">(current)</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="best_athletes.php">Naj olympionici</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_panel.php">Admin panel</a>
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
            <h1>Úvodná stránka</h1>
            <h2>Prehľad športovcov a ich víťazstiev</h2>
        </hgroup>
    </header>
    <?php
        if(isset($_GET['login']) && $_GET['login'] == 1){
            echo '<div class=message><p>Úspešné prihlásenie používateľa!</p></div>';
        }
    ?>
    <div class="container justify-content-center">

        <table id="athletes_data" class="table table-striped table-hover mx-auto d-block">
            <thead>
                <th scope="col">Meno</th>
                <th scope="col">Priezvisko</th>
                <th scope="col">Rok</th>
                <th scope="col">Mesto</th>
                <th scope="col">Typ</th>
                <th scope="col">Disciplína</th>
                <?php
                if (isset($_SESSION['user']) && $_SESSION['user']['id'] > 0) {
                    echo '<th scope="col">Úprava</th>';
                }
                ?>
            </thead>
            <?php
            foreach ($results as $result) {
                echo "<tr><td>" . $result['name'] . "</td><td>" . $result['surname'] . "</td><td>" . $result['year'] . "</td><td>" . $result['city'] . "</td><td>" . $result['type'] . "</td><td>" . $result['discipline'] . "</td>";
                if (!isset($_SESSION['user']) || $_SESSION['user'] <= 0) {
                    echo "</tr>";
                } else {
                    echo '<td><a href="admin_panel.php?id=' . $result['id'] . '">Upraviť</a></td></tr>';
                }
            }
            ?>
        </table>
    </div>
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