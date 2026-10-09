<?php
require_once 'db.php';
session_start();

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'jobseeker') {
    header('Location: applications.php');
    exit;
}

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare(
    "SELECT a.status, a.applied_at,
            j.id AS job_id, j.title, j.employer_name, j.pay_rate, j.pay_note,
            j.shift_day, j.shift_start, j.shift_end, j.location, j.status AS job_status
     FROM applications a
     JOIN jobs j ON j.id = a.job_id
     WHERE a.user_id = ?
     ORDER BY a.applied_at DESC"
);
$stmt->execute([$userId]);
$applications = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My applications — Flexiwork</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <header>
        <nav class="wrap" aria-label="Main navigation">

            <a href="index.php" class="logo">Flexi<span class="chip">Work</span></a>

            <div class="nav-links">

                <?php if (!isset($_SESSION['user_id'])): ?>

                    <a href="index.php">Home</a>
                    <a href="categories.php" >Find Jobs</a>
                    <a href="about.php">About</a>
                    <a href="contatct.php">Contact Us</a>

                <?php elseif ($_SESSION['role'] === 'jobseeker'): ?>

                    <a href="index.php">Home</a>
                    <a href="categories.php" >Find Jobs</a>
                    <a href="applications.php" class="active">My Applications</a>
                    <a href="about.php">About</a>
                    <a href="contact.php">Contact Us</a>
                    <a href="jobseeker_profile.php">Profile</a>

                <?php elseif ($_SESSION['role'] === 'employer'): ?>

                    <a href="employer_jobs.php">Dashboard</a>
                    <a href="employer_applications.php" class="active">Applicants</a>
                    <a href="Post_job.php">Post</a>
                    <a href="about.php">About</a>
                    <a href="contact.php">Contact Us</a>
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
        <section class="block">
            <div class="wrap">
                <div class="section-header">
                    <h2>My applications </h2>
                </div>
                
                <?php if (empty($applications)): ?>
                    <p>You haven't applied to any jobs yet. <a class="link" href="index.php">Browse open jobs</a>.</p>
                <?php else: ?>

                    <table class="job-table">

                        <thead>
                            <tr>
                                <th>Job</th>
                                <th>Employer</th>
                                <th>Day / time</th>
                                <th>Pay</th>
                                <th>Applied</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($applications as $app): ?>
                                <tr>
                                    <td>
                                        <a class="link" href="job.php?id=<?= (int) $app['job_id'] ?>">
                                            <?= htmlspecialchars($app['title']) ?>
                                        </a>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($app['employer_name']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($app['shift_day']) ?>,
                                        <?= date('g:i A', strtotime($app['shift_start'])) ?>–<?= date('g:i A', strtotime($app['shift_end'])) ?>
                                    </td>

                                    <td>
                                        <?= number_format($app['pay_rate'], 0) ?>/hr
                                        <?= $app['pay_note'] ? ' ' . htmlspecialchars($app['pay_note']) : '' ?>
                                    </td>

                                    <td>
                                        <?= date('M j, Y', strtotime($app['applied_at'])) ?>
                                    </td>

                                    <td>
                                        <span class="pill pill-<?= htmlspecialchars($app['status']) ?>">
                                            <?= htmlspecialchars(ucfirst($app['status'])) ?>
                                        </span>
                                    </td>
                                </tr>

                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
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