<?php
// debugging voor php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<?php

// Sessie word gestart en checkt of de gebruiker is ingelogd,
// word anders weer naar de login teruggekeert.
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: loginpage.php"); // send back to login if not logged in
    exit();
}
?>

<?php
include 'db.php';
include 'nav.php';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <!-- SEO Meta -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords" content="Presentie, Aanwezigheid, Overzicht">
    <meta name="Author" content="Gerben Zijlstra">
    <meta name="description" content="Applicatie voor het regelen van groepspresentie">
    <title>Alles over het Museum Project</title>



    <link rel="stylesheet" href="style.css?v=<?php echo time();?>">
    <style>
        html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        }

        body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        }

        .page-wrapper {
        flex: 1;
        }
    </style>
    <!-- pagina word elke 2 seconden herladen voor testing -->
    <!-- <meta http-equiv="refresh" content="2">  auto-refresh every 2 seconds for dev -->
    </head>
<body class="main-pagina">
  <div class="page-wrapper">
    <header id="header"></header>

    <!-- Home section-->
    <section id="homepagina" class="homepagina">            
      <div class="container">
        <div class="intro">
          <img src="img/logo1.png" alt="Museum project">
          <div class="intro-text">
            <h1>Presentie Manager</h1>
            <p>
              Presentie Manager is een webapplicatie waarmee scrum masters en docenten <br> 
              aanwezigheidsgegevens van studenten kunnen beheren. <br>
              De tool biedt overzicht, efficiëntie en gebruiksgemak bij het bijhouden van presentie.
            </p>
          </div>
          <br><br>
          <div class="intro-text" style="height: 70px;">
            <p>
              Merk je een bug of iets dat niet helemaal werkt zoals het hoort?
              Laat het ons weten via het <a href="contact.php">contactformulier</a>!
            </p>
          </div>
        </div>
      </div>
    </section>
  </div>

  <footer id="footer">
    <p>© 2025 Gerben Zijlstra.</p>
  </footer>
</body>
</html>