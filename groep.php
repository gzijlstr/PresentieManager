<?php
// debugging voor php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Sessie word gestart en checkt of de gebruiker is ingelogd,
// word anders weer naar de login teruggekeert.
session_start(); 
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

// prepared statement voor het verkrijgen van de role van de gebruiker (username), bindt string aan user.
$stmt = $conn->prepare("SELECT id, role FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();


// resultaat van de query, role en user_id worden gedefinieerd,
// daarna wordt de statement gesloten.
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_id = $user['id'];
$role = $user['role'];
$stmt->close();


// Form en table *student/studenten*
// Update de studenten met de volgende value van de form:
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['form_type'])) {  

    // 🎯 STUDENT TOEVOEGEN
    if ($_POST['form_type'] === "student") {
        $naam = trim($_POST['naam'] ?? '');
        // Checkt of de variable geen NULL is en value TRUE of FALSE terug
        // checkt de gebruiker (user_id) al een groep heeft
        $stmt = $conn->prepare("SELECT id FROM groepen WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $groep = $result->fetch_assoc();
        $stmt->close();

        if ($groep) {
            $groep_id = $groep['id'];

            // functie voor het tellen van aantal studenten met sql "count". 
            // debugging zodat er geen oneindig studenten kunnen worden aangemaakt, en niet zonder groep.
            $countQuery = $conn->prepare("SELECT COUNT(*) AS count FROM studenten WHERE groep_id = ?");
            $countQuery->bind_param("i", $groep_id);
            $countQuery->execute();
            $countResult = $countQuery->get_result();
            $count = $countResult->fetch_assoc()['count'];
            $countQuery->close();

            if ($count >= 10) {
                $message = "Je hebt al het maximum van 10 studenten in deze groep.";
            } else {
                $stmt = $conn->prepare("INSERT INTO studenten (naam, groep_id) VALUES (?, ?)");
                $stmt->bind_param("si", $naam, $groep_id);
                if ($stmt->execute()) {
                    $message = "Student '$naam' toegevoegd aan je groep.";
                } else {
                    $message = "Fout bij toevoegen student: " . $stmt->error;
                }
                $stmt->close();
            }
        } else {
            $message = "Je hebt nog geen groep aangemaakt.";
        }
    }

    // functie student verwijderen. 
    if ($_POST['form_type'] === "delete_student") {
        $student_id = intval($_POST['student_id'] ?? 0);

        // Controleer of de student echt bij de gebruiker en groep hoort.
        $stmt = $conn->prepare("SELECT s.id 
            FROM studenten s 
            JOIN groepen g ON s.groep_id = g.id 
            WHERE s.id = ? AND g.user_id = ?");
        $stmt->bind_param("ii", $student_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0 || $role === 'admin') {
            $delete = $conn->prepare("DELETE FROM studenten WHERE id = ?");
            $delete->bind_param("i", $student_id);
            if ($delete->execute()) {
                $message = "Student succesvol verwijderd.";
            } else {
                $message = "Fout bij verwijderen student: " . $delete->error;
            }
            $delete->close();
        } else {
            $message = "Je mag deze student niet verwijderen.";
        }
        $stmt->close();
    }
    
    // Form en table *groepen/groep*
    if ($_POST['form_type'] === "groep") {
        $groepnaam = $_POST['groepnaam'];

        // query voor het krijgen van de id van de gebruiker
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $_SESSION['username']);
        $stmt->execute();

        // resultaat van de query
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
    <meta name="keywords" content="Presentie, Aanwezigheid, Overzicht">
    <meta name="Author" content="Gerben Zijlstra">
    <meta name="description" content="Applicatie voor het regelen van groepspresentie">
    <title>Beheer je groep als scrum master.</title>


    <!-- CSS styling -->
    <link rel="stylesheet" href="style.css?v=<?php echo time();?>">

    <style>
        /* table styling voor de db data die is gefetched*/
        table { border-collapse: collapse; width: 390px; border-radius: 20px;}
        th, td { border: 1px dotted #b7b7b7ff; border-radius: 5px; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; border-radius: 5px;}
        tr:nth-child(even) { background-color: #fafafa; }
        .homepagina .container { }
        .delete-form-button {
            padding: 0  ;
            color: var(--color-orange);

        }
    </style>
</head>
<body class="main-pagina">
    <!-- Display message -->
    <?php if ($message): ?>
    <div style="background: #cfc; padding:10px; margin-bottom:10px;">
        <?= $message ?>
    </div>
    <?php endif; ?>

    <!-- Home section-->
    <section id="homepagina" class="homepagina">
        <div class="groep-container">
            <?php if ($groepenResult->num_rows > 0): ?>
                <?php while ($groep = $groepenResult->fetch_assoc()): ?>
                    
                    <div class="box1" style="background-color: #f0f0f0; padding: 20px; border-radius: 15px; border: solid 2px white; ">
                        <!-- overzicht van groepnaam en studenten. -->
                        <table style="margin-bottom: 15px;">
                        <tr>
                            <th>Groep naam:</th>
                            <th><p style=" font-weight: bolder; "><?= htmlspecialchars($groep['groepnaam']) ?></p></th>
                            <th><tr>
                                <?php if ($role === 'admin' || $groep['id'] == ($conn->query("SELECT id FROM groepen WHERE user_id = $user_id")->fetch_assoc()['id'] ?? 0)): ?>
                                    <form method="POST" action="?groep_id=...">

                                        <input type="hidden" name="form_type" value="student">
                                        <h3>Student toevoegen</h3><br>
                                        Naam student: <input type="text" name="naam" required>
                                        <input type="submit" value="Toevoegen">
                                    </form>
                                <?php endif; ?>
                            </tr></th>
                        </tr>

                        <table style="margin-bottom: 50px;">
                            <tr>
                                <th>Studenten:</th>
                                <th>Actie</th>
                            </tr>
                            <!-- student kan alleen studenten aan haar eigen groep toevoegen -->
                            <?php
                            $groep_id = $groep['id'];
                            $studenten = $conn->query("SELECT id, naam FROM studenten WHERE groep_id = $groep_id");
                            if ($studenten->num_rows > 0): 
                                while ($student = $studenten->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($student['naam']) ?></td>
                                        <td>
                                        <!-- Delete button -->
                                        <form method="POST" action="" class="delete-form-button" style="display:inline;">
                                            <input type="hidden" name="form_type" value="delete_student">
                                            <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                                            <button type="submit" class="delete-btn" onclick="return confirm('Weet je zeker dat je deze student wilt verwijderen?')">Verwijderen</button>
                                        </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td><em>Geen studenten toegevoegd</em></td></tr>
                            <?php endif; ?> 
                        </table>
                    </div>

                <?php endwhile; ?>
            <?php else: ?>
                <!-- word alleen zichtbaar als de gebruiker geen groep bezit -->
                 <div class="groep-aanmaken">
                    <h3>Maak een groep</h3>
                    <form method="POST" action="?groep_id=...">
                        <input type="hidden" name="form_type" value="groep">
                        Naam van de groep: <input type="text" name="groepnaam" required><br>
                        <input type="submit" value="Groep toevoegen">
                    </form>
                 </div>
            <?php endif; ?>
        </div>
    </section>
    <!--script JS--> 
    <script>
        
    </script>
</body>
</html>