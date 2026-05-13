<?php
// ============================================================
//  index.php  |  Homepage – Browse Anime (READ)
//  AniWatch PH
// ============================================================

require_once 'auth_check.php';
require_once 'db.php';

// Flash message from redirect
$flash        = $_SESSION['flash']      ?? '';
$flash_type   = $_SESSION['flash_type'] ?? 'success';
unset($_SESSION['flash'], $_SESSION['flash_type']);

// ── Search / Filter ───────────────────────────────────────────
$search = trim($_GET['q'] ?? '');
$genre  = trim($_GET['genre'] ?? '');

$query  = "SELECT * FROM anime WHERE 1=1";
$params = [];

if ($search !== '') {
    $query   .= " AND title LIKE ?";
    $params[] = "%$search%";
}
if ($genre !== '') {
    $query   .= " AND genre LIKE ?";
    $params[] = "%$genre%";
}

$query .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$animes = $stmt->fetchAll();

// Get distinct genres for filter
$genreStmt = $pdo->query("SELECT DISTINCT genre FROM anime WHERE genre IS NOT NULL AND genre != '' ORDER BY genre");
$genres    = $genreStmt->fetchAll(PDO::FETCH_COLUMN);

// Total count
$totalStmt = $pdo->query("SELECT COUNT(*) FROM anime");
$total     = $totalStmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AniWatch PH – Browse Anime</title>
    <link rel="stylesheet" href="main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">

    <!-- Hero -->
    <section class="hero">
        <div class="hero-label">🎌 Free Anime Streaming</div>
        <h1>Watch Anime<br><span>Anytime, Anywhere</span></h1>
        <p>Explore your favorite anime titles — completely free. Add, manage, and stream anime in one place.</p>
        <div class="hero-actions">
            <a href="add.php" class="btn-primary"><i class="fa-solid fa-plus"></i> Add Anime</a>
            <a href="#anime-list" class="btn-secondary"><i class="fa-solid fa-play"></i> Browse Library</a>
        </div>
    </section>

    <!-- Flash Message -->
    <?php if ($flash): ?>
        <div class="flash flash-<?= $flash_type ?>">
            <i class="fa-solid fa-<?= $flash_type === 'success' ? 'circle-check' : 'circle-xmark' ?>"></i>
            <?= htmlspecialchars($flash) ?>
        </div>
    <?php endif; ?>

    <!-- Search & Filter -->
    <section id="anime-list">
        <div class="section-header">
            <div class="section-title">
                <i class="fa-solid fa-film"></i>
                Anime Library
                <small style="font-size:13px;color:var(--muted);font-weight:400;"><?= $total ?> titles</small>
            </div>
        </div>

        <form method="GET" action="index.php" style="display:flex;gap:10px;margin-bottom:28px;flex-wrap:wrap;">
            <div class="search-bar" style="margin:0;flex:1;min-width:220px;">
                <input type="text" name="q" placeholder="Search anime title…"
                       value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn-primary" style="padding:12px 20px;border-radius:10px;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </div>
            <select name="genre" onchange="this.form.submit()"
                    style="padding:12px 16px;background:var(--surface);border:1px solid var(--border);
                           border-radius:10px;color:var(--text);font-family:Poppins,sans-serif;font-size:14px;outline:none;">
                <option value="">All Genres</option>
                <?php foreach ($genres as $g): ?>
                    <option value="<?= htmlspecialchars($g) ?>" <?= $genre === $g ? 'selected' : '' ?>>
                        <?= htmlspecialchars($g) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($search || $genre): ?>
                <a href="index.php" class="btn-secondary" style="display:inline-flex;align-items:center;gap:6px;">
                    <i class="fa-solid fa-xmark"></i> Clear
                </a>
            <?php endif; ?>
        </form>

        <!-- Anime Grid -->
        <?php if (empty($animes)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-film"></i>
                <h3>No anime found</h3>
                <p>
                    <?= $search ? "No results for \"" . htmlspecialchars($search) . "\"" : "The library is empty!" ?>
                </p>
                <a href="add.php" class="btn-primary"><i class="fa-solid fa-plus"></i> Add First Anime</a>
            </div>
        <?php else: ?>
            <div class="anime-grid">
                <?php foreach ($animes as $anime): ?>
                <div class="anime-card">

                    <!-- Status badge -->
                    <div class="card-badge <?= $anime['status'] === 'Completed' ? 'completed' : '' ?>">
                        <?= htmlspecialchars($anime['status']) ?>
                    </div>

                    <!-- Thumbnail -->
                    <div class="card-thumb">
                        <?php if (!empty($anime['thumbnail_url'])): ?>
                            <img src="<?= htmlspecialchars($anime['thumbnail_url']) ?>"
                                 alt="<?= htmlspecialchars($anime['title']) ?>"
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

                        <!-- Hover Overlay -->
                        <div class="card-overlay">
                            <div class="overlay-actions">
                                <a href="watch.php?id=<?= $anime['id'] ?>" class="oa-watch">
                                    <i class="fa-solid fa-play"></i> Watch
                                </a>
                                <a href="edit.php?id=<?= $anime['id'] ?>" class="oa-edit">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </a>
                                <a href="delete.php?id=<?= $anime['id'] ?>" class="oa-del">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="card-title" title="<?= htmlspecialchars($anime['title']) ?>">
                            <?= htmlspecialchars($anime['title']) ?>
                        </div>
                        <div class="card-genre"><?= htmlspecialchars($anime['genre'] ?? '—') ?></div>
                        <div class="card-meta">
                            <span class="card-eps">
                                <i class="fa-solid fa-tv" style="font-size:10px;"></i>
                                <?= $anime['episodes'] ?> eps
                            </span>
                            <a href="watch.php?id=<?= $anime['id'] ?>"
                               style="font-size:18px;color:var(--accent);transition:opacity 0.2s;"
                               title="Watch">
                                <i class="fa-solid fa-circle-play"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

</div>

</body>
</html>
