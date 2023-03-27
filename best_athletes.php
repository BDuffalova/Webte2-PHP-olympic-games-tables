<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
session_start();
//meno=person priezvisko=person rok=games miesto=games typ=games disciplína=placement
$query = "SELECT p.id, p.name, p.surname, Count(*) as medaile FROM placement as pl join person as p on pl.person_id = p.id WHERE pl.placing = 1 group by pl.person_id order by medaile desc, surname asc LIMIT 10;";
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
    <title>Njaúspešnejší olympionici</title>
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
                        <a class="nav-link active" href="best_athletes.php">Naj olympionici <span class="sr-only">(current)</span></a>
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
            <h1>Najlepší športovci</h1>
        </hgroup>
    </header>
    <div class="container">

        <table id="top-ten" class="table table-striped table-hover">
            <thead>
                <th scope="col">Celé meno</th>
                <th scope="col">Počet zlatých medailí</th>
            </thead>
            <?php
            foreach ($results as $result) {
                echo '<tr><td><a href="info.php?id=' . $result['id'] . '">' . $result['name'] . ' ' . $result['surname'] . "</a></td><td>" . $result['medaile'] . "</td></tr>";
            }
            ?>
        </table>
    </div>
</body>

</html>
<script>
    $(document).ready(function() {
        $('#top-ten').DataTable({
        "paging": false,
        "lengthChange": false,
        "info": false,
        responsive: true
    });
    })
</script>