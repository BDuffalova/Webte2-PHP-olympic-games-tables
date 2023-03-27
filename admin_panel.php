<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
session_start();
//meno=person priezvisko=person rok=games miesto=games typ=games disciplína=placement
$displayable = false;
$update = false;
$placement = NULL;

if (isset($_GET['id']) && (int)$_GET['id'] > 0) {
    $id_person = $_GET['id'];
} else {
    $id_person = 0;
}
if (isset($_GET['delete']) && (int)$_GET['delete'] == 1) {
    $query = "DELETE FROM placement as p WHERE p.id = :id";
    $statement = $db->prepare($query);
    $statement->bindParam(':id', $_GET['id_placement'], PDO::PARAM_STR);
    $update = true;
    if (!$statement->execute()) {
        $message = "Zmazanie záznamov sa nepodarilo!";
    } else {
        $message = "Zmazanie záznamov prebehlo úspešne!";
        $query = "INSERT INTO user_actions
        VALUES (NULL,?,?,?,?,CURRENT_TIMESTAMP)";
        $statement = $db->prepare($query);
        $statement->execute([$_SESSION['user']['id'], 'zmazanie', 'umiestnenia', $_GET['id_placement']]);
        header("Location: admin_panel.php?id=" . $id_person);
    }
}

if (isset($_GET['edit']) && (int)$_GET['edit'] == 1) {
    $query = "SELECT * FROM placement as p WHERE p.id = :id";
    $statement = $db->prepare($query);
    $statement->bindParam(':id', $_GET['id_placement'], PDO::PARAM_STR);
    $statement->execute();
    $placement = $statement->fetch(PDO::FETCH_ASSOC);
}

