<?php
session_start();
require 'db.php';

$page = 'contact';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);

    if ($name == '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message == '') {

        $error = 'Please fill in all fields with a valid email address.';
    } else {

        $user_id = $_SESSION['user_id'] ?? null;

        $stmt = $pdo->prepare("
    INSERT INTO contact (user_id, name, email, message)
    VALUES (?, ?, ?, ?)
");

        $stmt->execute([
            $user_id,
            $name,
            $email,
            $message
        ]);

        $_SESSION['flash'] = 'Thank you for reaching out. We will get back to you soon.';
        header('Location: contact.php');
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact us — Flexiwork</title>

    <link rel="stylesheet" href="style.css">
</head>

<body class="contact-page">

    <header>
        <nav class="wrap" aria-label="Main navigation">

            <a href="index.php" class="logo">Flexi<span class="chip">Work</span></a>

            <div class="nav-links">

                <?php if (!isset($_SESSION['user_id'])): ?>

                    <a href="index.php">Home</a>
                    <a href="categories.php">Find Jobs</a>
                    <a href="about.php">About</a>
                    <a href="contact.php" class="active">Contact Us</a>

                <?php elseif ($_SESSION['role'] === 'jobseeker'): ?>

                    <a href="index.php">Home</a>
                    <a href="categories.php">Find Jobs</a>
                    <a href="applications.php">My Applications</a>
                    <a href="about.php">About</a>
                    <a href="contact.php" class="active">Contact Us</a>
                    <a href="jobseeker_profile.php">Profile</a>

                <?php elseif ($_SESSION['role'] === 'employer'): ?>

                    <a href="employer_jobs.php">Dashboard</a>
                    <a href="employer_applications.php">Applicants</a>
                    <a href="Post_job.php">Post</a>
                    <a href="about.php">About</a>
                    <a href="contact.php" class="active">Contact Us</a>
                    <a href="employer_profile.php">Profile</a>

                <?php elseif ($_SESSION['role'] === 'admin'): ?>

                    <a href="admin_dashboard.php">Dashboard</a>
                    <a href="admin_categories.php">Categories</a>
                    <a href="admin_users.php">Users</a>
                    <a href="admin_settings.php">Settings</a>

                <?php endif; ?>

            </div>

            <div class="nav-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="user-pill">
                        Welcome, <?= htmlspecialchars($_SESSION['name'] ?? 'User') ?>
                    </span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="signin">Log in</a>
                    <a href="register.php" class="btn btn-primary">Get started</a>
                <?php endif; ?>
            </div>

            <button class="mobile-toggle" aria-label="Open menu" type="button">
                <span></span>
                <span></span>
                <span></span>
            </button>

        </nav>
    </header>

    <main>

        <!-- =====================================================
             CONTACT HERO
        ===================================================== -->
        <section class="contact-hero">
            <div class="contact-wrap">
                <div class="contact-hero-content">
                    <h1>Let's talk about <span>FlexiWork.</span></h1>

                    <p>
                        Have a question, need some help, or want to give
                        feedback? Send us a message and our team will get
                        back to you.
                    </p>
                </div>
            </div>
        </section>

        <!-- =====================================================
             CONTACT SECTION
        ===================================================== -->
        <section class="contact-section">
            <div class="contact-wrap contact-layout">

                <!-- CONTACT INFORMATION -->
                <div class="contact-info">

                    <div class="contact-heading">
                        <h2>We're here to help.</h2>
                        <p>
                            Whether you're looking for work or hiring your next
                            team member, feel free to reach out.
                        </p>
                    </div>

                    <div class="contact-cards">

                        <div class="contact-item">
                            <div class="contact-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="3" y="5" width="18" height="14" rx="2" />
                                    <path d="M4 6.5l8 6 8-6" />
                                </svg>
                            </div>
                            <div>
                                <strong>Email us</strong>
                                <span>support@flexiwork.mm</span>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M6 3h3l2 5-2.5 1.5a11 11 0 0 0 5 5L15 12l5 2v3a2 2 0 0 1-2 2C10.5 19 5 13.5 5 6a2 2 0 0 1 1-3z" />
                                </svg>
                            </div>
                            <div>
                                <strong>Call us</strong>
                                <span>+95 9 123 456 789</span>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 21s7-6.2 7-11.5A7 7 0 0 0 5 9.5C5 14.8 12 21 12 21z" />
                                    <circle cx="12" cy="9.5" r="2.5" />
                                </svg>
                            </div>
                            <div>
                                <strong>Visit us</strong>
                                <span>
                                    4th Floor, Junction Tower,<br>
                                    Yangon, Myanmar
                                </span>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="12" r="9" />
                                    <path d="M12 7v5l3 2" />
                                </svg>
                            </div>
                            <div>
                                <strong>Opening hours</strong>
                                <span>Monday – Friday, 9:00 AM – 6:00 PM</span>
                            </div>
                        </div>

                    </div>

                    <div class="contact-note">
                        <strong>Quick response</strong>
                        <p>
                            Our support team usually responds within one business day.
                        </p>
                    </div>

                </div>

                <!-- MESSAGE FORM -->
                <div class="contact-form-card">

                    <div class="form-heading">
                        <h2>How can we help?</h2>
                        <p>Fill in the form below and we'll be in touch.</p>
                    </div>

                    <?php if (!empty($_SESSION['flash'])): ?>
                        <div class="contact-alert contact-alert-success" role="status">
                            <?php echo htmlspecialchars($_SESSION['flash']);
                            unset($_SESSION['flash']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error != ''): ?>
                        <div class="contact-alert" role="alert">
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="10" cy="10" r="8" />
                                <path d="M10 6v5M10 14h.01" />
                            </svg>
                            <span><?php echo htmlspecialchars($error); ?></span>
                        </div>
                    <?php endif; ?>

                    <form action="contact.php" method="POST" class="contact-form">

                        <div class="field">
                            <label for="name">Full name</label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                placeholder="Your name"
                                autocomplete="name"
                                required>
                        </div>

                        <div class="field">
                            <label for="email">Email address</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="you@example.com"
                                autocomplete="email"
                                required>
                        </div>

                        <div class="field">
                            <label for="message">Message</label>
                            <textarea
                                id="message"
                                name="message"
                                rows="6"
                                placeholder="Tell us how we can help..."
                                required></textarea>
                        </div>

                        <button type="submit" class="contact-submit">Send message →</button>

                    </form>

                </div>

            </div>
        </section>

    </main>

    <footer>
        <div class="wrap">
            <div class="footer-grid">

                <div class="footer-brand">
                    <a href="index.php" class="footer-logo">Flexi<span class="chip">Work</span></a>
                    <p>
                        A part-time job marketplace connecting flexible workers with businesses that need reliable people.
                    </p>

                    <div class="footer-social">
                        <a href="https://www.facebook.com" aria-label="Facebook">f</a>
                        <a href="https://x.com" aria-label="X">𝕏</a>
                        <a href="https://www.instagram.com" aria-label="Instagram">◎</a>
                    </div>
                </div>

                <div class="footer-col">
                    <h4>For workers</h4>
                    <ul>
                        <li><a href="index.php">Find shifts</a></li>
                        <li><a href="categories.php">Categories</a></li>
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'jobseeker'): ?>
                            <li><a href="applications.php">My applications</a></li>
                        <?php else: ?>
                            <li><a href="register.php">Create account</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>For employers</h4>
                    <ul>
                        <li>
                            <a href="<?= isset($_SESSION['user_id']) && $_SESSION['role'] === 'employer' ? 'Post_job.php' : 'register.php' ?>">
                                Post a shift
                            </a>
                        </li>
                        <li><a href="<?= isset($_SESSION['user_id']) && $_SESSION['role'] === 'employer' ? 'employer_jobs.php' : 'register.php' ?>">
                                My Jobs
                            </a></li>
                        <li><a href="<?= isset($_SESSION['user_id']) && $_SESSION['role'] === 'employer' ? 'employer_applications.php' : 'register.php' ?>">
                                Applicants
                            </a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Company</h4>
                    <ul>
                        <li><a href="about.php">About</a></li>
                        <li><a href="contact.php">Contact Us</a></li>

                    </ul>
                </div>

            </div>

            <div class="footer-bottom">
                <span>© 2026 Flexiwork</span>
                <span><a href="privacy.php">Privacy</a> · Terms</span>
            </div>
        </div>
    </footer>

</body>

</html>