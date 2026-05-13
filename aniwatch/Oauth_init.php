<?php
// ═══════════════════════════════════════════════════════════════
//  oauth_init.php
//  Step 1: Redirect the user to the chosen OAuth provider.
// ═══════════════════════════════════════════════════════════════
session_start();

$config   = require __DIR__ . '/oauth_config.php';
$provider = filter_input(INPUT_GET, 'provider', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';

if (!array_key_exists($provider, $config)) {
    header('Location: login.php?error=invalid_provider');
    exit;
}

// Generate CSRF state token
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state']    = $state;
$_SESSION['oauth_provider'] = $provider;

// Build authorization URL
$params = [
    'client_id'     => $config[$provider]['client_id'],
    'redirect_uri'  => $config[$provider]['redirect_uri'],
    'response_type' => 'code',
    'scope'         => $config[$provider]['scope'],
    'state'         => $state,
];

if ($provider === 'google') {
    $params['access_type'] = 'online';
    $params['prompt']      = 'select_account'; // let user pick Google account
}

header('Location: ' . $config[$provider]['auth_url'] . '?' . http_build_query($params));
exit;