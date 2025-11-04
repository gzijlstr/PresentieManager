<?php
session_start();

// bericht verbonden met loginpage.php, bericht als de gebruiker is uitgelogd.
$_SESSION['logout_message'] = "Je bent succesvol uitgelogd.";
$logout_message = $_SESSION['logout_message'];

// verwijdert de sessie informatie
session_unset();
session_destroy();

session_start();
$_SESSION['logout_message'] = $logout_message;

// gebruiker word dan weer naar de login gestuurd.
header("Location: loginpage.php");
if (!$message) $message = "Uitgelogd";
exit();
