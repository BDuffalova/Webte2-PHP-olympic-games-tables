<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
session_start();
$logins_displayable = false;
$actions_displayable = false;

$id = (int)$_SESSION['user']['id'];
$query = "SELECT * FROM user_logins as u WHERE u.user_id = :id";
$statement = $db->prepare($query);
$statement->bindParam(':id', $id, PDO::PARAM_STR);
$statement->execute();
$logins = $statement->fetchALL(PDO::FETCH_ASSOC);
if ($statement->rowCount() > 0) {
    $logins_displayable = true;
}

$query = "SELECT * FROM user_actions as u WHERE u.user_id = :id";
$statement = $db->prepare($query);
$statement->bindParam(':id', $_SESSION['user']['id'], PDO::PARAM_STR);
$statement->execute();
$actions = $statement->fetchALL(PDO::FETCH_ASSOC);
if ($statement->rowCount() > 0) {
    $actions_displayable = true;
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
    <title>Document</title>
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
                        <a class="nav-link" href="index.php">Home <span class="sr-only">(current)</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="best_athletes.php">Naj olympionici</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="admin_panel.php">Admin panel<span class="sr-only">(current)</span></a>
                    </li>

                    <?php
                    if (isset($_SESSION['user']) && $_SESSION['user']['id'] > 0) {
                        echo '<li class="nav-item"><a class="nav-link" href="user_history.php">História úprav</a></li>';
                        echo '<li class="nav-item"><a class="nav-link" href="login.php?logout=1">Odhlásiť sa</a></li>';
                    }else {
                        echo '<li class="nav-item"><a class="nav-link" href="login.php?login=1">Prihlásiť sa</a></li>';
                    }
                    ?>
                </ul>
            </div>
        </nav>
        <hgroup>
            <h1>História prihlásení a úprav</h1>
        </hgroup>
    </header>
    <div>

        <div class="container justify-content-center">
            <h2>Prehľad víťazstiev</h2>
            <table id="user_logins" class="table table-striped table-hover mx-auto d-block">
                <thead>
                    <th scope="col">Čas prihlásenia</th>
                    <th scope="col">Spôsob prihlásenia</th>
                </thead>
                <?php
                if ($logins_displayable) {
                    foreach ($logins as $login) {
                        echo "<tr><td>" . $login['logged_in_at'] .
                            "</td><td>" . $login['logged_in_with'] .
                            "</td></tr>";
                    }
                }

                ?>
            </table>
        </div>

        <div class="container justify-content-center">
            <h2>Prehľad akcií</h2>
            <table id="user_actions" class="table table-striped table-hover mx-auto d-block">
                <thead>
                    <th scope="col">Akcia</th>
                    <th scope="col">Tabuľka</th>
                    <th scope="col">Id záznamu</th>
                    <th scope="col">Čas úpravy</th>
                </thead>
                <?php
                if ($actions_displayable) {
                    foreach ($actions as $action) {
                        echo "<tr><td>" .  $action['action'] .
                            "</td><td>" . $action['table'] .
                            "</td><td>" . $action['record_id'] .
                            "</td><td>" . $action['created_at'] .
                            "</td></tr>";
                    }
                }

                ?>
            </table>
        </div>
    </div>
</body>

</html>
<script>
    $(document).ready(function() {
        $('#user_logins').DataTable({
            "lengthMenu": [
                [10, 20, -1],
                [10, 20, "All"]
            ],
            responsive: true
        });
    });
    $(document).ready(function() {
        $('#user_actions').DataTable({
            "lengthMenu": [
                [10, 20, -1],
                [10, 20, "All"]
            ],
            responsive: true
        });
    });
</script>