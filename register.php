<?php
require_once 'db.php';
session_start();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'jobseeker';

    // ---- Validation ----
    if ($name === '') {
        $errors[] = 'Please enter your name.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if (!in_array($role, ['jobseeker', 'employer'], true)) {
        $errors[] = 'Please choose a valid account type.';
    }

    // ---- Check email isn't already registered ----
    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with that email already exists.';
        }
    }

    // ---- Create account ----
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare(
            'INSERT INTO users (name, email, password, role, created_at)
             VALUES (?, ?, ?, ?, NOW())'
        );
        $stmt->execute([$name, $email, $hash, $role]);

        $success = true;
    }
}

// Which account type is selected (defaults to job seeker)
$selectedRole = ($_POST['role'] ?? 'jobseeker') === 'employer' ? 'employer' : 'jobseeker';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create an account — Flexiwork</title>

    <!-- Fonts: Newsreader (classic serif) + Inter (modern sans) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <link rel="stylesheet" href="style.css">
</head>

<body class="auth-page">

    <header>
        <nav class="wrap">
            <a href="index.php" class="logo">Flexi<span class="chip">Work</span></a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="categories.php">Find Jobs</a></li>

            </ul>

            <div class="nav-actions">
                <a href="login.php" class="signin">Log in</a>
                <a href="register.php" class="btn btn-primary">Post a shift</a>
            </div>

            <button class="mobile-toggle" aria-label="Open menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </nav>
    </header>

    <main>
        <section class="auth-shell">
            <div class="auth-card">

                <!-- Left: brand panel -->
                <aside class="auth-aside">
                    <div>
                        <h2>Your next shift starts here.</h2>
                        <p>Create a free account to find part-time work, or to post shifts and hire people fast.</p>
                    </div>

                    <ul class="auth-points">
                        <li>
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 10.5l4 4 8-9" />
                            </svg>
                            Free to sign up
                        </li>
                        <li>
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 10.5l4 4 8-9" />
                            </svg>
                            Choose to look for shifts or post them
                        </li>
                        <li>
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 10.5l4 4 8-9" />
                            </svg>
                            Takes less than a minute
                        </li>
                    </ul>
                </aside>

                <!-- Right: form -->
                <div class="auth-main">

                    <h1>Create an account</h1>

                    <?php if ($success): ?>

                        <p class="auth-success" role="status">
                            Your account has been created. You can now
                            <a href="login.php">log in</a>.
                        </p>

                    <?php else: ?>

                        <p class="auth-sub">It only takes a minute to get started.</p>

                        <?php if (!empty($errors)): ?>
                            <div class="auth-alert" role="alert">
                                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="10" cy="10" r="8" />
                                    <path d="M10 6v5M10 14h.01" />
                                </svg>
                                <ul>
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="post" class="auth-form" novalidate>

                            <fieldset class="role-picker">
                                <legend>I am a</legend>

                                <label class="role-option">
                                    <input type="radio" name="role" value="jobseeker" <?= $selectedRole === 'jobseeker' ? 'checked' : '' ?>>
                                    <span>
                                        <strong>Job seeker</strong>
                                        <small>(Looking for shifts)</small>
                                    </span>
                                </label> <br>

                                <label class="role-option">
                                    <input type="radio" name="role" value="employer" <?= $selectedRole === 'employer' ? 'checked' : '' ?>>
                                    <span>
                                        <strong>Employer</strong>
                                        <small>(Posting shifts)</small>
                                    </span>
                                </label>
                            </fieldset>

                            <div class="field">
                                <label for="name">Full name</label>
                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    placeholder="Your full name"
                                    autocomplete="name"
                                    value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                                    required>
                            </div>

                            <div class="field">
                                <label for="email">Email</label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    placeholder="you@example.com"
                                    autocomplete="email"
                                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                    required>
                            </div>

                            <div class="field-row">
                                <div class="field">
                                    <label for="password">Password</label>
                                    <div class="password-wrap">
                                        <input
                                            type="password"
                                            id="password"
                                            name="password"
                                            placeholder="8+ characters"
                                            autocomplete="new-password"
                                            required
                                            minlength="8">
                                        <button type="button" class="toggle-pass" aria-controls="password" aria-pressed="false">Show</button>
                                    </div>
                                </div>

                            </div>

                            <button type="submit" class="auth-submit">Create account</button>
                        </form>

                        <p class="auth-switch">Already have an account? <a href="login.php">Log in</a></p>

                    <?php endif; ?>

                </div>
            </div>
        </section>
    </main>

    <script>
        // Show / hide password (works for every password field on the page)
        (function() {
            var buttons = document.querySelectorAll('.toggle-pass');
            buttons.forEach(function(btn) {
                var input = document.getElementById(btn.getAttribute('aria-controls'));
                if (!input) return;

                btn.addEventListener('click', function() {
                    var show = input.type === 'password';
                    input.type = show ? 'text' : 'password';
                    btn.textContent = show ? 'Hide' : 'Show';
                    btn.setAttribute('aria-pressed', show ? 'true' : 'false');
                });
            });
        })();
    </script>

</body>

</html>