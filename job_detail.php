<?php
require_once 'db.php';
session_start();

$jobId = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM jobs WHERE id = ?');
$stmt->execute([$jobId]);
$job = $stmt->fetch();

$loggedIn = !empty($_SESSION['user_id']);

$alreadyApplied = false;
if ($job && $loggedIn && ($_SESSION['role'] ?? '') === 'jobseeker') {
    $stmt = $pdo->prepare('SELECT id FROM applications WHERE job_id = ? AND user_id = ?');
    $stmt->execute([$jobId, $_SESSION['user_id']]);
    $alreadyApplied = (bool) $stmt->fetch();
}

function formatTime($time): string
{
    if (!$time) {
        return '';
    }

    $timestamp = strtotime($time);
    return $timestamp ? date('g:i A', $timestamp) : htmlspecialchars($time);
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$role = $_SESSION['role'] ?? '';
$userName = $_SESSION['name'] ?? 'User';
$shiftStart = formatTime($job['shift_start'] ?? '');
$shiftEnd = formatTime($job['shift_end'] ?? '');
$shiftTime = trim($shiftStart . ($shiftStart && $shiftEnd ? ' – ' : '') . $shiftEnd);
$payRate = isset($job['pay_rate']) ? number_format((float) $job['pay_rate'], 0) : '0';
$status = strtolower((string) ($job['status'] ?? ''));
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $job ? e($job['title']) . ' — FlexiWork' : 'Job not found — FlexiWork' ?></title>
    <link rel="stylesheet" href="style.css">

</head>

<body>
    <header>
        <nav class="wrap" aria-label="Main navigation">

            <a href="index.php" class="logo">Flexi<span class="chip">Work</span></a>

            <div class="nav-links">

                <?php if (!isset($_SESSION['user_id'])): ?>

                    <a href="index.php" >Home</a>
                    <a href="categories.php" class="active">Find Jobs</a>
                    <a href="about.php">About</a>
                    <a href="contact.php">Contact Us</a>

                <?php elseif ($_SESSION['role'] === 'jobseeker'): ?>

                    <a href="index.php" >Home</a>
                    <a href="categories.php" class="active">Find Jobs</a>
                    <a href="applications.php">My Applications</a>
                    <a href="jobseeker_profile.php">Profile</a>

                <?php elseif ($_SESSION['role'] === 'employer'): ?>

                    <a href="employer_jobs.php">Dashboard</a>
                    <a href="employer_applications.php">Applicants</a>
                    <a href="Post_job.php">Post</a>
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



    <main class="page">
        <div class="wrap">
            <a href="categories.php" class="back-link">← Back to all jobs</a>

            <?php if (!$job): ?>
                <section class="card empty-card">
                    <div class="empty-icon">!</div>
                    <h1>Job not found</h1>
                    <p>This job could not be found. It may have been removed or the job link may be incorrect.</p>
                    <a href="index.php" class="btn">Browse available jobs</a>
                </section>
            <?php else: ?>
                <section class="job-hero">
                    <div class="job-hero-content">
                        <p class="eyebrow">Part-time opportunity</p>
                        <h1 class="job-title"><?= e($job['title']) ?></h1>
                        <div class="employer">by <?= e($job['employer_name']) ?></div>

                        <div class="hero-tags">
                            <?php if ($status): ?>
                                <span class="pill pill-<?= e($status) ?>"><?= e(ucfirst($status)) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($job['category'])): ?>
                                <span class="pill"><?= e($job['category']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($job['tag'])): ?>
                                <span class="pill"><?= e($job['tag']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>

                <div class="content-grid">
                    <section class="card section-card">
                        <p class="section-label">About the role</p>
                        <h2 class="section-title">Job Description</h2>

                        <?php if (!empty($job['description'])): ?>
                            <div class="description">
                                <?= nl2br(e($job['description'])) ?>
                            </div>
                        <?php else: ?>
                            <p class="description">The employer has not provided a detailed description for this position.</p>
                        <?php endif; ?>
                    </section>

                    <aside class="card summary-card">
                        <p class="section-label">Quick information</p>
                        <h2 class="summary-title">Job Summary</h2>

                        <div class="summary-list">
                            <?php if (!empty($job['category'])): ?>
                                <div class="summary-item">
                                    <div class="icon-box">▦</div>
                                    <div>
                                        <span class="summary-label">Category</span>
                                        <span class="summary-value"><?= e($job['category']) ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($job['location'])): ?>
                                <div class="summary-item">
                                    <div class="icon-box">⌖</div>
                                    <div>
                                        <span class="summary-label">Location</span>
                                        <span class="summary-value"><?= e($job['location']) ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($job['shift_day']) || $shiftTime): ?>
                                <div class="summary-item">
                                    <div class="icon-box">◷</div>
                                    <div>
                                        <span class="summary-label">Working Hours</span>
                                        <span class="summary-value">
                                            <?= !empty($job['shift_day']) ? e($job['shift_day']) : '' ?>
                                            <?= !empty($job['shift_day']) && $shiftTime ? '<br>' : '' ?>
                                            <?= e($shiftTime) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="summary-item">
                                <div class="icon-box">$</div>
                                <div>
                                    <span class="summary-label">Pay Rate</span>
                                    <span class="summary-value pay-value"><?= e($payRate) ?>/hr</span>
                                    <?php if (!empty($job['pay_note'])): ?>
                                        <span class="summary-label"><?= e($job['pay_note']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="action-box">
                            <?php if ($status !== 'open'): ?>
                                <div class="notice notice-muted">This job is no longer accepting applications.</div>
                            <?php elseif ($alreadyApplied): ?>
                                <div class="notice notice-success">You've already applied to this job.</div>
                            <?php elseif ($loggedIn && $role === 'jobseeker'): ?>
                                <a href="apply.php?job_id=<?= (int) $job['id'] ?>" class="btn btn-large">Apply for this job</a>
                                <p class="apply-note">Your application will be sent to the employer.</p>
                            <?php elseif ($loggedIn && $role === 'employer'): ?>
                                <div class="notice notice-muted">Employer accounts can't apply to jobs.</div>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-large">Log in to apply</a>
                                <p class="apply-note">You need a job seeker account to apply.</p>
                            <?php endif; ?>
                        </div>
                    </aside>
                </div>
            <?php endif; ?>
        </div>
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