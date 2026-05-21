<?php
// ═══════════════════════════════════════════════════════════════
//  oauth_callback.php  –  Rewritten for PDO  ($pdo)
//  Provider redirects back here with ?code=&state=
// ═══════════════════════════════════════════════════════════════
session_start();
require_once __DIR__ . '/db.php';          // gives us $pdo  (PDO)
$config = require __DIR__ . '/oauth_config.php';

// ── 1. Validate provider & CSRF state ─────────────────────────
$provider = filter_input(INPUT_GET, 'provider', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
$code     = $_GET['code']  ?? null;
$state    = $_GET['state'] ?? null;

if (
    !array_key_exists($provider, $config)   ||
    !$code                                   ||
    !$state                                  ||
    empty($_SESSION['oauth_state'])          ||
    !hash_equals($_SESSION['oauth_state'], $state)
) {
    header('Location: login.php?error=oauth_failed');
    exit;
}

unset($_SESSION['oauth_state'], $_SESSION['oauth_provider']);

// ── 2. Exchange authorization code for access token ───────────
$tokenResponse = oauthPost($config[$provider]['token_url'], [
    'client_id'     => $config[$provider]['client_id'],
    'client_secret' => $config[$provider]['client_secret'],
    'code'          => $code,
    'redirect_uri'  => $config[$provider]['redirect_uri'],
    'grant_type'    => 'authorization_code',
], $provider === 'github' ? ['Accept: application/json'] : []);

$accessToken = $tokenResponse['access_token'] ?? null;
if (!$accessToken) {
    header('Location: login.php?error=token_failed');
    exit;
}

// ── 3. Fetch user profile from the provider ───────────────────
$userInfo = oauthGet($config[$provider]['userinfo_url'], $accessToken, $provider);

// Normalize — each provider uses different key names
$email      = $userInfo['email']  ?? null;
$name       = $userInfo['name']   ?? ($userInfo['login'] ?? 'User'); // GitHub uses 'login'
$providerId = (string)($userInfo['sub'] ?? $userInfo['id'] ?? '');

// GitHub: email may be private — fetch from /user/emails endpoint
if ($provider === 'github' && !$email) {
    $emails = oauthGet('https://api.github.com/user/emails', $accessToken, 'github');
    foreach ($emails as $entry) {
        if (!empty($entry['primary']) && !empty($entry['verified'])) {
            $email = $entry['email'];
            break;
        }
    }
}

if (!$email) {
    header('Location: login.php?error=no_email');
    exit;
}

// ── 4. Look up or create the user  (PDO version) ──────────────
$stmt = $pdo->prepare("SELECT id, username, role FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch();   // PDO::FETCH_ASSOC is set in db.php

if ($user) {
    // Existing user — update provider info in case they switched
    $upd = $pdo->prepare("UPDATE users SET provider = ?, provider_id = ? WHERE id = ?");
    $upd->execute([$provider, $providerId, $user['id']]);

} else {
    // New user — auto-register (password = NULL, allowed after schema patch)
    $username = generateUniqueUsername($name, $pdo);

    $insert = $pdo->prepare(
        "INSERT INTO users (username, email, password, provider, provider_id, created_at)
         VALUES (?, ?, NULL, ?, ?, NOW())"
    );
    $insert->execute([$username, $email, $provider, $providerId]);

    $user = [
        'id'       => (int) $pdo->lastInsertId(),
        'username' => $username,
        'role'     => 'user',
    ];
}

// ── 5. Start session and redirect ─────────────────────────────
$_SESSION['user_id']  = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['email']    = $email;
$_SESSION['role']     = $user['role'] ?? 'user';

header('Location: index.php');
exit;


// ═══════════════════════════════════════════════════════════════
//  Helper functions
// ═══════════════════════════════════════════════════════════════

function oauthPost(string $url, array $data, array $extraHeaders = []): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => array_merge(
            ['Content-Type: application/x-www-form-urlencoded'],
            $extraHeaders
        ),
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT        => 15,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true) ?? [];
}

function oauthGet(string $url, string $token, string $provider): array
{
    $headers = [
        'Authorization: Bearer ' . $token,
        'Accept: application/json',
    ];
    if ($provider === 'github') {
        $headers[] = 'User-Agent: AniWatch-OAuth';   // GitHub requires User-Agent
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT        => 15,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true) ?? [];
}

function generateUniqueUsername(string $displayName, PDO $pdo): string
{
    $base     = preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', $displayName));
    $base     = $base ?: 'user';
    $username = $base;
    $i        = 2;

    while (true) {
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
        $check->execute([$username]);
        if (!$check->fetch()) break;
        $username = $base . $i++;
    }

    return $username;
}
