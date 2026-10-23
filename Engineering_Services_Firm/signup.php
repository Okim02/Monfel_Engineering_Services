<?php
require_once __DIR__ . '/Includes/bootstrap.php';
require_once __DIR__ . '/Includes/Auth.php';

if (Auth::user() && isset(Auth::user()['role']) && Auth::user()['role'] === 'owner') redirect('dashboard.php');
$error = null;
$fullName = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $fullName = trim(isset($_POST['full_name']) ? $_POST['full_name'] : '');
    $email = strtolower(trim(isset($_POST['email']) ? $_POST['email'] : ''));
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    if ($fullName === '' || strlen($fullName) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter your name and a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Choose a password with at least 8 characters.';
    } elseif ($password !== $confirmPassword) {
        $error = 'The passwords do not match.';
    } else {
        try {
            Auth::registerOwner($fullName, $email, $password);
            if (Auth::login($email, $password)) redirect('dashboard.php');
            $error = 'The account was created, but sign in failed. Please sign in.';
        } catch (PDOException $exception) {
            $error = 'An account with that email may already exist. Check the address and try signing in.';
        }
    }
}

$pageTitle = 'Administrator sign up';
$isAuthPage = true;
require __DIR__ . '/Includes/header.php';
?>
<main class="auth-layout">
    <section class="auth-story">
        <a class="brand brand-light" href="index.php"><img class="brand-logo" src="Assets/images/back%20logo.png" alt="Monfel Engineering Services logo"><span><strong>Monfel</strong><small>Engineering Services</small></span></a>
        <div class="story-copy"><p class="eyebrow">Administrator access</p><h1>Build a better <em>workday.</em></h1><p>Create an administrator account to manage your team, attendance, and payroll.</p></div>
        <div class="story-footer"><span>ENGINEERING SERVICES FIRM</span><span>ADMIN SETUP</span></div>
    </section>
    <section class="auth-card">
        <div class="auth-heading"><p class="eyebrow">Admin portal</p><h2>Create account</h2><p>Administrator access is assigned to this account.</p></div>
        <?php if ($error): ?><p class="error" role="alert"><?= e($error) ?></p><?php endif; ?>
        <form method="post" autocomplete="on">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <label for="full_name">Full name</label><input id="full_name" type="text" name="full_name" value="<?= e($fullName) ?>" maxlength="150" autocomplete="name" required>
            <label for="email">Email</label><input id="email" type="email" name="email" value="<?= e($email) ?>" autocomplete="email" required>
            <label for="password">Password</label><input id="password" type="password" name="password" minlength="8" autocomplete="new-password" required>
            <label for="confirm_password">Confirm password</label><input id="confirm_password" type="password" name="confirm_password" minlength="8" autocomplete="new-password" required>
            <button class="button button-dark auth-submit" type="submit">Create administrator account <span>→</span></button>
        </form>
        <p class="auth-note">Already have access? <a href="signin.php">Sign in</a></p>
    </section>
</main>
<?php require __DIR__ . '/Includes/footer.php'; ?>
