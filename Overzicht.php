<?php

// checkt of de gebruiker is ingelogd
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: loginpage.php"); 
    exit();
}
?>

<?php
include 'nav.php';
include 'db.php';

$message = "";

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

// gekozen datum, standaard vandaag
$selected_date = isset($_GET['datum']) ? $_GET['datum'] : date("Y-m-d");


// functie in geval van een update door een Scrum Master of Admin (docent)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'])) {
    $student_id = intval($_POST['student_id']);
    $datum = $_POST['datum'];
    // ? :, aanwezig is wel of niet aanwezig
    $aanwezig = isset($_POST['aanwezig']) ? intval($_POST['aanwezig']) : 0;
    $ziek = isset($_POST['ziek']) ? intval($_POST['ziek']) : 0;

    // Admin kan elke student updaten, de Scrum Master alleen van haar eigen groep
    if ($role === 'admin') {
        // query versturen van aanwezigheid, 
        // ON DUPLICATE KEY UPDATE, als er al een row bestaat word die updated.
        $stmt = $conn->prepare(
            "INSERT INTO aanwezigheid (student_id, datum, aanwezig, ziek)
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE aanwezig = VALUES(aanwezig), ziek = VALUES(ziek)
            ");
        $stmt->bind_param("isii", $student_id, $datum, $aanwezig, $ziek);
    } else {
        // alleen de student van haar eigen groep word updated
        $stmt = $conn->prepare(
            "INSERT INTO aanwezigheid (student_id, datum, aanwezig, ziek)
            SELECT s.id, ?, ?, ?
            FROM studenten s
            JOIN groepen g ON s.groep_id = g.id
            WHERE s.id = ? AND g.user_id = ?
            ON DUPLICATE KEY UPDATE aanwezig = VALUES(aanwezig), ziek = VALUES(ziek)"
        );
        $stmt->bind_param("siiii", $datum, $aanwezig, $ziek, $student_id, $user_id);
    }

    if($stmt->execute()) {
        $message = "Aanwezigheid bijgewerkt";
    } else {
        $message = "Fout: " . $stmt->error;
    }
    $stmt->close();
}

// Data ophalen
if ($role === 'admin') {
    $sql = "SELECT s.id, s.naam, a.aanwezig, a.ziek, g.groepnaam
            FROM studenten s
            LEFT JOIN groepen g ON s.groep_id = g.id
            LEFT JOIN aanwezigheid a ON s.id = a.student_id AND a.datum = '$selected_date'
            ORDER BY s.id DESC";
} else  {
    $sql = "SELECT s.id, s.naam, a.aanwezig, a.ziek, g.groepnaam
            FROM studenten s
            LEFT JOIN groepen g ON s.groep_id = g.id
            LEFT JOIN aanwezigheid a ON s.id = a.student_id AND a.datum = '$selected_date'
            WHERE g.user_id = $user_id
            ORDER BY s.id DESC";
}
$result = $conn->query($sql);
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <!-- SEO Meta -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bekijk hier jouw overzicht</title>

    <!-- CSS file -->
    <link rel="stylesheet" href="style.css?v=<?php echo time();?>">

    <style>
        /* table styling voor de db data die is gefetched*/
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; }
        tr:nth-child(even) { background-color: #fafafa; }
    </style>
</head>
<body class="main-pagina">
    <header id="header">
        
    </header>
    <!-- Home section-->
    <section id="homepagina" class="homepagina">
        <div class="overzicht-container">
            <div class="studenten">
                <!-- form geeft informatie over de geselecteerde dag aan $selected date -->
                <form method="GET" action="Overzicht.php">
                    <label for="datum">Kies een datum:</label>
                    <input type="date" id="datum" name="datum" value="<?= htmlspecialchars($selected_date) ?>">
                    <button type="submit">Toon</button>
                </form>

                <?php if ($message): ?>
                    <p style="color: green;"><?= htmlspecialchars($message) ?></p>
                <?php endif; ?>

                <!-- aanwezigheid table -->
                <table>
                    <tr>
                        <h3> Aanwezigheid: </h3>
                        <th>Naam</th>
                        <th>Aanwezig</th>
                        <th>Ziek</th>
                        <th>Groep</th>
                        <th>Datum</th>
                        <th>Actie</th>
                    </tr>
                    
                    <?php 
                    // wanneer er het aantal rows in de table-
                    // groter zijn dan 0 wordt de form aangemaakt.
                    if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <form method="POST" action="overzicht.php?datum=<?= $selected_date ?>">
                                    <input type="hidden" name="student_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="datum" value="<?= $selected_date ?>">
                                    <td><?= htmlspecialchars($row['naam']) ?></td>
                                    <td>
                                        <input type="radio" name="aanwezig" value="1" <?= $row['aanwezig'] ? 'checked' : '' ?>> Ja
                                        <input type="radio" name="aanwezig" value="0" <?= !$row['aanwezig'] ? 'checked' : '' ?>> Nee
                                    </td>
                                    <td>
                                        <input type="radio" name="ziek" value="1" <?= $row['ziek'] ? 'checked' : '' ?>> Ja
                                        <input type="radio" name="ziek" value="0" <?= !$row['ziek'] ? 'checked' : '' ?>> Nee
                                    </td>
                                    <td><?= htmlspecialchars($row['groepnaam'] ?? 'Geen groep') ?></td>
                                    <td><?= htmlspecialchars($selected_date) ?></td>
                                    <td><input type="submit" value="Opslaan"></td>
                                </form>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6">Geen gegevens gevonden</td></tr>
                    <?php endif; ?>
                </table>
            </div>
    </section>
    <!--script JS-->
</body>
</html>