<?php
// privacy_policy.php
// Static legal page — no DB queries needed, but session started in case
// the navbar partial checks login state to render jobseeker/employer/admin links.
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy | Flexiwork</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <header>
        <nav class="wrap" aria-label="Main navigation">

            <a href="index.php" class="logo">Flexi<span class="chip">Work</span></a>

            <div class="nav-links">

                <?php if (!isset($_SESSION['user_id'])): ?>

                    <a href="index.php" class="active">Home</a>
                    <a href="categories.php">Find Jobs</a>
                    <a href="about.php">About</a>
                    <a href="contact.php">Contact Us</a>

                <?php elseif ($_SESSION['role'] === 'jobseeker'): ?>

                    <a href="index.php" class="active">Home</a>
                    <a href="categories.php">Find Jobs</a>
                    <a href="applications.php">My Applications</a>
                    <a href="about.php">About</a>
                    <a href="contact.php">Contact Us</a>
                    <a href="jobseeker_profile.php">Profile</a>

                <?php elseif ($_SESSION['role'] === 'employer'): ?>

                    <a href="employer_jobs.php" class="active">Dashboard</a>
                    <a href="employer_applications.php">Applicants</a>
                    <a href="Post_job.php">Post</a>
                    <a href="about.php">About</a>
                    <a href="contact.php">Contact Us</a>
                    <a href="employer_profile.php">Profile</a>

                <?php elseif ($_SESSION['role'] === 'admin'): ?>

                    <a href="admin_dashboard.php" class="active">Dashboard</a>
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

    <main class="wrap policy-page">

        <header class="policy-header">
            <div class="policy-header-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L4 5v6c0 5 3.4 9 8 11 4.6-2 8-6 8-11V5l-8-3z" stroke="#FDFDFA" stroke-width="1.6" stroke-linejoin="round" />
                </svg>
            </div>
            <div class="policy-header-text">
                <h1>Privacy Policy</h1>
                <p class="policy-updated">Last updated: <?php echo date('F j, Y'); ?></p>
            </div>
        </header>

        <section class="policy-block">
            <h2>1. Who We Are</h2>
            <p>Flexiwork ("we", "us", "our") operates a part-time job marketplace connecting jobseekers and employers in Yangon, Myanmar. This policy explains what information we collect, why we collect it, and how we protect it.</p>
        </section>

        <section class="policy-block">
            <h2>2. Information We Collect</h2>
            <ul class="policy-list">
                <li><strong>Account information</strong> — name, email address, phone number, and password (stored as a bcrypt hash) when you register as a jobseeker or employer.</li>
                <li><strong>Profile information</strong> — resume details, work experience, business name, and job listing content you choose to provide.</li>
                <li><strong>Usage data</strong> — pages visited, jobs viewed or applied to, and basic device/browser information collected automatically.</li>
                <li><strong>Cookies</strong> — small files stored in your browser to keep you logged in and remember your preferences. See Section 5.</li>
            </ul>
        </section>

        <section class="policy-block">
            <h2>3. How We Use Your Information</h2>
            <ul class="policy-list">
                <li>To create and manage your jobseeker, employer, or admin account.</li>
                <li>To match jobseekers with relevant job postings.</li>
                <li>To let employers review applications submitted to their listings.</li>
                <li>To keep the platform secure and prevent misuse.</li>
                <li>To communicate important account or job-related updates.</li>
            </ul>
        </section>

        <section class="policy-block">
            <h2>4. How We Share Your Information</h2>
            <p>We do not sell your personal data. Information is shared only as needed for the platform to function:</p>
            <ul class="policy-list">
                <li>Jobseeker application details are shared with the employer for the specific job applied to.</li>
                <li>Employer business and job listing details are shown publicly to jobseekers browsing the platform.</li>
                <li>We may disclose information if required by law.</li>
            </ul>
        </section>

        <section class="policy-block">
            <h2>5. Cookies</h2>
            <p>We use cookies for the following purposes:</p>
            <ul class="policy-list">
                <li><strong>Essential cookies</strong> — required to keep you logged in and maintain your session. These cannot be turned off.</li>
                <li><strong>Preference cookies</strong> — remember choices such as your cookie consent setting.</li>
            </ul>
            <p>You can control or delete cookies through your browser settings at any time. Blocking essential cookies may prevent parts of the site, such as login, from working correctly.</p>
        </section>

        <section class="policy-block">
            <h2>6. Data Retention</h2>
            <p>We keep your account information for as long as your account is active. If you delete your account, your personal data is removed within a reasonable period, except where we are required to retain records for legal reasons.</p>
        </section>

        <section class="policy-block">
            <h2>7. Your Rights</h2>
            <ul class="policy-list">
                <li>Access, update, or correct your profile information at any time through your account settings.</li>
                <li>Request deletion of your account and associated data.</li>
                <li>Withdraw cookie consent (essential cookies excluded) at any time.</li>
            </ul>
        </section>

        <section class="policy-block">
            <h2>8. Contact Us</h2>
            <p>Questions about this policy or your data can be sent to <a href="mailto:support@flexiwork.example">support@flexiwork.example</a>.</p>
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
                        <li><a href="employer_jobs.php">My jobs</a></li>
                        <li><a href="employer_applications.php">Applicants</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Company</h4>
                    <ul>
                        <li><a href="about.php">About</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                        <li><a href="index.php">How it works</a></li>
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