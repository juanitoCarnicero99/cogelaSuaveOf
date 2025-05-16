<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo '<div style="padding:30px;text-align:center;">Debes iniciar sesión para ver tus entradas.</div>';
    exit();
}
include 'db.php';
$user_id = $_SESSION['user_id'];
$entries = $mysqli->query("SELECT * FROM journal_entries WHERE user_id = $user_id ORDER BY created_at DESC");
?>
<div class="journal-entries-modal">
    <h3 style="color:var(--primary-color);margin-bottom:18px;">Mis Entradas Anteriores</h3>
    <?php if ($entries->num_rows === 0): ?>
        <p style="text-align:center;">No tienes entradas registradas.</p>
    <?php else: ?>
        <?php while ($row = $entries->fetch_assoc()): ?>
            <div class="journal-entry">
                <div class="entry-date"><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></div>
                <div class="entry-content"><?php echo nl2br(htmlspecialchars($row['entry'])); ?></div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div> 