<?php
define('DB_HOST', 'sql301.infinityfree.com');
define('DB_USER', 'if0_41872439');
define('DB_PASS', 'Johnpatrick237');
define('DB_NAME', 'if0_41872439_data');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<p style='color:red;font-family:sans-serif;padding:20px;'>
        <strong>Database Error:</strong> " . htmlspecialchars($e->getMessage()) . "<br>
        Make sure MySQL is running and you have imported <code>schema.sql</code>.
    </p>");
}
?>