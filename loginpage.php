
<?php
session_start(); // start session to store login state
include 'db.php';
include 'nav.php';
$message = ""; // incase there is an error

// only runs when the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    

    // reads the form inputs
    $username = $_POST['username'];
    $password = $_POST['password'];

    // prepared statements against sql injections,
    // binds the value "string" to the username
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    // executes query and retrieves result
    $stmt->execute();
    $result = $stmt->get_result();

    // runs, if there is atleast one existing user, and fetches an array
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // if the password and user match with one of the database, the login is succesfull
        if (password_verify($password, $user['password'])) {
            // for this Session, the username gets stored

            // login successful
            $_SESSION['username'] = $user['username'];
            $_SESSION['password'] = $pass['password'];
            $_SESSION['role'] = $role['role'];
            header("Location: main.php"); // redirect to main app
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presentie Manager</title>
    <link rel="stylesheet" href="style.css">
    <!-- pagina word elke 2 seconden herladen voor testing -->
   <!-- <meta http-equiv="refresh" content="2"> <!-- auto-refresh every 2 seconds for dev -->
    <style>
        .error {
            color: red;
            display: none;
        }
    </style>
</head>
<body class="main-pagina">
    <!-- Home section-->
    <section id="homepagina">
        <h1>Login</h1><br><br>
        <?php if ($message) echo "<p>$message</p>"; ?>
        <form id="loginform" method="POST" onsubmit="return validateForm()">
            Gebruikersnaam: <input id="usernamelog" type="text" name="username" required><br>
            Wachtwoord: <input id="passwordlog" type="password" name="password" required><br>
            <input type="submit" value="Login">    
        </form>
    </section>
    <!--script JS-->
    <script>
        // function ValidateForm() {
        //     let x = document.Forms["loginform"]["username"].value;
        //     if (x == '') {
        //         alert("form must be filled out")
        //         return false;
        //     }
        // }
    </script>
</body>
</html>