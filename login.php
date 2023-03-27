<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'vendor/autoload.php';
require_once('config.php');
require_once('PHPGangsta/GoogleAuthenticator.php');
//client id: 505569368215-jcldql9i9uq5dslb1etkrm79g2ktv3cb.apps.googleusercontent.com
//client secret: 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
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    session_destroy();
    header("Location: login.php");
}

if (isset($_POST['uname']) && isset($_POST['password'])) {
    $isSuccess = 0;
    $uname = $_POST['uname'];
    $query = "SELECT u.userId, u.userName, u.password, u.email, u.2fa_secret FROM users AS u WHERE u.userName = :uname";
    $statement = $db->prepare($query);
    $statement->bindParam(':uname', $uname, PDO::PARAM_STR);
    $statement->execute();
    $result = $statement->fetch(PDO::FETCH_ASSOC);
    if (!empty($result)) {
        $hashedPassword = $result["password"];
        if (password_verify($_POST["password"], $hashedPassword)) {
            $g2fa = new PHPGangsta_GoogleAuthenticator();
            if ($g2fa->verifyCode($result["2fa_secret"], $_POST['2fa'], 2)) {
                // Heslo aj kod su spravne, pouzivatel autentifikovany.
                $query = "SELECT u.userId, u.name, u.userName, u.email, u.surname FROM users AS u WHERE u.userName = :uname";
                $statement = $db->prepare($query);
                $statement->bindParam(':uname', $uname, PDO::PARAM_STR);
                $statement->execute();
                $result = $statement->fetch(PDO::FETCH_ASSOC);
                $fullname = $result['name'] . " " . $result['surname'];
                $isSuccess = 1;
                // Uloz data pouzivatela do session.
                $_SESSION["loggedin"] = true;
                $_SESSION["user"] = array(
                    'id' => $result['userId'],
                    'name' => $fullname,
                    'login' => $result['userName'],
                    'email' => $result['email']
                );
                $_SESSION["successful"] = true;
                $query = "INSERT INTO user_logins (user_id, user_login, logged_in_at, logged_in_with) VALUES (?,?,CURRENT_TIMESTAMP,?)";
                $statement = $db->prepare($query);
                $statement->execute([$result['userId'], $result['userName'], 'registration']);
                // $_SESSION["created_at"] = $result['created_at'];
            }
        }
    }
    if ($isSuccess == 1) {
        // redirect to index.php
        header("Location: index.php?login=1");
        // exit;
    } else {
        $message = "<div class=message><p>Zlé údaje!</p></div>";
    }
}

$client = new Google\Client();

// Definica konfiguracneho JSON suboru pre autentifikaciu klienta.
// Subor sa stiahne z Google Cloud Console v zalozke Credentials.
$client->setAuthConfig('client_secret.json');

// Nastavenie URI, na ktoru Google server presmeruje poziadavku po uspesnej autentifikacii.
$redirect_uri = "https://site76.webte.fei.stuba.sk/Zadanie1/redirect.php";
$client->setRedirectUri($redirect_uri);

// Definovanie Scopes - rozsah dat, ktore pozadujeme od pouzivatela z jeho Google uctu.
$client->addScope("email");
$client->addScope("profile");

// Vytvorenie URL pre autentifikaciu na Google server - odkaz na Google prihlasenie.
$auth_url = $client->createAuthUrl();


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
                    if (isset($_SESSION['user'])) {
                        echo '<li class="nav-item"><a class="nav-link" href="user_history.php">História úprav</a></li>';
                        echo '<li class="nav-item"><a class="nav-link" href="login.php?logout=1">Odhlásiť sa</a></li>';
                    } else {
                        echo '<li class="nav-item active"><a class="nav-link" href="login.php">Prihlásiť sa<span class="sr-only">(current)</span></a></li>';
                    }
                    ?>
                </ul>
            </div>
        </nav>
        <hgroup>
            <h1>Prihlásenie</h1>
        </hgroup>
    </header>
    <div>
        <?php echo $message ?? ""; ?>
        <form id="login_form" action="login.php" method="POST">
            <div>
                <label for="uname">Login</label>
                <input type="text" id="uname" name="uname" placeholder="userlogin">
            </div>
            <div>
                <label for="password">Heslo</label>
                <input type="password" id="password" name="password">
            </div>
            <div>
                <label for="2fa">2FA kod:</label>
                <input type="number" name="2fa" value="" id="2fa" required>
            </div>
            <br>
            <button type="submit">Prihásiť sa</button>
        </form>
        <div>
            <p>Nemáte účet? </p>
            <a href="signin.php">Vytvorte si ho!</a>
        </div>
        <div>
            <p>Prihlásenie pomocou Google účtu</p>
            <?php echo '<a role="button" href="' . filter_var($auth_url, FILTER_SANITIZE_URL) . '">Google prihlasenie</a>'; ?>
        </div>

    </div>
</body>

</html>