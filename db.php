 <?php
// mysql database.
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "presentie";

// database connectie string.
$conn = new mysqli($servername, $username, $password, $dbname);

// Checkt of de connectie met de database is gelukt.
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

?> 