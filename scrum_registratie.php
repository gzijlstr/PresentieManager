<?php
session_start();
include 'db.php';
include 'nav.php';

// Check if the user is logged in and is an admin (teacher)
if (!isset($_SESSION['username'])) {
    header("Location: loginpage.php");
    exit();
}

// Get user role
$stmt = $conn->prepare("SELECT role FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($user['role'] !== 'admin') {
    echo "<p style='color:red;'>🚫 Geen toegang. Alleen de docent kan deze pagina zien.</p>";
    exit();
}

$message = "";

// ✅ Add new Scrum user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type'])) {
    if ($_POST['form_type'] === 'add_user') {
        $new_username = trim($_POST['username']);
        $new_password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
        $role = 'scrum';

        // Check if username already exists
        $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $new_username);
        $check->execute();
        $exists = $check->get_result()->num_rows > 0;
        $check->close();

        if ($exists) {
            $message = " Deze gebruikersnaam bestaat al.";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $new_username, $new_password, $role);
            if ($stmt->execute()) {
                $message = "✅ Nieuwe Scrum gebruiker toegevoegd: <strong>$new_username</strong>";
            } else {
                $message = "❌ Fout bij toevoegen: " . $stmt->error;
            }
            $stmt->close();
        }
    }

    // scum actie
    if ($_POST['form_type'] === 'delete_user') {
        $delete_id = intval($_POST['user_id']);
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'scrum'");
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            $message = "🗑️ Scrum gebruiker verwijderd.";
        } else {
            $message = "❌ Fout bij verwijderen: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch all Scrum users
$scrums = $conn->query("SELECT id, username FROM users WHERE role = 'scrum' ORDER BY username ASC");
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beheer Scrum Gebruikers</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        table { border-collapse: collapse; width: 400px; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; }
        tr:nth-child(even) { background-color: #fafafa; }
        .delete-btn {
            background-color: orange;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            padding: 3px 6px;
            cursor: pointer;
            transition: 0.2s;
        }
        .delete-btn:hover {
            background-color: darkorange;
        }
        form {
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="main-pagina">
    <h2>👨‍🏫 Beheer Scrum Gebruikers</h2>
    <?php if ($message): ?>
        <div style="background:#eef; padding:10px; margin-bottom:10px;">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <h3>Nieuwe Scrum Gebruiker Aanmaken</h3>
    <form method="POST" action="">
        <input type="hidden" name="form_type" value="add_user">
        Gebruikersnaam: <input type="text" name="username" required><br>
        Wachtwoord: <input type="password" name="password" required><br>
        <input type="submit" value="Aanmaken">
    </form>

    <h3>Bestaande Scrum Gebruikers</h3>
    <table>
        <tr>
            <th>Gebruikersnaam</th>
            <th>Actie</th>
        </tr>
        <?php while ($scrum = $scrums->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($scrum['username']) ?></td>
                <td>
                    <form method="POST" action="" style="display:inline;">
                        <input type="hidden" name="form_type" value="delete_user">
                        <input type="hidden" name="user_id" value="<?= $scrum['id'] ?>">
                        <button type="submit" class="delete-btn" onclick="return confirm('Weet je zeker dat je deze gebruiker wilt verwijderen?')">Verwijder</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
