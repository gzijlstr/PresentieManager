<?php
include 'db.php';
include 'nav.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $naam  = $_POST['naam'];
    // checkt of de variable geen NULL is en value TRUE of FALSE terug
    $aanwezig = isset($_POST['aanwezig']) ? 1 : 0;
    $ziek = isset($_POST['Ziek']) ? 1 : 0;

    $stmt = $conn->prepare("INSERT INTO aanwezigheid (naam, aanwezig, ziek) VALUES (?, ?, ?)");
    // binds the parameters to the sql query, string, int, int, by telling sql the type of- 
    // data, it minimizes risk of SQL injections.
     
    $stmt->bind_param("sii", $naam, $aanwezig, $ziek);

    if ($stmt->execute()) {
        echo "Data saved successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    // after saving the data, the connection can be closed
    $stmt->close();
    $conn->close();
}
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
        <button onclick="Formshow()">Groep aanmaken</button><br>
        <div class="container">
            <form action="Presentie.php" id="Presentieform" method="POST" style="display: none;">
            Naam: <input type="text" name="naam"><br>
            Aanwezig: <input type="checkbox" name="aanwezig"><br>
            Ziek: <input type="checkbox" name="ziek"><br>
            <input type="submit">
            </form>
        </div>
    </section>
    <!--script JS--> 
    <script>
        function Formshow() {
            form = document.getElementById('Presentieform');
            if (form.style.display === 'none') {
                form.style.display = "block";
            }
        }
    </script>
</body>
</html>