<?php
require_once __DIR__ . '/Includes/bootstrap.php';
require_once __DIR__ . '/Includes/Auth.php';

if (Auth::user() && isset(Auth::user()['role']) && Auth::user()['role'] === 'owner') redirect('dashboard.php');
$error = null;
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim(isset($_POST['email']) ? $_POST['email'] : ''));
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    if (Auth::login($email, $password)) redirect('dashboard.php');
    $error = 'We could not sign you in with those administrator credentials.';
}

$pageTitle = 'Administrator sign in';
$isAuthPage = true;
require __DIR__ . '/Includes/header.php';
?>
<main class="auth-layout">
    <section class="auth-story">
        <a class="brand brand-light" href="index.php"><img class="brand-logo" src="Assets/images/back%20logo.png" alt="Monfel Engineering Services logo"><span><strong>Monfel</strong><small>Engineering Services</small></span></a>
        <div class="story-copy"><p class="eyebrow">Administrator access</p><h1>Good to see <em>you.</em></h1><p>Sign in to review your workforce and keep operations moving.</p></div>
        <div class="story-footer"><span>ENGINEERING SERVICES FIRM</span><span>SECURE SIGN IN</span></div>
    </section>
    <section class="auth-card">
        <div class="auth-heading"><p class="eyebrow">Admin portal</p><h2>Sign in</h2><p>Use your administrator email and password.</p></div>
        <?php if ($error): ?><p class="error" role="alert"><?= e($error) ?></p><?php endif; ?>
        <form method="post" autocomplete="on">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <label for="email">Email</label><input id="email" type="email" name="email" value="<?= e($email) ?>" autocomplete="username" required>
            <label for="password">Password</label><input id="password" type="password" name="password" autocomplete="current-password" required>
            <button class="button button-dark auth-submit" type="submit">Sign in <span>→</span></button>
        </form>
        <p class="auth-note">Need administrator access? <a href="signup.php">Create an account</a></p>
    </section>
</main>
<?php require __DIR__ . '/Includes/footer.php'; ?>
