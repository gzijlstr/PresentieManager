<?php
session_start();
// checkt of de gebruiker is ingelogd, 
if (!isset($_SESSION['username'])) {
    header("Location: loginpage.php"); 
    exit();
}
?>
<?php
// database & navigatie bar 
include 'db.php';
include 'nav.php';

$message = '';

// prepared statement voor het verkrijgen van de role van de gebruiker (username), bindt string aan user
$stmt = $conn->prepare("SELECT id, role FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();


// resultaat van de query, role en user_id worden gedefinieerd,
// daarna wordt de statement gesloten
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_id = $user['id'];
$role = $user['role'];
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time();?>">
</head>
<body>
    <!-- Contact  -->
    <section id="contact" class="contact">
            <form action="" id="" method="">  
                <h2>Contact formulier</h2>
                Voor- en achternaam: <input type="text" name=""><br>
                Telefoonnummer: <input type="text" name=""><br>
                E-mail address: <input type="text" name=""><br>
                Uw bericht: <input type="text" style="height: 100px;" name="">
                <input type="submit" value="Versturen">
            </form>
    </section>
</body>
</html>