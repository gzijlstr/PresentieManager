<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

session_start();
// echo "<pre>SESSION DEBUG:\n"; print_r($_SESSION); echo "</pre>";
if (!isset($_SESSION['username'])) {
    header("Location: loginpage.php");
    exit();
}

include 'nav.php';
include 'db.php';

// --- Debug POST ---
// echo "<pre style='background:#eee;padding:10px;border:1px solid #ccc;'>";
// echo "DEBUG POST:\n";
// print_r($_POST['studenten'] ?? 'No POST data yet.');
// echo "</pre>";

$message = "";

// --- Get logged-in user info ---
$stmt = $conn->prepare("SELECT id, role FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_id = $user['id'];
$role = $user['role'];
$stmt->close();

// --- Selected date ---
$selected_date = $_GET['datum'] ?? date("Y-m-d");

// --- Handle POST: save/update all students ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['studenten']) && is_array($_POST['studenten'])) {

    foreach ($_POST['studenten'] as $student_id => $data) {
        $aanwezig = isset($data['aanwezig']) ? intval($data['aanwezig']) : 0;
        $description = $data['description'] ?? '';

        $sql = "INSERT INTO aanwezigheid (student_id, datum, aanwezig, description)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE aanwezig = VALUES(aanwezig), description = VALUES(description)";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $message .= "Fout bij student $student_id: " . $conn->error . "<br>";
            continue;
        }

        // Debug each insert
        // echo "➡️ Inserting student $student_id | aanwezig=$aanwezig | date=$selected_date | desc='$description'<br>";

        $stmt->bind_param("isis", $student_id, $selected_date, $aanwezig, $description);
        if (!$stmt->execute()) {
            $message .= "Fout bij student $student_id: " . $stmt->error . "<br>";
        }
        $stmt->close();
    }

    if (!$message) $message = "✅ Aanwezigheid succesvol opgeslagen!";
}

// --- Fetch students for this user/group ---
if ($role === 'admin') {
    $sql = "SELECT s.id, s.naam, a.aanwezig, a.description
            FROM studenten s
            LEFT JOIN aanwezigheid a ON s.id = a.student_id AND a.datum = ?
            ORDER BY s.naam ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $selected_date);
} else {
    $sql = "SELECT s.id, s.naam, a.aanwezig, a.description
            FROM studenten s
            JOIN groepen g ON s.groep_id = g.id
            LEFT JOIN aanwezigheid a ON s.id = a.student_id AND a.datum = ?
            WHERE g.user_id = ?
            ORDER BY s.naam ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $selected_date, $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
$students = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
<!-- SEO Meta -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords" content="Presentie, Aanwezigheid, Overzicht">
    <meta name="Author" content="Gerben Zijlstra">
    <meta name="description" content="Applicatie voor het regelen van groepspresentie">
    <title>Registreer of bewerk hier de presentie</title>

<link rel="stylesheet" href="style.css?v=<?php echo time();?>">
<style>
table { border-collapse: collapse; width: 100%; margin-top: 10px; }
th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
th { background-color: #f0f0f0; }
tr:nth-child(even) { background-color: #fafafa; }
form { margin-bottom: 15px; }
button, input[type=submit] { padding: 8px 15px; border-radius: 5px; border: 1px solid #999; cursor: pointer; }
</style>
</head>
<body>


<!-- Display message -->
<?php if ($message): ?>
<div style="background: #cfc; padding:10px; margin-bottom:10px;">
    <?= $message ?>
</div>
<?php endif; ?>

<!-- Show current request type -->
<!-- <p><strong>DEBUG:</strong> Current method: // <?= htmlspecialchars($_SERVER['REQUEST_METHOD']) ?></p> -->

<!-- ✅ Calendar / Date selector -->
 <br>
<form id="dateForm" method="GET" action="">
    <h2>Presentie bewerken</h2>
    <label>Datum:
        <input type="date" name="datum" value="<?= htmlspecialchars($selected_date) ?>">
    </label>
    <button type="submit">Toon</button>
</form>


<!-- ✅ Students POST form -->
<form id="saveForm" method="POST" action="?datum=<?= htmlspecialchars($selected_date) ?>">
    <table>
        <tr>
            <th>Naam</th>
            <th>Aanwezig</th>
            <th>Opmerking</th>
        </tr>
        <?php foreach($students as $s): ?>
        <tr>
            <td><?= htmlspecialchars($s['naam']) ?></td>
            <td>
                <label>
                    <input type="radio" name="studenten[<?= $s['id'] ?>][aanwezig]" value="1" <?= $s['aanwezig'] ? 'checked' : '' ?>> Ja
                </label>
                <label>
                    <input type="radio" name="studenten[<?= $s['id'] ?>][aanwezig]" value="0" <?= !$s['aanwezig'] ? 'checked' : '' ?>> Nee
                </label>
            </td>
            <td>
                <input type="text" name="studenten[<?= $s['id'] ?>][description]" 
                       value="<?= htmlspecialchars($s['description'] ?? '') ?>" 
                       placeholder="Optioneel" style="width:95%;">
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <br>
    <button type="submit" form="saveForm">Opslaan</button>
</form>

</body>
</html>
