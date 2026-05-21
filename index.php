<?php
// ============================================================
//  index.php  |  Homepage – Browse Anime (READ)
//  AniWatch PH
// ============================================================

require_once 'auth_check.php';
require_once 'db.php';

$isAdmin = ($_SESSION['role'] ?? 'user') === 'admin';

// Flash message from redirect
$flash        = $_SESSION['flash']      ?? '';
$flash_type   = $_SESSION['flash_type'] ?? 'success';
unset($_SESSION['flash'], $_SESSION['flash_type']);

// ── Search / Filter ───────────────────────────────────────────
$search = trim($_GET['q'] ?? '');
$genre  = trim($_GET['genre'] ?? '');

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
        <p>Explore your favorite anime titles — completely free. Stream anime from the library anytime.</p>
        <div class="hero-actions">
            <?php if ($isAdmin): ?>
                <a href="admin.php" class="btn-primary"><i class="fa-solid fa-gauge-high"></i> Admin Dashboard</a>
            <?php endif; ?>
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
                <small id="anime-count" style="font-size:13px;color:var(--muted);font-weight:400;"><?= $total ?> titles</small>
            </div>
        </div>

        <form id="anime-filter-form" method="GET" action="index.php" style="display:flex;gap:10px;margin-bottom:28px;flex-wrap:wrap;">
            <div class="search-bar" style="margin:0;flex:1;min-width:220px;">
                <input type="text" name="q" placeholder="Search anime title…"
                       value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn-primary" style="padding:12px 20px;border-radius:10px;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </div>
            <select name="genre"
                    style="padding:12px 16px;background:var(--surface);border:1px solid var(--border);
                           border-radius:10px;color:var(--text);font-family:Poppins,sans-serif;font-size:14px;outline:none;">
                <option value="">All Genres</option>
                <?php foreach ($genres as $g): ?>
                    <option value="<?= htmlspecialchars($g) ?>" <?= $genre === $g ? 'selected' : '' ?>>
                        <?= htmlspecialchars($g) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="button" id="clear-filters" class="btn-secondary" style="display:inline-flex;align-items:center;gap:6px;">
                <i class="fa-solid fa-xmark"></i> Clear
            </button>
        </form>

        <!-- Anime Grid -->
        <div class="legacy-grid" style="display:none;">
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
                                <?php if ($isAdmin): ?>
                                    <a href="edit.php?id=<?= $anime['id'] ?>" class="oa-edit">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </a>
                                    <a href="delete.php?id=<?= $anime['id'] ?>" class="oa-del">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                <?php endif; ?>
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
        </div>
        <div id="ajax-feedback" class="ajax-feedback" role="status" aria-live="polite"></div>
        <div id="anime-grid" class="anime-grid" data-is-admin="<?= $isAdmin ? '1' : '0' ?>"></div>
    </section>

</div>

<script>
const form = document.getElementById('anime-filter-form');
const grid = document.getElementById('anime-grid');
const feedback = document.getElementById('ajax-feedback');
const count = document.getElementById('anime-count');
const clearButton = document.getElementById('clear-filters');
const genreSelect = form.querySelector('select[name="genre"]');
const searchInput = form.querySelector('input[name="q"]');
const isAdmin = grid.dataset.isAdmin === '1';

function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value ?? '';
    return div.innerHTML;
}

function setFeedback(type, message) {
    feedback.className = `ajax-feedback ${type}`;
    feedback.innerHTML = message;
}

function showLoading() {
    grid.innerHTML = `
        <div class="loading-state">
            <span class="spinner"></span>
            <span>Loading anime library...</span>
        </div>
    `;
    setFeedback('loading', '<span class="spinner"></span> Loading anime...');
}

function showEmpty(message) {
    grid.innerHTML = `
        <div class="empty-state ajax-empty">
            <i class="fa-solid fa-film"></i>
            <h3>No anime found</h3>
            <p>${escapeHtml(message)}</p>
            ${isAdmin ? '<a href="add.php" class="btn-primary"><i class="fa-solid fa-plus"></i> Add First Anime</a>' : ''}
        </div>
    `;
}

function animeCard(anime) {
    const statusClass = anime.status === 'Completed' ? ' completed' : '';
    const thumbnail = anime.thumbnail_url
        ? `<img src="${escapeHtml(anime.thumbnail_url)}" alt="${escapeHtml(anime.title)}" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
           <div class="card-thumb-placeholder" style="display:none;"><i class="fa-solid fa-film"></i></div>`
        : '<div class="card-thumb-placeholder"><i class="fa-solid fa-film"></i></div>';
    const adminActions = isAdmin
        ? `<a href="edit.php?id=${anime.id}" class="oa-edit"><i class="fa-solid fa-pen"></i> Edit</a>
           <a href="delete.php?id=${anime.id}" class="oa-del"><i class="fa-solid fa-trash"></i></a>`
        : '';

    return `
        <div class="anime-card">
            <div class="card-badge${statusClass}">${escapeHtml(anime.status)}</div>
            <div class="card-thumb">
                ${thumbnail}
                <div class="card-overlay">
                    <div class="overlay-actions">
                        <a href="watch.php?id=${anime.id}" class="oa-watch"><i class="fa-solid fa-play"></i> Watch</a>
                        ${adminActions}
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="card-title" title="${escapeHtml(anime.title)}">${escapeHtml(anime.title)}</div>
                <div class="card-genre">${escapeHtml(anime.genre || '-')}</div>
                <div class="card-meta">
                    <span class="card-eps"><i class="fa-solid fa-tv" style="font-size:10px;"></i> ${Number(anime.episodes || 0)} eps</span>
                    <a href="watch.php?id=${anime.id}" style="font-size:18px;color:var(--accent);transition:opacity 0.2s;" title="Watch">
                        <i class="fa-solid fa-circle-play"></i>
                    </a>
                </div>
            </div>
        </div>
    `;
}

async function loadAnime() {
    const params = new URLSearchParams(new FormData(form));
    const query = params.toString();

    showLoading();

    try {
        const response = await fetch(`api/anime.php?${query}`, {
            headers: { 'Accept': 'application/json' }
        });

        if (!response.ok) {
            throw new Error(`Request failed with status ${response.status}`);
        }

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'Unable to load anime.');
        }

        count.textContent = `${result.total} title${result.total === 1 ? '' : 's'}`;
        setFeedback('success', `<i class="fa-solid fa-circle-check"></i> ${escapeHtml(result.message)}`);
        history.replaceState(null, '', query ? `index.php?${query}` : 'index.php');

        if (result.data.length === 0) {
            showEmpty(result.message);
            return;
        }

        grid.innerHTML = result.data.map(animeCard).join('');
    } catch (error) {
        count.textContent = '0 titles';
        setFeedback('error', `<i class="fa-solid fa-circle-xmark"></i> ${escapeHtml(error.message)}`);
        grid.innerHTML = `
            <div class="empty-state ajax-empty">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <h3>Could not load anime</h3>
                <p>Please check your connection or try again.</p>
            </div>
        `;
    }
}

form.addEventListener('submit', (event) => {
    event.preventDefault();
    loadAnime();
});

genreSelect.addEventListener('change', loadAnime);

clearButton.addEventListener('click', () => {
    searchInput.value = '';
    genreSelect.value = '';
    loadAnime();
});

document.addEventListener('DOMContentLoaded', loadAnime);
</script>

</body>
</html>
