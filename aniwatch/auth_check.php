<?php
// ============================================================
//  auth_check.php  |  Session Guard
//  Include at the top of every protected page
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
