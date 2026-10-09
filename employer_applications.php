<?php
require_once 'db.php';
session_start();

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header('Location: employer_applications.php');
    exit;
}

$employerId = $_SESSION['user_id'];
$jobFilter = (int) ($_GET['job_id'] ?? 0);

// ---- Handle accept / reject (POST only) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applicationId = (int) ($_POST['application_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if (in_array($action, ['accept', 'reject'], true)) {
        $newStatus = $action === 'accept' ? 'accepted' : 'declined';

        // Ownership check via a join: only update an application if the
        // related job actually belongs to this employer.
        $stmt = $pdo->prepare(
            "UPDATE applications a
             JOIN jobs j ON j.id = a.job_id
             SET a.status = ?
             WHERE a.id = ? AND j.employer_id = ?"
        );
        $stmt->execute([$newStatus, $applicationId, $employerId]);
    }

    header('Location: employer_applications.php' . ($jobFilter ? "?job_id=$jobFilter" : ''));
    exit;
}

// ---- Load applicants for this employer's jobs ----
$sql = "SELECT a.id AS application_id, a.status, a.applied_at,
               u.name AS applicant_name, u.email AS applicant_email, u.phone AS applicant_phone,
               j.id AS job_id, j.title AS job_title
        FROM applications a
        JOIN jobs j ON j.id = a.job_id
        JOIN users u ON u.id = a.user_id
        WHERE j.employer_id = ?";
$params = [$employerId];

if ($jobFilter) {
    $sql .= " AND j.id = ?";
    $params[] = $jobFilter;
}
$sql .= " ORDER BY a.applied_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll();

// For the filter dropdown.
$stmt = $pdo->prepare('SELECT id, title FROM jobs WHERE employer_id = ? ORDER BY created_at DESC');
$stmt->execute([$employerId]);
$myJobs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicants — Flexiwork</title>
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
                    <a href="categories.php">Find Jobs</a>
                    <a href="applications.php">My Applications</a>
                    <a href="jobseeker_profile.php">Profile</a>
                    <a href="about.php">About</a>
                    <a href="contact.php">Contact Us</a>

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
                    <h2>Applicants</h2>
                    <form method="get" class="filters-row">
                        <select name="job_id" onchange="this.form.submit()">
                            <option value="">All jobs</option>
                            <?php foreach ($myJobs as $j): ?>
                                <option value="<?= (int) $j['id'] ?>" <?= $jobFilter === (int) $j['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($j['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                <?php if (empty($applications)): ?>
                    <p>No applicants yet.</p>
                <?php else: ?>
                    <table class="job-table">
                        <thead>
                            <tr>
                                <th>Applicant</th>
                                <th>Job</th>
                                <th>Applied</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $app): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($app['applicant_name']) ?><br>
                                        <span class="subtext">
                                            <?= htmlspecialchars($app['applicant_email']) ?>
                                            <?= $app['applicant_phone'] ? ' · ' . htmlspecialchars($app['applicant_phone']) : '' ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($app['job_title']) ?></td>
                                    <td><?= date('M j, Y', strtotime($app['applied_at'])) ?></td>
                                    <td><span class="pill pill-<?= htmlspecialchars($app['status']) ?>"><?= htmlspecialchars(ucfirst($app['status'])) ?></span></td>
                                    <td>
                                        <?php if ($app['status'] === 'pending'): ?>
                                            <div class="row-actions">
                                                <form method="post">
                                                    <input type="hidden" name="application_id" value="<?= (int) $app['application_id'] ?>">
                                                    <input type="hidden" name="action" value="accept">
                                                    <button type="submit" class="btn btn-small">Accept</button>
                                                </form>
                                                <form method="post">
                                                    <input type="hidden" name="application_id" value="<?= (int) $app['application_id'] ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="btn-danger-text">Reject</button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
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