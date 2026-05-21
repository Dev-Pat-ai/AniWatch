<?php
// ============================================================
//  admin.php  |  Admin Dashboard
//  AniWatch PH
// ============================================================

require_once 'admin_check.php';
require_once 'db.php';

$totalAnime = (int)$pdo->query("SELECT COUNT(*) FROM anime")->fetchColumn();
$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalAdmins = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();

$stmt = $pdo->query("
    SELECT anime.*, users.username AS added_by_username
    FROM anime
    LEFT JOIN users ON users.id = anime.added_by
    ORDER BY anime.created_at DESC
");
$animes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - AniWatch PH</title>
    <link rel="stylesheet" href="main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="section-header">
        <div class="section-title">
            <i class="fa-solid fa-gauge-high"></i>
            Admin Dashboard
        </div>
        <a href="add.php" class="btn-primary">
            <i class="fa-solid fa-plus"></i> Add Anime
        </a>
    </div>

    <div class="admin-stats">
        <div class="stat-card">
            <div class="label">Anime Titles</div>
            <div class="value"><?= $totalAnime ?></div>
        </div>
        <div class="stat-card">
            <div class="label">Users</div>
            <div class="value"><?= $totalUsers ?></div>
        </div>
        <div class="stat-card">
            <div class="label">Admins</div>
            <div class="value"><?= $totalAdmins ?></div>
        </div>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Genre</th>
                <th>Status</th>
                <th>Added By</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($animes)): ?>
                <tr>
                    <td colspan="5">No anime in the library yet.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($animes as $anime): ?>
                    <tr>
                        <td><?= htmlspecialchars($anime['title']) ?></td>
                        <td><?= htmlspecialchars($anime['genre'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($anime['status']) ?></td>
                        <td><?= htmlspecialchars($anime['added_by_username'] ?: 'Seed data') ?></td>
                        <td>
                            <div class="admin-actions">
                                <a href="watch.php?id=<?= $anime['id'] ?>" class="btn-secondary">
                                    <i class="fa-solid fa-eye"></i> View
                                </a>
                                <a href="edit.php?id=<?= $anime['id'] ?>" class="btn-primary">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </a>
                                <a href="delete.php?id=<?= $anime['id'] ?>" class="btn-danger">
                                    <i class="fa-solid fa-trash"></i> Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
