<?php
// debugging voor php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<?php

session_start();
include 'db.php';
include 'nav.php';

$message = "";

// Toon logout bericht wanneer de gebruiker zich uitgelogd heeft.
if (isset($_SESSION['logout_message'])) {
    $message = $_SESSION['logout_message'];
    unset($_SESSION['logout_message']); // verwijder na tonen
}


// Word alleen gebruikt wanneer er sprake is van ""POST"",
// gegevens word aan de gebruiker gekoppeld.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();


        // gebruiker word naar de main pagina gebracht als de gegevens kloppen.
        if (password_verify($password, $user['password'])) {
            // informatie van de sessie word opgeslagen
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role']; 
            $_SESSION['user_id'] = $user['id']; 

            header("Location: main.php");
            exit();
        } else {
            $message = "Ongeldige gebruikersnaam of wachtwoord.";
        }
    } else {
        $message = "Ongeldige gebruikersnaam of wachtwoord.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
<!-- SEO Meta -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords" content="Presentie, Aanwezigheid, Overzicht">
    <meta name="Author" content="Gerben Zijlstra">
    <meta name="description" content="Applicatie voor het regelen van groepspresentie">
    <title>Login pagina voor het Museum project</title>

<!-- CSS styling  -->
<link rel="stylesheet" href="style.css">
<style>.error{color:red;display:none;}</style>
</head>
<body class="main-pagina">
<!-- Display message -->
    <?php if ($message): ?>
    <div style="background: #cfc; padding:10px; margin-bottom:10px;">
        <?= $message ?>
    </div>
    <?php endif; ?>
    
<section id="homepagina" style="display: flex;">
    <br><br>
    <!-- login form -->
    <div class="login-veld">
        <form id="loginform" method="POST">
            <h1>Login</h1>
            Gebruikersnaam: <input id="usernamelog" type="text" name="username" required><br>
            Wachtwoord: <input id="passwordlog" type="password" name="password" required><br>
            <br>
            <input type="submit" value="Login">    
        </form>
    </div>
</section>
</body>
</html>
