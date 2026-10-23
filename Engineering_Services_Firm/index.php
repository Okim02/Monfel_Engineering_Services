<?php
require_once __DIR__ . '/Includes/bootstrap.php';
require_once __DIR__ . '/Includes/Auth.php';

if (Auth::user()) {
    if (isset(Auth::user()['role']) && Auth::user()['role'] === 'owner') {
        redirect('dashboard.php');
    }
    Auth::logout();
}

$pageTitle = 'Welcome';
$isAuthPage = true;
require __DIR__ . '/Includes/header.php';
?>
<main class="auth-layout welcome-layout">
    <section class="auth-story">
        <a class="brand brand-light" href="index.php"><img class="brand-logo" src="Assets/images/back%20logo.png" alt="Monfel Engineering Services logo"><span><strong>Monfel</strong><small>Engineering Services</small></span></a>
        <div class="story-copy welcome-story-copy"><img class="welcome-hero-logo" src="Assets/images/front%20logo.png" alt="Monfel Engineering Services"></div>
    </section>
    <section class="auth-card welcome-card">
        <div class="auth-heading"><p class="eyebrow">Administrator access</p><h2>Welcome back</h2><p>Sign in to manage workforce operations, or create an administrator account.</p></div>
        <a class="button button-dark auth-submit" href="signin.php">Sign in <span>→</span></a>
        <a class="button button-outline welcome-signup" href="signup.php">Sign up as administrator <span>+</span></a>
        <p class="auth-note"><span class="lock-icon">✓</span> Administrator accounts only</p>
    </section>
</main>
<?php require __DIR__ . '/Includes/footer.php'; ?>
