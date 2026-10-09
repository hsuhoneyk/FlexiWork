<?php

require_once 'db.php';
session_start();

const MAX_ATTEMPTS = 3;
const LOCKOUT_MINUTES = 3;

$errors = [];

// Already logged in? Send them home.
if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {

        $errors[] = 'Please enter your email and password.';
    } else {

        // Find user
        $stmt = $pdo->prepare(
            'SELECT * FROM users WHERE email = ?'
        );

        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {

            // Do not reveal whether the email exists
            $errors[] = 'Incorrect email or password.';
        } else {

            /* ==========================================
               CHECK ACCOUNT LOCKOUT
               ========================================== */

            $locked = false;

            if (
                $user['failed_attempts'] >= MAX_ATTEMPTS &&
                !empty($user['last_failed_login'])
            ) {

                // Lockout expires 3 minutes after the 3rd failed attempt
                $lockedUntil =
                    strtotime($user['last_failed_login'])
                    + (LOCKOUT_MINUTES * 60);

                if (time() < $lockedUntil) {

                    $locked = true;

                    $secondsLeft = $lockedUntil - time();
                    $minutesLeft = ceil($secondsLeft / 60);

                    $errors[] =
                        "Too many failed attempts. "
                        . "Your account is locked. "
                        . "Try again in {$minutesLeft} minute(s).";
                } else {

                    /*
                     * Lockout has expired.
                     * Reset failed attempts so the user gets
                     * a fresh 3 attempts.
                     */

                    $resetStmt = $pdo->prepare(
                        'UPDATE users
                         SET failed_attempts = 0,
                             last_failed_login = NULL
                         WHERE id = ?'
                    );

                    $resetStmt->execute([$user['id']]);

                    // Update local value too
                    $user['failed_attempts'] = 0;
                    $user['last_failed_login'] = null;
                }
            }


            /* ==========================================
               LOGIN CHECK
               ========================================== */

            if (!$locked) {

                if (password_verify($password, $user['password'])) {

                    /*
         * LOGIN SUCCESS
         * Reset failed attempts
         */

                    $resetStmt = $pdo->prepare(
                        'UPDATE users
             SET failed_attempts = 0,
                 last_failed_login = NULL
             WHERE id = ?'
                    );

                    $resetStmt->execute([$user['id']]);

                    // Create a new session ID
                    session_regenerate_id(true);

                    // Store user information in session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['role'] = $user['role'];


                    /*
         * REDIRECT BASED ON USER ROLE
         */

                    if ($_SESSION['role'] === 'admin') {

                        // Admin → Admin Dashboard
                        header('Location: admin_dashboard.php');
                        exit;
                    } elseif ($_SESSION['role'] === 'employer') {

                        // Employer → Employer Dashboard
                        header('Location: employer_jobs.php');
                        exit;
                    } elseif ($_SESSION['role'] === 'jobseeker') {

                        // Job Seeker → Home
                        header('Location: index.php');
                        exit;
                    } else {

                        // Unknown role → Home
                        header('Location: index.php');
                        exit;
                    }
                } else {

                    /*
                     * WRONG PASSWORD
                     * Increase failed attempts
                     */

                    $newAttempts =
                        (int)$user['failed_attempts'] + 1;

                    $updateStmt = $pdo->prepare(
                        'UPDATE users
                         SET failed_attempts = ?,
                             last_failed_login = NOW()
                         WHERE id = ?'
                    );

                    $updateStmt->execute([
                        $newAttempts,
                        $user['id']
                    ]);


                    /* ==========================================
                       SHOW MESSAGE
                       ========================================== */

                    if ($newAttempts >= MAX_ATTEMPTS) {

                        $errors[] =
                            'Incorrect email or password. '
                            . 'You have reached 3 failed attempts. '
                            . 'Your account is now locked for 3 minutes.';
                    } else {

                        $remaining =
                            MAX_ATTEMPTS - $newAttempts;

                        $errors[] =
                            'Incorrect email or password. '
                            . "You have {$remaining} attempt(s) remaining.";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in — Flexiwork</title>

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
                        <h2>Good work, on your own schedule.</h2>
                        <p>Log in to find part-time shifts near you, or manage the shifts you've posted.</p>
                    </div>

                    <ul class="auth-points">
                        <li>
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 10.5l4 4 8-9" />
                            </svg>
                            Browse shifts by category and area
                        </li>
                        <li>
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 10.5l4 4 8-9" />
                            </svg>
                            Apply in a few taps
                        </li>
                        <li>
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 10.5l4 4 8-9" />
                            </svg>
                            Post a shift and hire quickly
                        </li>
                    </ul>
                </aside>

                <!-- Right: form -->
                <div class="auth-main">

                    <h1>Welcome back</h1>
                    <p class="auth-sub">Log in with your email and password.</p>

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

                        <div class="field">
                            <label for="password">Password</label>
                            <div class="password-wrap">
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    placeholder="Enter your password"
                                    autocomplete="current-password"
                                    required>
                                <button type="button" class="toggle-pass" aria-controls="password" aria-pressed="false">Show</button>
                            </div>
                        </div>

                        <button type="submit" class="auth-submit">Log in</button>
                    </form>

                    <p class="auth-switch">New to FlexiWork? <a href="register.php">Create an account</a></p>

                </div>
            </div>
        </section>
    </main>

    <script>
        // Show / hide password
        (function() {
            var btn = document.querySelector('.toggle-pass');
            var input = document.getElementById('password');
            if (!btn || !input) return;

            btn.addEventListener('click', function() {
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.textContent = show ? 'Hide' : 'Show';
                btn.setAttribute('aria-pressed', show ? 'true' : 'false');
            });
        })();
    </script>

</body>

</html>