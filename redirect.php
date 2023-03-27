<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require_once 'vendor/autoload.php';
require_once 'config.php';

// Inicializacia Google API klienta
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

// Ak bolo prihlasenie uspesne, Google server nam posle autorizacny kod v URI,
// ktory ziskame pomocou premennej $_GET['code']. Pri neuspesnom prihlaseni tento kod nie je odoslany.
if (isset($_GET['code'])) {
    // Na zaklade autentifikacneho kodu ziskame "access token".
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token['access_token']);

    // Inicializacia triedy OAuth2, pomocou ktorej ziskame informacie pouzivatela na zaklade Scopes.
    $oauth = new Google\Service\Oauth2($client);
    $account_info = $oauth->userinfo->get();

    // Ziskanie dat pouzivatela z Google uctu. Tieto data sa nachadzaju aj v tokene po jeho desifrovani.
    $g_fullname = $account_info->name;
    $g_id = $account_info->id;
    $g_email = $account_info->email;
    $g_name = $account_info->givenName;
    $g_surname = $account_info->familyName;

    // Na tomto mieste je vhodne vytvorit poziadavku na vlastnu DB, ktora urobi:
    // 1. Ak existuje prihlasenie Google uctom -> ziskaj mi minule prihlasenia tohoto pouzivatela.
    // 2. Ak neexistuje prihlasenie pod tymto Google uctom -> vytvor novy zaznam v tabulke prihlaseni.
    $query = "SELECT u.userId, u.name, u.surname, u.userName, u.email FROM users AS u WHERE u.email = :email";
    $statement = $db->prepare($query);
    $statement->bindParam(':email', $g_email, PDO::PARAM_STR);
    $statement->execute();
    $result = $statement->fetch(PDO::FETCH_ASSOC);
    if ($statement->rowCount() == 1) {
        $fullname = $result['name'] . " " . $result['surname'];
        $_SESSION["loggedin"] = true;
        $_SESSION["user"] = array(
            'id' => $result['userId'],
            'name' => $fullname,
            'login' => $result['userName'],
            'email' => $result['email']
        );
        $query = "INSERT INTO user_logins (user_id, user_login, logged_in_at, logged_in_with) VALUES (?,?,CURRENT_TIMESTAMP,?)";
        $statement = $db->prepare($query);
        $statement->execute([$result['userId'], $result['userName'], 'google']);
    } else {
        $hashedPassword = password_hash($token['access_token'], PASSWORD_ARGON2ID);
        $query = "INSERT INTO users (name, surname, userName, email, password, 2fa_secret) VALUES (?,?,?,?,?,?)";
        $statement = $db->prepare($query);
        $statement->execute([$g_name, $g_surname, $g_email, $g_email, $token['access_token'], $hashedPassword]);

        $query = "SELECT u.userId, u.name, u.surname, u.userName, u.email FROM users AS u WHERE u.email = :email";
        $statement = $db->prepare($query);
        $statement->bindParam(':email', $g_email, PDO::PARAM_STR);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        $fullname = $result['name'] . " " . $result['surname'];
        $_SESSION["loggedin"] = true;
        $_SESSION["user"] = array(
            'id' => $result['userId'],
            'name' => $fullname,
            'login' => $result['userName'],
            'email' => $result['email']
        );

        $query = "INSERT INTO user_logins (user_id, user_login, logged_in_at, logged_in_with) VALUES (?,?,CURRENT_TIMESTAMP,?)";
        $statement = $db->prepare($query);
        $statement->execute([$result['userId'], $result['userName'], 'google']);
    }
    // Ulozime potrebne data do session.
}
// Presmerujem pouzivatela na hlavnu stranku alebo kam potrebujem
// aj v pripade, ze zabludil na redirect.php mimo prihlasenia.
header('Location: index.php');
