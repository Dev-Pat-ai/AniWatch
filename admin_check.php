<?php
// ============================================================
//  admin_check.php  |  Admin-only Session Guard
// ============================================================

require_once 'auth_check.php';

if (($_SESSION['role'] ?? 'user') !== 'admin') {
    $_SESSION['flash']      = "You need an admin account to manage anime.";
    $_SESSION['flash_type'] = 'error';
    header("Location: index.php");
    exit();
}
?>
