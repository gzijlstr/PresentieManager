<?php
session_start();
// deletes the session data
session_destroy();
header("Location: loginpage.php");
exit();
