<?php
session_start();
include 'db.php';
include 'nav.php';
$message = "";

// only runs when the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // ✅ store session data correctly
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role']; // from DB
            $_SESSION['user_id'] = $user['id']; // optional, often useful

            header("Location: main.php");
            exit();
        } else {
            $message = "❌ Ongeldige gebruikersnaam of wachtwoord.";
        }
    } else {
        $message = "❌ Ongeldige gebruikersnaam of wachtwoord.";
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>
<link rel="stylesheet" href="style.css">
<style>.error{color:red;display:none;}</style>
</head>
<body class="main-pagina">
<section id="homepagina">
    <h1>Login</h1><br><br>
    <?php if ($message) echo "<p>$message</p>"; ?>
    <form id="loginform" method="POST">
        Gebruikersnaam: <input id="usernamelog" type="text" name="username" required><br>
        Wachtwoord: <input id="passwordlog" type="password" name="password" required><br>
        <input type="submit" value="Login">    
    </form>
</section>
</body>
</html>
