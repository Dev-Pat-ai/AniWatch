<?php
// ============================================================
//  edit.php  |  Edit Anime  (UPDATE)
//  AniWatch PH
// ============================================================

require_once 'auth_check.php';
require_once 'db.php';

$error = '';

// Get anime ID from URL
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: index.php");
    exit();
}

// Fetch existing anime record (prepared statement)
$stmt = $pdo->prepare("SELECT * FROM anime WHERE id = ?");
$stmt->execute([$id]);
$anime = $stmt->fetch();

if (!$anime) {
    $_SESSION['flash']      = "Anime not found.";
    $_SESSION['flash_type'] = 'error';
    header("Location: index.php");
    exit();
}

// Pre-fill form with existing data
$inputs = $anime;

// ── Handle Form Submit ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect & sanitize
    $inputs['title']         = trim($_POST['title']         ?? '');
    $inputs['description']   = trim($_POST['description']   ?? '');
    $inputs['genre']         = trim($_POST['genre']         ?? '');
    $inputs['thumbnail_url'] = trim($_POST['thumbnail_url'] ?? '');
    $inputs['video_url']     = trim($_POST['video_url']     ?? '');
    $inputs['episodes']      = (int)($_POST['episodes']     ?? 1);
    $inputs['status']        = in_array($_POST['status'] ?? '', ['Ongoing', 'Completed'])
                                ? $_POST['status'] : 'Ongoing';

    // Validation
    if (empty($inputs['title'])) {
        $error = "Anime title is required.";
    } elseif ($inputs['episodes'] < 1) {
        $error = "Episodes must be at least 1.";
    } else {
        // Update using prepared statement
        $update = $pdo->prepare("
            UPDATE anime
            SET title         = ?,
                description   = ?,
                genre         = ?,
                thumbnail_url = ?,
                video_url     = ?,
                episodes      = ?,
                status        = ?
            WHERE id = ?
        ");
        $update->execute([
            $inputs['title'],
            $inputs['description'],
            $inputs['genre'],
            $inputs['thumbnail_url'],
            $inputs['video_url'],
            $inputs['episodes'],
            $inputs['status'],
            $id,
        ]);

        $_SESSION['flash']      = "\"" . $inputs['title'] . "\" has been updated successfully!";
        $_SESSION['flash_type'] = 'success';
        header("Location: index.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Anime – AniWatch PH</title>
    <link rel="stylesheet" href="main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-header">
        <a href="index.php"><i class="fa-solid fa-arrow-left"></i></a>
        <h1>Edit Anime</h1>
    </div>

    <div class="form-page">
        <div class="form-card">
            <h2>
                <i class="fa-solid fa-pen-to-square"></i>
                <?= htmlspecialchars($anime['title']) ?>
            </h2>

            <?php if ($error): ?>
                <div class="flash flash-error">
                    <i class="fa-solid fa-circle-xmark"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="edit.php?id=<?= $id ?>">
                <div class="form-group">
                    <label>Anime Title <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="title" placeholder="e.g. Demon Slayer"
                           value="<?= htmlspecialchars($inputs['title']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" placeholder="Brief synopsis of the anime…"><?= htmlspecialchars($inputs['description']) ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Genre</label>
                        <input type="text" name="genre" placeholder="e.g. Action, Fantasy"
                               value="<?= htmlspecialchars($inputs['genre']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Episodes</label>
                        <input type="number" name="episodes" min="1"
                               value="<?= (int)$inputs['episodes'] ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="Ongoing"   <?= $inputs['status'] === 'Ongoing'   ? 'selected' : '' ?>>Ongoing</option>
                            <option value="Completed" <?= $inputs['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Thumbnail URL</label>
                        <input type="url" name="thumbnail_url" placeholder="https://…"
                               value="<?= htmlspecialchars($inputs['thumbnail_url']) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Video / Embed URL</label>
                    <input type="url" name="video_url"
                           placeholder="https://www.youtube.com/embed/VIDEO_ID"
                           value="<?= htmlspecialchars($inputs['video_url']) ?>">
                    <small style="color:var(--muted);font-size:12px;margin-top:6px;display:block;">
                        Use the YouTube embed URL format: <code style="color:var(--accent);">https://www.youtube.com/embed/VIDEO_ID</code>
                    </small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Save Changes
                    </button>
                    <a href="watch.php?id=<?= $id ?>" class="btn-secondary">
                        <i class="fa-solid fa-eye"></i> View
                    </a>
                    <a href="index.php" class="btn-secondary">
                        <i class="fa-solid fa-xmark"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
