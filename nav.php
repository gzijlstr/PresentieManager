<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav id="navmenu">
  <div class="nav-container">
    <button id="menu-toggle" aria-label="Menu">☰</button>
    <ul id="nav-links">
      <li><a href="main.php" class="<?= $current_page == 'main.php' ? 'active' : '' ?>">Home</a></li>
      <li><a href="groep.php" class="<?= $current_page == 'groep.php' ? 'active' : '' ?>">Jouw groep</a></li>
      <li><a href="presentie.php" class="<?= $current_page == 'presentie.php' ? 'active' : '' ?>">Presentie</a></li>
      <li><a href="overzicht.php" class="<?= $current_page == 'overzicht.php' ? 'active' : '' ?>">Overzicht</a></li>
      <li><a href="contact.php" class="<?= $current_page == 'contact.php' ? 'active' : '' ?>">Contact</a></li>


      <?php if (isset($_SESSION['username']) && $_SESSION['username'] === 'teacher1'): ?>
      <a href="scrum_registratie.php">Beheer gebruikers</a>
      <?php endif; ?>


      <?php if (isset($_SESSION['username'])): ?>
        <li><a href="logout.php">Logout (<?= htmlspecialchars($_SESSION['username']); ?>)</a></li>
      <?php endif; ?>
    </ul>
  </div>
</nav>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('menu-toggle');
    const navLinks = document.getElementById('nav-links');
    toggle.addEventListener('click', () => {
      navLinks.classList.toggle('show');
    });
  });
</script>



