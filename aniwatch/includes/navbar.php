<?php
// ============================================================
//  includes/navbar.php  |  Reusable Navigation Bar
//  Include after session_start() on every protected page
// ============================================================
?>
<nav class="navbar">
    <a href="index.php" class="nav-logo">ANI<span>WATCH</span> PH</a>

    <div class="nav-links">
        <a href="index.php"  class="<?= basename($_SERVER['PHP_SELF']) === 'index.php'  ? 'active' : '' ?>">
            <i class="fa-solid fa-house"></i> Home
        </a>
        <a href="add.php"    class="<?= basename($_SERVER['PHP_SELF']) === 'add.php'    ? 'active' : '' ?>">
            <i class="fa-solid fa-plus"></i> Add Anime
        </a>
    </div>

    <div class="nav-right">
        <div class="user-badge">
            <i class="fa-solid fa-circle-user"></i>
            <?= htmlspecialchars($_SESSION['username'] ?? 'Guest') ?>
        </div>
        <a href="logout.php">
            <button class="btn-logout">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </button>
        </a>
    </div>
</nav>
