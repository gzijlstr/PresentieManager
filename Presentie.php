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


// Form en table *student/studenten*
// Update de aanwezigheid table met de volgende value van de form:
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($_POST['form_type'] === "student") {
        // Checkt of de variable geen NULL is en value TRUE of FALSE terug
        $naam  = $_POST['naam'];

        // checkt de gebruiker (user_id) al een groep heeft
        $stmt = $conn->prepare("SELECT id FROM groepen WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $groep = $result->fetch_assoc();
        $stmt->close();

        if ($groep) {
            $groep_id = $groep['id'];

            $stmt = $conn->prepare("INSERT INTO studenten (naam, groep_id) VALUES (?, ?)");
            $stmt->bind_param("si", $naam, $groep_id);
            if ($stmt->num_rows > 5) {
                $message = "Je hebt al 5 leden";
            } else {
                $stmt->execute();
            }
        }
    }
    
    // Form en table *groepen/groep*
    if ($_POST['form_type'] === "groep") {
        $groepnaam = $_POST['groepnaam'];

        // query for obtaining the id of the current user
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $_SESSION['username']);
        $stmt->execute();

        // getting the results
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $user_id = $user['id'];
        $stmt->close();

        // checkt of the gebruiker al een groep bezit
        $stmt = $conn->prepare("SELECT id FROM groepen WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo "je hebt al een groep";
        } else {

            $stmt = $conn->prepare("INSERT INTO groepen (groepnaam, user_id) VALUES (?, ?)");
            $stmt->bind_param("si", $groepnaam, $user_id);

            // checkt of de statement is verstuurd. 
            if ($stmt->execute()) {
                echo "data is verwerkt";
            } else {
                echo "error: " . $stmt->error;
            }
        };

        // statement is gesloten
        $stmt->close();
    }
}

// admin ziet alle groepen en scrum masters alleen de groepen die zijn aangemaakt met hen user_id
if ($role === 'admin') {
    $groepenResult = $conn->query("SELECT id, groepnaam FROM groepen ORDER BY groepnaam ASC");
} else {
    $groepenResult = $conn->query("SELECT id, groepnaam FROM groepen WHERE user_id = $user_id");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- SEO Meta -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presentie bewerken voor jouw groep</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time();?>">

    <style>
        /* table styling voor de db data die is gefetched*/
        table { border-collapse: collapse; width: 400px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; }
        tr:nth-child(even) { background-color: #fafafa; }
        .homepagina .container { }
    </style>
</head>
<body class="main-pagina">
    <!-- Home section-->
    <section id="homepagina" class="homepagina">
        <div class="container">
            <?php if ($message) echo "<p>$message</p>"; ?>

            <?php if ($groepenResult->num_rows > 0): ?>
                <?php while ($groep = $groepenResult->fetch_assoc()): ?>
                    <table style="margin-bottom: 15px;">
                        <tr>
                            <th>Groepnaam:</th>
                            <th><p style=" font-weight: bolder; "><?= htmlspecialchars($groep['groepnaam']) ?></p></th>
                        </tr>
                    </table>
                    <table style="margin-bottom: 50px;">
                        <tr>
                            <th>Studenten:</th>
                        </tr>
                        <?php
                        $groep_id = $groep['id'];
                        $studenten = $conn->query("SELECT naam FROM studenten WHERE groep_id = $groep_id");
                        if ($studenten->num_rows > 0): 
                            while ($student = $studenten->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($student['naam']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td><em>Geen studenten toegevoegd</em></td></tr>
                        <?php endif; ?> <!-- ✅ closes if($studenten->num_rows > 0) -->
                    </table>

                    <!-- only allow adding students to your own group -->
                    <?php if ($role === 'admin' || $groep['id'] == ($conn->query("SELECT id FROM groepen WHERE user_id = $user_id")->fetch_assoc()['id'] ?? 0)): ?>
                        <form action="Presentie.php" method="POST">
                            <input type="hidden" name="form_type" value="student">
                            <h3>Student toevoegen</h3><br>
                            Naam student: <input type="text" name="naam" required>
                            <input type="submit" value="Toevoegen">
                        </form>
                    <?php endif; ?>

                <?php endwhile; ?>
            <?php else: ?>
                <!-- only show if user has no group -->
                <h3>Maak een groep</h3>
                <form action="Presentie.php" method="POST">
                    <input type="hidden" name="form_type" value="groep">
                    Naam van de groep: <input type="text" name="groepnaam" required><br>
                    <input type="submit" value="Groep toevoegen">
                </form>
            <?php endif; ?>
        </div>
    </section>
    <!--script JS--> 
    <script>
        
    </script>
</body>
</html>