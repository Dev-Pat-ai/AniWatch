<?php
// ============================================================
//  login.php  |  Login & Register — with OAuth social buttons
// ============================================================

session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'db.php';

$error   = '';
$success = '';
$active  = '';

// ── OAuth error messages passed back via ?error= ─────────────
$oauthErrors = [
    'oauth_failed'     => 'OAuth sign-in failed. Please try again.',
    'token_failed'     => 'Could not get access token. Try again.',
    'no_email'         => 'Your account has no public email. Use a different method.',
    'invalid_provider' => 'Unknown sign-in provider.',
];
if (!empty($_GET['error']) && isset($oauthErrors[$_GET['error']])) {
    $error = $oauthErrors[$_GET['error']];
}

// ── Handle REGISTER ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $active   = 'active';
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password =       $_POST['password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);

        if ($stmt->fetch()) {
            $error = "Username or email is already taken.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $insert->execute([$username, $email, $hashed]);

            $success = "Account created! You can now log in.";
            $active  = '';
        }
    }
}

// ── Handle LOGIN ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password =       $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && $user['password'] !== null && password_verify($password, $user['password'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'] ?? 'user';
            header("Location: index.php");
            exit();
        } elseif ($user && $user['password'] === null) {
            // OAuth-only account — no password set
            $error = "This account uses social login. Please use the social button below.";
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AniWatch PH – Login / Register</title>
    <link rel="stylesheet" href="login_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="container <?= htmlspecialchars($active) ?>">

    <!-- ── Login Form ── -->
    <div class="form-box login">
        <form method="POST" action="login.php">
            <input type="hidden" name="action" value="login">
            <h1>Login</h1>

            <?php if (!empty($error) && empty($active)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <div class="input-box">
                <input type="text" name="username" placeholder="Username" required>
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
                <i class="fa-solid fa-lock"></i>
            </div>
            <div class="forgot-link">
                <a href="#">Forgot Password?</a>
            </div>
            <button type="submit" class="btn">Login</button>
            <p class="social-icon">Or Login with social platforms</p>

            <!-- ── OAuth Social Buttons ── -->
            <div class="social-icons">
                <a href="oauth_init.php?provider=google"   title="Login with Google">
                    <i class="fa-brands fa-google"></i>
                </a>
                <a href="oauth_init.php?provider=facebook" title="Login with Facebook">
                    <i class="fa-brands fa-facebook-f"></i>
                </a>
                <a href="oauth_init.php?provider=github"   title="Login with GitHub">
                    <i class="fa-brands fa-github"></i>
                </a>
                <a href="oauth_init.php?provider=linkedin" title="Login with LinkedIn">
                    <i class="fa-brands fa-linkedin-in"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- ── Register Form ── -->
    <div class="form-box register">
        <form method="POST" action="login.php">
            <input type="hidden" name="action" value="register">
            <h1>Registration</h1>

            <?php if (!empty($error) && !empty($active)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="input-box">
                <input type="text" name="username" placeholder="Username" required
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="input-box">
                <input type="email" name="email" placeholder="Email" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                <i class="fa-solid fa-envelope"></i>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
                <i class="fa-solid fa-lock"></i>
            </div>
            <button type="submit" class="btn">Register</button>
            <p class="social-icon">Or Register with social platforms</p>

            <!-- ── OAuth Social Buttons (same endpoints — auto-registers new users) ── -->
            <div class="social-icons">
                <a href="oauth_init.php?provider=google"   title="Register with Google">
                    <i class="fa-brands fa-google"></i>
                </a>
                <a href="oauth_init.php?provider=facebook" title="Register with Facebook">
                    <i class="fa-brands fa-facebook-f"></i>
                </a>
                <a href="oauth_init.php?provider=github"   title="Register with GitHub">
                    <i class="fa-brands fa-github"></i>
                </a>
                <a href="oauth_init.php?provider=linkedin" title="Register with LinkedIn">
                    <i class="fa-brands fa-linkedin-in"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- ── Toggle Box ── -->
    <div class="toggle-box">
        <div class="toggle-panel toggle-left">
            <div class="site-logo">ANI<span>WATCH</span> PH</div>
            <h1>Hello, Nakama!</h1>
            <p>Don't have an account yet?<br>Join us and start watching!</p>
            <button class="btn register-btn">Register</button>
        </div>
        <div class="toggle-panel toggle-right">
            <div class="site-logo">ANI<span>WATCH</span> PH</div>
            <h1>Welcome Back!</h1>
            <p>Already have an account?<br>Login to continue watching.</p>
            <button class="btn login-btn">Login</button>
        </div>
    </div>

</div>

<script>
    const container   = document.querySelector('.container');
    const registerBtn = document.querySelector('.register-btn');
    const loginBtn    = document.querySelector('.login-btn');

    registerBtn.addEventListener('click', () => container.classList.add('active'));
    loginBtn.addEventListener('click',    () => container.classList.remove('active'));
</script>
</body>
</html>
