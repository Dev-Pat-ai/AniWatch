<?php
// ═══════════════════════════════════════════════════════════════
//  oauth_callback.php
//  Step 2: Provider redirects back here with ?code=&state=
//  We exchange the code for a token, fetch the user's profile,
//  then log them in or auto-register them.
// ═══════════════════════════════════════════════════════════════
session_start();
require_once __DIR__ . '/db.php';   // provides $conn (mysqli)
$config = require __DIR__ . '/oauth_config.php';

// ── 1. Validate provider & CSRF state ─────────────────────────
$provider = filter_input(INPUT_GET, 'provider', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
$code     = $_GET['code']  ?? null;
$state    = $_GET['state'] ?? null;

if (
    !array_key_exists($provider, $config)  ||
    !$code                                  ||
    !$state                                 ||
    empty($_SESSION['oauth_state'])         ||
    !hash_equals($_SESSION['oauth_state'], $state)
) {
    header('Location: login.html?error=oauth_failed');
    exit;
}

// Clear one-time state
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
    header('Location: login.html?error=token_failed');
    exit;
}

// ── 3. Fetch user profile from the provider ───────────────────
$userInfo = oauthGet($config[$provider]['userinfo_url'], $accessToken, $provider);

// Normalize fields (each provider uses different key names)
$email      = $userInfo['email']   ?? null;
$name       = $userInfo['name']    ?? ($userInfo['login'] ?? 'User'); // GitHub uses 'login'
$providerId = (string)($userInfo['sub'] ?? $userInfo['id'] ?? '');

// GitHub: email may be private — fetch from the /user/emails endpoint
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
    // User refused to share their email — cannot proceed
    header('Location: login.html?error=no_email');
    exit;
}

// ── 4. Look up or create the user in the database ─────────────
// Assumes your users table has at least: id, username, email
// Run the ALTER statements in schema_oauth_patch.sql if needed.

$stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // ── Existing user: log them in ─────────────────────────────
    $user = $result->fetch_assoc();

    // Optionally update provider info in case they switched providers
    $upd = $conn->prepare(
        "UPDATE users SET provider = ?, provider_id = ? WHERE id = ?"
    );
    $upd->bind_param('ssi', $provider, $providerId, $user['id']);
    $upd->execute();

} else {
    // ── New user: auto-register them ──────────────────────────
    $username = generateUniqueUsername($name, $conn);

    $insert = $conn->prepare(
        "INSERT INTO users (username, email, provider, provider_id, created_at)
         VALUES (?, ?, ?, ?, NOW())"
    );
    $insert->bind_param('ssss', $username, $email, $provider, $providerId);
    $insert->execute();

    $user = [
        'id'       => $conn->insert_id,
        'username' => $username,
    ];
}

// ── 5. Start the session and redirect to the app ──────────────
$_SESSION['user_id']  = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['email']    = $email;

header('Location: index.php');
exit;


// ═══════════════════════════════════════════════════════════════
//  Helper functions
// ═══════════════════════════════════════════════════════════════

/**
 * POST request via cURL, returns decoded JSON array.
 */
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

/**
 * GET request via cURL with Bearer token, returns decoded JSON array.
 */
function oauthGet(string $url, string $token, string $provider): array
{
    $headers = [
        'Authorization: Bearer ' . $token,
        'Accept: application/json',
    ];

    // GitHub requires a User-Agent header
    if ($provider === 'github') {
        $headers[] = 'User-Agent: AniWatch-OAuth';
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

/**
 * Turn a display name into a unique DB-safe username.
 * e.g. "John Doe" → "John_Doe", or "John_Doe2" if taken.
 */
function generateUniqueUsername(string $displayName, mysqli $conn): string
{
    // Strip non-alphanumeric/underscore chars, replace spaces with underscores
    $base = preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', $displayName));
    $base = $base ?: 'user';

    $username = $base;
    $i        = 2;

    while (true) {
        $check = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
        $check->bind_param('s', $username);
        $check->execute();
        $check->store_result();

        if ($check->num_rows === 0) {
            break; // username is free
        }

        $username = $base . $i++;
    }

    return $username;
}