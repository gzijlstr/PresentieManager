<?php
include 'nav.php';
include 'db.php';

$sql = "SELECT * FROM aanwezigheid ORDER BY id DESC;";
// fetching data vanuit de database
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presentie Manager</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="main-pagina">
    <header id="header">
        
    </header>
    <!-- Home section-->
    <section id="homepagina" class="homepagina">
        <div class="container">
            <h1></h1>
            <?php 
                // checkt of of er rows bestaan
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc())
                    {
                        echo "Naam: " . htmlspecialchars($row['naam']) . " | ";
                        echo "Aanwezig: " . ($row['aanwezig'] ? 'Ja' : 'Nee') . " | ";
                        echo "Ziek: " . ($row['ziek'] ? 'Ja' : 'Nee') . "<br>";
                    }
                }
            ?>
        </div>
    </section>
    <!--script JS-->
</body>
</html>