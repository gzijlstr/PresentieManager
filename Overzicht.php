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

include 'nav.php';
include 'db.php';

// standaard bericht voor debugging of gebruiker informatie
$message = "";

// bindt de gegevens van de gebruiker aan de gebruiker, prepared statement 
$stmt = $conn->prepare("SELECT id, role FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_id = $user['id'];
$role = $user['role'];
$stmt->close();

// verkrijgt geselecteerde datum
$selected_date = $_GET['datum'] ?? date("Y-m-d");

// verkrijgt verschillende groepen voor admin view
$selected_group = $_GET['groep'] ?? "";

// Admin/docent view
if ($role === 'admin') {

    // alle groepen ophalen met query in volgorde 
    $groepen = $conn->query("SELECT id, groepnaam FROM groepen ORDER BY groepnaam ASC");

    // query voor het filteren van de groepen, met alle informatie van de studenten
    $sql = "SELECT s.id, s.naam, g.id AS groep_id, g.groepnaam, a.aanwezig, a.description
            FROM studenten s
            LEFT JOIN groepen g ON s.groep_id = g.id
            LEFT JOIN aanwezigheid a ON s.id = a.student_id AND a.datum = ?
            ";

    if ($selected_group) {
        $sql .= " WHERE g.id = ?";
    }

    $sql .= " ORDER BY g.groepnaam, s.naam ASC";

    // prepared statement voor het ophalen van de datum met de geselecteerde groep
    $stmt = $conn->prepare($sql);
    if ($selected_group) {
        $stmt->bind_param("si", $selected_date, $selected_group);
    } else {
        $stmt->bind_param("s", $selected_date);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $students = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // studenten worden in haar groep gesorteerd
    $grouped_students = [];
    foreach ($students as $s) {
        $grouped_students[$s['groepnaam']][] = $s;
    }
}

// Scrum master view
else {
    // functie voor het verkrijgen van de gebruiker's groep
    $stmt = $conn->prepare("SELECT id, groepnaam FROM groepen WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $groep_result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // als groep daadwerkelijk correct bestaat word alle informatie behaald met een prepared statement, anders: Error
    if ($groep_result) {
        $groep_id = $groep_result['id'];
        $groepnaam = $groep_result['groepnaam'];

        $stmt = $conn->prepare("SELECT s.id, s.naam, a.aanwezig, a.description
                                FROM studenten s
                                LEFT JOIN aanwezigheid a ON s.id = a.student_id AND a.datum = ?
                                WHERE s.groep_id = ?
                                ORDER BY s.naam ASC");
        $stmt->bind_param("si", $selected_date, $groep_id);
        $stmt->execute();
        $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        $groepnaam = "Geen groep gevonden";
        $students = [];
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <!-- Meta SEO -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords" content="Presentie, Aanwezigheid, Overzicht">
    <meta name="Author" content="Gerben Zijlstra">
    <meta name="description" content="Applicatie voor het regelen van groepspresentie">
    <title>Overzicht</title>

    <!-- boostrap gebruik voor HTML -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Style CSS -->
    <link rel="stylesheet" href="style.css">
    <style>
        table { border-collapse: collapse; width: 100%; margin-bottom: 30px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; }
        tr:nth-child(even) { background-color: #fafafa; }
        h3 { margin-top: 40px; }
        form {
            padding: 20px;
        }
    </style>
</head>
<body>

<form method="GET" action="overzicht.php">
    <!-- Selected datum functie -->
    <h2>Overzicht aanwezigheid voor <?= htmlspecialchars($selected_date) ?></h2>
    <label>Datum: <input type="date" name="datum" value="<?= htmlspecialchars($selected_date) ?>"></label>

    <!-- Admin view voor alle groepen en studenten in categorieen -->
    <?php if ($role === 'admin'): ?>
        <label style="margin-left:20px;">Groep:
            <select name="groep" onchange="this.form.submit()">
                <option value="">Alle groepen</option>
                <?php while ($g = $groepen->fetch_assoc()): ?>
                    <option value="<?= $g['id'] ?>" <?= ($selected_group == $g['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($g['groepnaam']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </label>
    <?php endif; ?>
    <button type="submit">Toon</button>
</form>


<?php if ($role === 'admin'): ?>

    <?php if ($selected_group): ?>
        <h3>Groep: <?= htmlspecialchars(array_values($grouped_students)[0][0]['groepnaam'] ?? 'Onbekend') ?></h3>
    <?php endif; ?>

    <?php if ($grouped_students): ?>
        <?php foreach ($grouped_students as $groepnaam => $leden): ?>
            <?php if (!$selected_group): ?>
                <h3><?= htmlspecialchars($groepnaam ?: 'Geen groep') ?></h3>
            <?php endif; ?>

            <table>
                <tr>
                    <th>Naam</th>
                    <th>Aanwezig</th>
                    <th>Opmerking</th>
                </tr>
                <?php foreach ($leden as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['naam']) ?></td>
                        <td><?= $s['aanwezig'] === null ? '-' : ($s['aanwezig'] ? 'Ja' : 'Nee') ?></td>
                        <td><?= htmlspecialchars($s['description'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Geen gegevens gevonden.</p>
    <?php endif; ?>

<?php else: ?>

    <h3>Groep: <?= htmlspecialchars($groepnaam) ?></h3>

    <div class="card mb-4 shadow-sm border-0 overzicht-card">
  <div class="card-header text-white" style="background-color: var(--color-greenblue);">
      <h5 class="mb-0"><?= htmlspecialchars($groepnaam ?: 'Geen groep') ?></h5>
  </div>
  <div class="card-body p-0">
      <table class="table table-striped table-hover m-0">
          <thead class="table-warning">
              <tr>
                  <th>Naam</th>
                  <th>Aanwezig</th>
                  <th>Opmerking</th>
              </tr>
          </thead>
          <tbody>
              <?php foreach ($leden as $s): ?>
              <tr>
                  <td><?= htmlspecialchars($s['naam']) ?></td>
                  <td><?= $s['aanwezig'] === null ? '-' : ($s['aanwezig'] ? '✅ Ja' : '❌ Nee') ?></td>
                  <td><?= htmlspecialchars($s['description'] ?? '-') ?></td>
              </tr>
              <?php endforeach; ?>
          </tbody>
      </table>
  </div>
</div>


<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
