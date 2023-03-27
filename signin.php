<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once('config.php');
require_once('PHPGangsta/GoogleAuthenticator.php');


if (isset($_POST['uname']) && isset($_POST['password'])) {

    $uname = $_POST['uname'];
    $email = $_POST['email'];
    $userexists = "SELECT userId FROM users WHERE userName = :userName OR email = :email";
    $statement = $db->prepare($userexists);
    $statement->bindParam(':userName', $uname, PDO::PARAM_STR);
    $statement->bindParam(':email', $email, PDO::PARAM_STR);
    $statement->execute();
    if ($statement->rowCount() == 0) {
        $query = "INSERT INTO users (name, surname, userName, email, password, 2fa_secret) VALUES (?,?,?,?,?,?)";
        $statement = $db->prepare($query);
        $hashedPassword = password_hash($_POST['password'], PASSWORD_ARGON2ID);

        $g2fa = new PHPGangsta_GoogleAuthenticator();
        $user_secret = $g2fa->createSecret();
        $codeURL = $g2fa->getQRCodeGoogleUrl('Olympic Games', $user_secret);

        if ($statement->execute([$_POST['name'], $_POST['surname'], $_POST['uname'], $_POST['email'], $hashedPassword, $user_secret])) {
            // qrcode je premenna, ktora sa vykresli vo formulari v HTML.
            $qrcode = $codeURL;
            echo "pridany pouzivatel";
            // header("Location: login.php");
        } else {
            echo "Ups. Nieco sa pokazilo";
        }
    } else {
        echo "pouzivatel existuje";
    }
    // $result = $statement->fetch(PDO::FETCH_ASSOC);






    // var_dump($uname);
    // if (!empty($result)) {
    //     $hashedPassword = $result["password"];
    //     if (password_verify($_POST["password"], $hashedPassword)) {
    //         echo "verified";
    //         $isSuccess = 1;
    //         $_SESSION['user'] = $result['userId'];
    //     }
    // }
    // if ($isSuccess == 1) {
    //     // redirect to index.php
    //     header("Location: index.php");
    //     exit;
    // } else {
    //     $message = "wrong credentials!";
    // }
}
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
                    <li class="nav-item">
                        <a class="nav-link" href="admin_panel.php">Admin panel</a>
                    </li>

                    <?php
                    if (isset($_SESSION['user']) && $_SESSION['user']['id'] > 0) {
                        echo '<li class="nav-item"><a class="nav-link" href="user_history.php">História úprav</a></li>';
                        echo '<li class="nav-item"><a class="nav-link" href="login.php?logout=1">Odhlásiť sa</a></li>';
                    }else {
                        echo '<li class="nav-item active"><a class="nav-link" href="login.php?login=1">Prihlásiť sa<span class="sr-only">(current)</span></a></li>';
                    }
                    ?>
                </ul>
            </div>
        </nav>
        <hgroup>
            <h1>Registrácia</h1>
        </hgroup>
    </header>
    <div class="container justify-content-center">
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <label for="name">Meno</label>
            <input type="text" id="name" name="name" required><br>
            <label for="surname">Priezvisko</label>
            <input type="text" id="surname" name="surname" required><br>
            <label for="email">Email</label>
            <input type="mail" id="email" name="email" required><br>
            <label for="uname">Login</label>
            <input type="text" id="uname" name="uname" placeholder="userlogin" required><br>
            <label for="password">Heslo</label>
            <input type="password" id="password" name="password" required><br>
            <button type="submit">Zaregistrovať</button>

            <?php
            if (isset($qrcode)) {
                // Pokial bol vygenerovany QR kod po uspesnej registracii, zobraz ho.
                $message = '<p>Naskenujte QR kod do aplikacie Authenticator pre 2FA: <br><img src="' . $qrcode . '" alt="qr kod pre aplikaciu authenticator"></p>';

                echo $message;
                echo '<p>Teraz sa mozte prihlasit: <a href="login.php" role="button">Login</a></p>';
            }
            ?>
        </form>

    </div>
</body>

</html>