if (!empty($_POST)) {
    if (isset($_POST['save_changes_edit']) && $id_person > 0) {
        $query = "UPDATE person as p
         SET p.name = :name,
           p.surname = :surname, 
           p.birth_day = :birth_day, 
           p.birth_place = :birth_place, 
           p.birth_country = :birth_country, 
           p.death_day = :death_day, 
           p.death_place = :death_place, 
           p.death_country = :death_country 
           WHERE p.id = :id";
        $death_day = $_POST['death_day'] ?: NULL;
        $statement = $db->prepare($query);
        $statement->bindParam(':name', $_POST['name'], PDO::PARAM_STR);
        $statement->bindParam(':surname', $_POST['surname'], PDO::PARAM_STR);
        $statement->bindParam(':birth_day', $_POST['birth_day'], PDO::PARAM_STR);
        $statement->bindParam(':birth_place', $_POST['birth_place'], PDO::PARAM_STR);
        $statement->bindParam(':birth_country', $_POST['birth_country'], PDO::PARAM_STR);
        $statement->bindParam(':death_day', $death_day, PDO::PARAM_STR);
        $statement->bindParam(':death_place', $_POST['death_place'], PDO::PARAM_STR);
        $statement->bindParam(':death_country', $_POST['death_country'], PDO::PARAM_STR);
        $statement->bindParam(':id', $id_person, PDO::PARAM_STR);
        $update = true;
        if (!$statement->execute()) {
            $message = "Editácia sa nepodarila!";
        } else {
            $message = "Editácia prebehla úspešne!";
            $query = "INSERT INTO user_actions
            VALUES (NULL,?,?,?,?,CURRENT_TIMESTAMP)";
            $statement = $db->prepare($query);
            $statement->execute([$_SESSION['user']['id'], 'editacia', 'atleti', $id_person]);
        }
    }

    $query = "SELECT * FROM person as p WHERE p.name = :name AND p.surname = :surname AND p.birth_day = :birth_day";
    $statement = $db->prepare($query);
    $statement->bindParam(':name', $_POST['name'], PDO::PARAM_STR);
    $statement->bindParam(':surname', $_POST['surname'], PDO::PARAM_STR);
    $statement->bindParam(':birth_day', $_POST['birth_day'], PDO::PARAM_STR);
    $statement->execute();
    $edit = $statement->fetch(PDO::FETCH_ASSOC);
    if (isset($_POST['add_person'])) {
        // $query = "SELECT * FROM person as p WHERE p.name = :name AND p.surname = :surname AND p.birth_day = :birth_day";
        // $statement = $db->prepare($query);
        // $statement->bindParam(':name', $_POST['name'], PDO::PARAM_STR);
        // $statement->bindParam(':surname', $_POST['surname'], PDO::PARAM_STR);
        // $statement->bindParam(':birth_day', $_POST['birth_day'], PDO::PARAM_STR);
        // $statement->execute();
        if ($statement->rowCount() == 0) {
            $query = "INSERT INTO person
         VALUES (?,?,?,?,?,?,?,?,?)";
            $death_day = $_POST['death_day'] ?: NULL;
            $death_place = $_POST['death_place'] ?: NULL;
            $death_country = $_POST['death_country'] ?: NULL;
            $statement = $db->prepare($query);
            $update = true;
            if (!$statement->execute([NULL, $_POST['name'], $_POST['surname'], $_POST['birth_day'], $_POST['birth_place'], $_POST['birth_country'], $death_day, $_POST['death_place'], $_POST['death_country']])) {
                $message = "Pridanie sa nepodarilo!";
            } else {
                $message = "Pridanie prebehlo úspešne!";
                $query = "SELECT p.id FROM person as p WHERE p.name = :name AND p.surname = :surname AND p.birth_day = :birth_day";
                $statement = $db->prepare($query);
                $statement->bindParam(':name', $_POST['name'], PDO::PARAM_STR);
                $statement->bindParam(':surname', $_POST['surname'], PDO::PARAM_STR);
                $statement->bindParam(':birth_day', $_POST['birth_day'], PDO::PARAM_STR);
                $statement->execute();
                $edit = $statement->fetch(PDO::FETCH_ASSOC);
                $id_person = $edit['id'];
                $query = "INSERT INTO user_actions
                VALUES (NULL,?,?,?,?,CURRENT_TIMESTAMP)";
                $statement = $db->prepare($query);
                $statement->execute([$_SESSION['user']['id'], 'pridanie', 'atleti', $id_person]);
            }
        } else {
            $update = true;
            $message = "Športovec už existuje!";
        }
        // $edit = $statement->fetch(PDO::FETCH_ASSOC);

    }

    if (isset($_POST['delete_person']) && $statement->rowCount() == 1) {
        $query = "DELETE FROM person as p WHERE p.id = :id";
        $statement = $db->prepare($query);
        $statement->bindParam(':id', $edit['id'], PDO::PARAM_STR);
        $update = true;
        if (!$statement->execute()) {
            $message = "Zmazanie osoby sa nepodarilo!";
        } else {
            $message = "Zmazanie osoby prebehlo úspešne!";
            $query = "INSERT INTO user_actions
            VALUES (NULL,?,?,?,?,CURRENT_TIMESTAMP)";
            $statement = $db->prepare($query);
            $statement->execute([$_SESSION['user']['id'], 'zmazanie', 'atleti', $id_person]);
        }

        $query = "DELETE FROM placement as p WHERE p.person_id = :id";
        $statement = $db->prepare($query);
        $statement->bindParam(':id', $edit['id'], PDO::PARAM_STR);
        $update = true;
        if (!$statement->execute()) {
            $message = "Zmazanie záznamov sa nepodarilo!";
        } else {
            $message = $message . " " . "Zmazanie záznamov prebehlo úspešne!";
            $query = "INSERT INTO user_actions
            VALUES (NULL,?,?,?,?,CURRENT_TIMESTAMP)";
            $statement = $db->prepare($query);
            $statement->execute([$_SESSION['user']['id'], 'zmazanie', 'umiestnenia', $edit['id']]);
        }

        $id_person = 0;
    }

    if (isset($_POST['add_placement']) && $statement->rowCount() == 1) {
        $query = "INSERT INTO placement
            VALUES (?,?,?,?,?)";
        $person_id = $edit['id'];
        $games_id = $_POST['games_id'] ?: NULL;
        $placing = $_POST['placing'] ?: NULL;
        $discipline = $_POST['discipline'] ?: NULL;
        $statement = $db->prepare($query);
        $update = true;
        if (!$statement->execute([NULL, $person_id, $games_id, $placing, $discipline])) {
            $message = "Pridanie umiestnenia sa nepodarilo!";
        } else {
            $message = "Pridanie umiestnenia prebehlo úspešne!";
            $query = "INSERT INTO user_actions
            VALUES (NULL,?,?,?,?,CURRENT_TIMESTAMP)";
            $statement = $db->prepare($query);
            $statement->execute([$_SESSION['user']['id'], 'pridanie', 'umiestnenia', 1]);
        }
    }

    if (isset($_POST['save_changes_placement']) && $placement != NULL && $placement['id'] > 0) {
        $query = "UPDATE placement as pl
                  SET pl.games_id = :games_id, 
                  pl.placing = :placing, 
                  pl.discipline = :discipline
                  WHERE pl.id = :id";
        $statement = $db->prepare($query);
        $placement_id = $_GET['id_placement'];
        $games_id = $_POST['games_id'] ?: NULL;
        $placing = $_POST['placing'] ?: NULL;
        $discipline = $_POST['discipline'] ?: NULL;
        $statement->bindParam(':games_id', $games_id, PDO::PARAM_STR);
        $statement->bindParam(':placing', $placing, PDO::PARAM_STR);
        $statement->bindParam(':discipline', $discipline, PDO::PARAM_STR);
        $statement->bindParam(':id', $placement_id, PDO::PARAM_STR);
        $update = true;
        if (!$statement->execute()) {
            $message = "Editácia umiestnenia sa nepodarila!";
        } else {
            $message = "Editácia umiestnenia prebehlo úspešne!";
            $query = "INSERT INTO user_actions
            VALUES (NULL,?,?,?,?,CURRENT_TIMESTAMP)";
            $statement = $db->prepare($query);
            $statement->execute([$_SESSION['user']['id'], 'editacia', 'umiestnenia', $_GET['id_placement']]);
            header("Location: admin_panel.php?id=" . $id_person);
        }
    }
}

