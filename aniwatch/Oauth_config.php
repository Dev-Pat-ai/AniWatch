<?php
// ═══════════════════════════════════════════════════════════════
//  OAuth Configuration
//  Fill in YOUR credentials from each developer console.
//  See the README comments below for how to get each one.
// ═══════════════════════════════════════════════════════════════

// ── Change this to your actual domain / local path ────────────
define('BASE_URL', 'http://localhost/Activity4/aniwatch');

return [

    // ── Google ──────────────────────────────────────────────────
    // Console: https://console.cloud.google.com → APIs & Services → Credentials
    // Add Authorised redirect URI: BASE_URL/oauth_callback.php?provider=google
    'google' => [
        'client_id'     => 'YOUR_GOOGLE_CLIENT_ID',
        'client_secret' => 'YOUR_GOOGLE_CLIENT_SECRET',
        'redirect_uri'  => BASE_URL . '/oauth_callback.php?provider=google',
        'auth_url'      => 'https://accounts.google.com/o/oauth2/v2/auth',
        'token_url'     => 'https://oauth2.googleapis.com/token',
        'userinfo_url'  => 'https://www.googleapis.com/oauth2/v3/userinfo',
        'scope'         => 'openid email profile',
    ],

    // ── Facebook ────────────────────────────────────────────────
    // Console: https://developers.facebook.com → My Apps → Create App
    // Add OAuth Redirect URI: BASE_URL/oauth_callback.php?provider=facebook
    'facebook' => [
        'client_id'     => 'YOUR_FACEBOOK_APP_ID',
        'client_secret' => 'YOUR_FACEBOOK_APP_SECRET',
        'redirect_uri'  => BASE_URL . '/oauth_callback.php?provider=facebook',
        'auth_url'      => 'https://www.facebook.com/v18.0/dialog/oauth',
        'token_url'     => 'https://graph.facebook.com/v18.0/oauth/access_token',
        'userinfo_url'  => 'https://graph.facebook.com/me?fields=id,name,email',
        'scope'         => 'email',
    ],

    // ── GitHub ──────────────────────────────────────────────────
    // Console: https://github.com/settings/developers → OAuth Apps → New
    // Callback URL: BASE_URL/oauth_callback.php?provider=github
    'github' => [
        'client_id'     => 'YOUR_GITHUB_CLIENT_ID',
        'client_secret' => 'YOUR_GITHUB_CLIENT_SECRET',
        'redirect_uri'  => BASE_URL . '/oauth_callback.php?provider=github',
        'auth_url'      => 'https://github.com/login/oauth/authorize',
        'token_url'     => 'https://github.com/login/oauth/access_token',
        'userinfo_url'  => 'https://api.github.com/user',
        'scope'         => 'user:email',
    ],

    // ── LinkedIn ────────────────────────────────────────────────
    // Console: https://www.linkedin.com/developers/apps → Create App
    // Add Redirect URL: BASE_URL/oauth_callback.php?provider=linkedin
    'linkedin' => [
        'client_id'     => 'YOUR_LINKEDIN_CLIENT_ID',
        'client_secret' => 'YOUR_LINKEDIN_CLIENT_SECRET',
        'redirect_uri'  => BASE_URL . '/oauth_callback.php?provider=linkedin',
        'auth_url'      => 'https://www.linkedin.com/oauth/v2/authorization',
        'token_url'     => 'https://www.linkedin.com/oauth/v2/accessToken',
        'userinfo_url'  => 'https://api.linkedin.com/v2/userinfo',
        'scope'         => 'openid profile email',
    ],

];