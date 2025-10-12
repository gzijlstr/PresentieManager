<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: loginpage.php");
    exit();
}

include 'nav.php';
include 'db.php';

$message = "";

// Get logged-in user info
$stmt = $conn->prepare("SELECT id, role FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_id = $user['id'];
$role = $user['role'];
$stmt->close();

// Selected date
$selected_date = $_GET['datum'] ?? date("Y-m-d");

// Fetch students + presence for the date
if ($role === 'admin') {
    $sql = "SELECT s.id, s.naam, g.groepnaam, a.aanwezig, a.description
            FROM studenten s
            LEFT JOIN groepen g ON s.groep_id = g.id
            LEFT JOIN aanwezigheid a ON s.id = a.student_id AND a.datum = ?
            ORDER BY g.groepnaam, s.naam ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $selected_date);
} else {
    $sql = "SELECT s.id, s.naam, g.groepnaam, a.aanwezig, a.description
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
    <title>Overzicht data van studenten en groepen.</title>

    <!-- Style css -->
    <link rel="stylesheet" href="style.css?v=<?php echo time();?>">
<style>
table { border-collapse: collapse; width: 100%; }
th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
th { background-color: #f0f0f0; }
tr:nth-child(even) { background-color: #fafafa; }
</style>
</head>
<body>
<h2>Overzicht aanwezigheid voor <?= htmlspecialchars($selected_date) ?></h2>

<form method="GET" action="overzicht.php">
    <label>Datum: <input type="date" name="datum" value="<?= htmlspecialchars($selected_date) ?>"></label>
    <button type="submit">Toon</button>
</form>

<table>
<tr>
    <th>Naam</th>
    <th>Groep</th>
    <th>Aanwezig</th>
    <th>Opmerking</th>
</tr>

<?php if ($students): ?>
    <?php foreach($students as $s): ?>
    <tr>
        <td><?= htmlspecialchars($s['naam']) ?></td>
        <td><?= htmlspecialchars($s['groepnaam'] ?? 'Geen groep') ?></td>
        <td><?= $s['aanwezig'] === null ? '-' : ($s['aanwezig'] ? 'Ja' : 'Nee') ?></td>
        <td><?= htmlspecialchars($s['description'] ?? '-') ?></td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
<tr>
    <td colspan="4">Geen gegevens voor deze datum</td>
</tr>
<?php endif; ?>
</table>
</body>
</html>
