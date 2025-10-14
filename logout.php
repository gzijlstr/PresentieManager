<?php
session_start();

$_SESSION['logout_message'] = "✅ Je bent succesvol uitgelogd.";

$logout_message = $_SESSION['logout_message'];

// deletes the session data
session_unset();
session_destroy();

session_start();
$_SESSION['logout_message'] = $logout_message;

header("Location: loginpage.php");
if (!$message) $message = "✅ Uitgelogd";
exit();
