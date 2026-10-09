<?php
require_once 'db.php';
session_start();

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header('Location: employer_jobs.php');
    exit;
}

$employerId = $_SESSION['user_id'];

// ---- Handle delete (POST only) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $jobId = (int) ($_POST['job_id'] ?? 0);

    // Ownership check: only delete jobs that belong to this employer.
    $stmt = $pdo->prepare('DELETE FROM jobs WHERE id = ? AND employer_id = ?');
    $stmt->execute([$jobId, $employerId]);

    header('Location: employer_jobs.php');
    exit;
}

// ---- Load this employer's jobs, with an applicant count per job ----
$stmt = $pdo->prepare(
    "SELECT j.*,
            (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) AS applicant_count
     FROM jobs j
     WHERE j.employer_id = ?
     ORDER BY j.created_at DESC"
);
$stmt->execute([$employerId]);
$jobs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My jobs — Flexiwork</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <header>
        <nav class="wrap" aria-label="Main navigation">

            <a href="index.php" class="logo">Flexi<span class="chip">Work</span></a>

            <div class="nav-links">

                <?php if (!isset($_SESSION['user_id'])): ?>

                    <a href="index.php">Home</a>
                    <a href="categories.php" class="active">Find Jobs</a>
                    <a href="about.php">About</a>
                    <a href="contact.php">Contact Us</a>

                <?php elseif ($_SESSION['role'] === 'jobseeker'): ?>

                    <a href="index.php">Home</a>
                    <a href="categories.php" class="active">Find Jobs</a>
                    <a href="applications.php">My Applications</a>
                    <a href="jobseeker_profile.php">Profile</a>
                    <a href="about.php">About</a>
                    <a href="contact.php">Contact Us</a>

                <?php elseif ($_SESSION['role'] === 'employer'): ?>

                    <a href="employer_jobs.php" class="active">Dashboard</a>
                    <a href="employer_applications.php">Applicants</a>
                    <a href="Post_job.php" >Post</a>
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
                <div class="row-header">
                    <h2>My jobs</h2>
                    <a href="Post_job.php" class="btn">Post a job</a>
                </div>

                <?php if (empty($jobs)): ?>
                    <p>You haven't posted any jobs yet. <a class="link" href="Post_job.php">Post your first job</a>.</p>
                <?php else: ?>
                    <table class="job-table">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Day / time</th>
                                <th>Pay</th>
                                <th>Status</th>
                                <th>Applicants</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jobs as $job): ?>
                                <tr>
                                    <td><?= htmlspecialchars($job['title']) ?></td>
                                    <td>
                                        <?= htmlspecialchars($job['shift_day']) ?>,
                                        <?= date('g:i A', strtotime($job['shift_start'])) ?>–<?= date('g:i A', strtotime($job['shift_end'])) ?>
                                    </td>
                                    <td><?= number_format($job['pay_rate'], 0) ?>/hr</td>
                                    <td><span class="pill pill-<?= htmlspecialchars($job['status']) ?>"><?= htmlspecialchars(ucfirst($job['status'])) ?></span></td>
                                    <td>
                                        <a class="link" href="employer_applications.php?job_id=<?= (int) $job['id'] ?>">
                                            <?= (int) $job['applicant_count'] ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="row-actions">
                                            <a class="link" href="edit_job.php?id=<?= (int) $job['id'] ?>">Edit</a>
                                            <form method="post" onsubmit="return confirm('Delete this job posting? This cannot be undone.');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="job_id" value="<?= (int) $job['id'] ?>">
                                                <button type="submit" class="btn-danger-text">Delete</button>
                                            </form>
                                        </div>
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