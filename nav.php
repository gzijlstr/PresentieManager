<nav id="navmenu">
    <ul>
        <li><a href="main.php">Home</a></li>
        <li><a href="overzicht.php">Overzicht</a></li>
        <li><a href="contact.php">Contact</a></li>
        <li><a href="presentie.php">Jouw groep</a></li>

        <?php
        // als de sessie een username bevat en niet NULL is.   
        if (isset($_SESSION['username'])): 
        ?>
            <li><a href="logout.php">Logout (<?= htmlspecialchars($_SESSION['username']); ?>)</a></li>
        <?php endif; ?>
    </ul>
</nav>
