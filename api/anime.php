<?php
// ============================================================
//  api/anime.php  |  JSON endpoint for asynchronous anime search
// ============================================================

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Please log in to view the anime library.',
    ]);
    exit();
}

require_once __DIR__ . '/../db.php';

try {
    $search = trim($_GET['q'] ?? '');
    $genre  = trim($_GET['genre'] ?? '');

    $query  = "SELECT id, title, description, genre, thumbnail_url, video_url, episodes, status, created_at
               FROM anime
               WHERE 1=1";
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

    echo json_encode([
        'success' => true,
        'message' => count($animes) > 0 ? 'Anime library loaded.' : 'No anime matched your filters.',
        'total' => count($animes),
        'data' => $animes,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Something went wrong while loading anime. Please try again.',
    ]);
}
?>
