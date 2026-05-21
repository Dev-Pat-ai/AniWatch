<?php
// ═══════════════════════════════════════════════════════════════
//  oauth_config.php  —  Fill in YOUR credentials below.
//
//  BASE_URL: change to your InfinityFree subdomain, e.g.
//    https://yoursite.infinityfreeapp.com/aniwatch
//    or a custom domain if you have one.
// ═══════════════════════════════════════════════════════════════

define('BASE_URL', 'http://localhost/Activity4/aniwatch');

return [

    // ── Google ──────────────────────────────────────────────────
    // https://console.cloud.google.com → APIs & Services → Credentials
    // Authorised redirect URI: BASE_URL/oauth_callback.php?provider=google
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
    // https://developers.facebook.com → My Apps → Create App → Consumer
    // Valid OAuth Redirect URI: BASE_URL/oauth_callback.php?provider=facebook
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
    // https://github.com/settings/developers → OAuth Apps → New OAuth App
    // Callback URL: BASE_URL/oauth_callback.php?provider=github
    'github' => [
        'client_id'     => 'Ov23liVF3VyWm6HEE0A6',
        'client_secret' => 'b39c50df58a5eeaa98cc64d90058434e9c7923c4',
        'redirect_uri'  => BASE_URL . '/oauth_callback.php?provider=github',
        'auth_url'      => 'https://github.com/login/oauth/authorize',
        'token_url'     => 'https://github.com/login/oauth/access_token',
        'userinfo_url'  => 'https://api.github.com/user',
        'scope'         => 'user:email',
    ],

    // ── LinkedIn ────────────────────────────────────────────────
    // https://www.linkedin.com/developers/apps → Create App
    // Authorized redirect URL: BASE_URL/oauth_callback.php?provider=linkedin
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