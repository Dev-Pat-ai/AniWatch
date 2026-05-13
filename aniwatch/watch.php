<?php
// ============================================================
//  watch.php  |  Watch Anime
//  AniWatch PH
// ============================================================

require_once 'auth_check.php';
require_once 'db.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: index.php");
    exit();
}

// Fetch this anime
$stmt = $pdo->prepare("SELECT * FROM anime WHERE id = ?");
$stmt->execute([$id]);
$anime = $stmt->fetch();

if (!$anime) {
    $_SESSION['flash']      = "Anime not found.";
    $_SESSION['flash_type'] = 'error';
    header("Location: index.php");
    exit();
}

// Fetch "More Like This" (same genre, different title)
$related = [];
if (!empty($anime['genre'])) {
    $genreWord = explode(',', $anime['genre'])[0]; // first genre word
    $relStmt   = $pdo->prepare("
        SELECT * FROM anime
        WHERE genre LIKE ? AND id != ?
        LIMIT 4
    ");
    $relStmt->execute(["%$genreWord%", $id]);
    $related = $relStmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($anime['title']) ?> – AniWatch PH</title>
    <link rel="stylesheet" href="main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">

    <!-- Back link -->
    <div class="page-header" style="margin-bottom:24px;">
        <a href="index.php"><i class="fa-solid fa-arrow-left"></i></a>
        <h1><?= htmlspecialchars($anime['title']) ?></h1>
    </div>

    <!-- Watch Layout -->
    <div class="watch-layout">

        <!-- Video Player -->
        <div>
            <?php if (!empty($anime['video_url'])): ?>
                <div class="video-player">
                    <iframe src="<?= htmlspecialchars($anime['video_url']) ?>"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                            title="<?= htmlspecialchars($anime['title']) ?>">
                    </iframe>
                </div>
            <?php else: ?>
                <div class="thumb-fallback">
                    <i class="fa-solid fa-video-slash"></i>
                </div>
                <p style="text-align:center;color:var(--muted);margin-top:12px;font-size:14px;">
                    No video URL provided. <a href="edit.php?id=<?= $id ?>" style="color:var(--accent);">Add one</a>
                </p>
            <?php endif; ?>

            <!-- More From Library -->
            <?php if (!empty($related)): ?>
            <div style="margin-top:40px;">
                <div class="section-title" style="margin-bottom:16px;">
                    <i class="fa-solid fa-film"></i> More Like This
                </div>
                <div class="anime-grid" style="grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:16px;">
                    <?php foreach ($related as $rel): ?>
                    <div class="anime-card">
                        <div class="card-badge <?= $rel['status'] === 'Completed' ? 'completed' : '' ?>">
                            <?= htmlspecialchars($rel['status']) ?>
                        </div>
                        <div class="card-thumb">
                            <?php if (!empty($rel['thumbnail_url'])): ?>
                                <img src="<?= htmlspecialchars($rel['thumbnail_url']) ?>"
                                     alt="<?= htmlspecialchars($rel['title']) ?>"
                                     loading="lazy"
                                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                                <div class="card-thumb-placeholder" style="display:none;">
                                    <i class="fa-solid fa-film"></i>
                                </div>
                            <?php else: ?>
                                <div class="card-thumb-placeholder">
                                    <i class="fa-solid fa-film"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-overlay">
                                <div class="overlay-actions">
                                    <a href="watch.php?id=<?= $rel['id'] ?>" class="oa-watch">
                                        <i class="fa-solid fa-play"></i> Watch
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="card-title"><?= htmlspecialchars($rel['title']) ?></div>
                            <div class="card-genre"><?= htmlspecialchars($rel['genre'] ?? '—') ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar Info -->
        <div class="watch-sidebar anime-info-card">
            <?php if (!empty($anime['thumbnail_url'])): ?>
                <img src="<?= htmlspecialchars($anime['thumbnail_url']) ?>"
                     alt="<?= htmlspecialchars($anime['title']) ?>"
                     style="width:100%;border-radius:10px;margin-bottom:16px;object-fit:cover;aspect-ratio:3/4;"
                     onerror="this.style.display='none'">
            <?php endif; ?>

            <h2><?= htmlspecialchars($anime['title']) ?></h2>

            <div class="badge-row">
                <span class="badge badge-accent"><?= htmlspecialchars($anime['genre'] ?? 'Unknown') ?></span>
                <span class="badge <?= $anime['status'] === 'Completed' ? 'badge-success' : 'badge-warning' ?>">
                    <?= htmlspecialchars($anime['status']) ?>
                </span>
            </div>

            <?php if (!empty($anime['description'])): ?>
                <p class="anime-desc"><?= nl2br(htmlspecialchars($anime['description'])) ?></p>
            <?php endif; ?>

            <ul class="info-list">
                <li>
                    <span class="label"><i class="fa-solid fa-tv"></i> Episodes</span>
                    <span class="value"><?= $anime['episodes'] ?></span>
                </li>
                <li>
                    <span class="label"><i class="fa-solid fa-tag"></i> Genre</span>
                    <span class="value"><?= htmlspecialchars($anime['genre'] ?? '—') ?></span>
                </li>
                <li>
                    <span class="label"><i class="fa-solid fa-circle-dot"></i> Status</span>
                    <span class="value"><?= htmlspecialchars($anime['status']) ?></span>
                </li>
                <li>
                    <span class="label"><i class="fa-solid fa-calendar"></i> Added</span>
                    <span class="value"><?= date('M d, Y', strtotime($anime['created_at'])) ?></span>
                </li>
            </ul>

            <div class="watch-actions">
                <a href="edit.php?id=<?= $id ?>" class="btn-primary" style="width:100%;justify-content:center;">
                    <i class="fa-solid fa-pen"></i> Edit Anime
                </a>
                <a href="delete.php?id=<?= $id ?>" class="btn-danger" style="width:100%;justify-content:center;">
                    <i class="fa-solid fa-trash"></i> Delete Anime
                </a>
                <a href="index.php" class="btn-secondary" style="width:100%;justify-content:center;">
                    <i class="fa-solid fa-grid-2"></i> Back to Library
                </a>
            </div>
        </div>

    </div>
</div>

</body>
</html>
