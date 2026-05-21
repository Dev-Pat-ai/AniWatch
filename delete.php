<?php
// ============================================================
//  delete.php  |  Delete Anime  (DELETE)
//  AniWatch PH  –  Shows confirmation page before deleting
// ============================================================

require_once 'admin_check.php';
require_once 'db.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: index.php");
    exit();
}

// Fetch anime to confirm
$stmt = $pdo->prepare("SELECT * FROM anime WHERE id = ?");
$stmt->execute([$id]);
$anime = $stmt->fetch();

if (!$anime) {
    $_SESSION['flash']      = "Anime not found.";
    $_SESSION['flash_type'] = 'error';
    header("Location: index.php");
    exit();
}

// ── Handle Confirmed Delete ───────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $title = $anime['title']; // save before deletion

    $del = $pdo->prepare("DELETE FROM anime WHERE id = ?");
    $del->execute([$id]);

    $_SESSION['flash']      = "\"$title\" has been removed from the library.";
    $_SESSION['flash_type'] = 'success';
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Anime – AniWatch PH</title>
    <link rel="stylesheet" href="main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">

    <div class="delete-card">
        <i class="fa-solid fa-triangle-exclamation big-icon"></i>
        <h2>Delete Anime?</h2>
        <p>
            You are about to permanently delete<br>
            <strong style="color:var(--text);"><?= htmlspecialchars($anime['title']) ?></strong><br>
            from the library. This action cannot be undone.
        </p>

        <?php if (!empty($anime['thumbnail_url'])): ?>
            <img src="<?= htmlspecialchars($anime['thumbnail_url']) ?>"
                 alt="<?= htmlspecialchars($anime['title']) ?>"
                 style="width:100px;border-radius:8px;margin-bottom:24px;
                        box-shadow:0 4px 20px rgba(231,76,60,0.3);"
                 onerror="this.style.display='none'">
        <?php endif; ?>

        <div class="delete-actions">
            <!-- Confirm Delete Form -->
            <form method="POST" action="delete.php?id=<?= $id ?>" style="display:inline;">
                <input type="hidden" name="confirm_delete" value="1">
                <button type="submit" class="btn-danger" style="padding:12px 24px;font-size:14px;">
                    <i class="fa-solid fa-trash"></i> Yes, Delete
                </button>
            </form>

            <a href="index.php" class="btn-secondary">
                <i class="fa-solid fa-xmark"></i> Cancel
            </a>
        </div>
    </div>

</div>

</body>
</html>
