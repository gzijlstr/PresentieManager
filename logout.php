<!-- Display message -->
<?php if ($message): ?>
<div style="background: #cfc; padding:10px; margin-bottom:10px;">
    <?= $message ?>
</div>
<?php endif; ?>

<?php
session_start();
// deletes the session data
session_destroy();
header("Location: loginpage.php");
if (!$message) $message = "✅ Uitgelogd";
exit();