if ($id_person > 0) {
    $query = "SELECT * FROM person as p WHERE p.id = :id";
    $statement = $db->prepare($query);
    $statement->bindParam(':id', $id_person, PDO::PARAM_STR);
    $statement->execute();
    if ($statement->rowCount() == 1) {
        $displayable = true;
        $person = $statement->fetch(PDO::FETCH_ASSOC);
    }
    $query = "SELECT g.type, g.year, g.city, g.country, pl.placing, pl.discipline, pl.id FROM placement as pl JOIN games AS g ON pl.games_id = g.id WHERE pl.person_id = :id";
    $statement = $db->prepare($query);
    $statement->bindParam(':id', $id_person, PDO::PARAM_STR);
    $statement->execute();
    $victories = $statement->fetchALL(PDO::FETCH_ASSOC);
}
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles-main.css" />
    <link rel="stylesheet" href="top_best.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
    <script src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css" />
    <title>Admin panel</title>
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
                    } else {
                        echo '<li class="nav-item"><a class="nav-link" href="login.php?login=1">Prihlásiť sa</a></li>';
                    }
                    ?>
                </ul>
            </div>
        </nav>
        <hgroup>
            <h1>Admin panel</h1>
            <h2>Uprav, pridaj alebo vymaž športovca a jeho úspechy</h2>
        </hgroup>
    </header>
    <div>
        <?php if ($update) {
            echo "<div class=message><p>" . $message . "</p></div>";
        } ?>
        <form action="" method="POST">
            <div>
                <label for="firstname">Meno:</label>
                <input type="text" id="firstname" name="name" value="<?php if ($displayable) {
                                                                            echo $person['name'];
                                                                        } ?>" required><br>
                <label for="surname">Priezvisko:</label>
                <input type="text" id="surname" name="surname" value="<?php if ($displayable) {
                                                                            echo $person['surname'];
                                                                        } ?>" required><br>
                <label for="birth_day">Dátum narodenia:</label>
                <input type="date" id="birth_day" name="birth_day" value="<?php if ($displayable) {
                                                                                echo $person['birth_day'];
                                                                            } ?>" required><br>
                <label for="birth_place">Miesto narodenia:</label>
                <input type="text" id="birth_place" name="birth_place" value="<?php if ($displayable) {
                                                                                    echo $person['birth_place'];
                                                                                } ?>" required><br>
                <label for="birth_country">Krajina pôvodu:</label>
                <input type="text" id="birth_country" name="birth_country" value="<?php if ($displayable) {
                                                                                        echo $person['birth_country'];
                                                                                    } ?>" required><br>
                <label for="death_day">Dátum úmrtia:</label>
                <input type="date" id="death_day" name="death_day" value="<?php if ($displayable && $person['death_day']) {
                                                                                echo $person['death_day'];
                                                                            } ?>"><br>
                <label for="death_place">Miesto úmrtia:</label>
                <input type="text" id="death_place" name="death_place" value="<?php if ($displayable && $person['death_place']) {
                                                                                    echo $person['death_place'];
                                                                                } ?>"><br>
                <label for="death_country">Krajina úmrtia:</label>
                <input type="text" id="death_country" name="death_country" value="<?php if ($displayable && $person['death_country']) {
                                                                                        echo $person['death_country'];
                                                                                    } ?>"><br>
                <button type="submit" name="save_changes_edit">Uložiť zmeny</button>
                <button type="submit" name="add_person">Pridaj športovca</button>
                <button type="submit" name="delete_person">Vymaž športovca</button><br>
            </div>
            <div>
                <label for="games_id">Poradové číslo hier:</label>
                <input type="number" id="games_id" name="games_id" value="<?php if (isset($placement)) {
                                                                                echo $placement['games_id'];
                                                                            } else {
                                                                                echo "1";
                                                                            } ?>" min=1 max=34 required><br>
                <label for="placing">Umiestnenie:</label>
                <input type="number" id="placing" name="placing" value="<?php if (isset($placement)) {
                                                                            echo $placement['placing'];
                                                                        } else {
                                                                            echo "1";
                                                                        } ?>" min=1 required><br>
                <label for="discipline">Disciplína:</label>
                <input type="text" id="discipline" name="discipline" value="<?php if (isset($placement)) {
                                                                                echo $placement['discipline'];
                                                                            } else {
                                                                                echo "NULL";
                                                                            } ?>" required><br>
                <button type="submit" name="add_placement">Pridať umiestnenie</button>
                <button type="submit" name="save_changes_placement">Uložiť zmeny</button>
            </div>
        </form>
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
                        <th scope="col">Akcia</th>
                    </thead>
                    <?php if ($displayable) {
                        foreach ($victories as $victory) {
                            echo "<tr><td>" . $victory['type'] .
                                "</td><td>" . $victory['year'] .
                                "</td><td>" . $victory['city'] .
                                "</td><td>" . $victory['country'] .
                                "</td><td>" . $victory['placing'] .
                                "</td><td>" . $victory['discipline'] .
                                "</td><td>" . '<a href="admin_panel.php?id=' . $id_person . '&id_placement=' . $victory['id'] . '&edit=1">Upravit</a>' . '<br>' .
                                '<a href="admin_panel.php?id=' . $id_person . '&id_placement=' . $victory['id'] . '&delete=1">Vymazat</a>' .
                                "</td>" .
                                "</td></tr>";
                        }
                    }

                    ?>
                </table>
            </div>
        </div>
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