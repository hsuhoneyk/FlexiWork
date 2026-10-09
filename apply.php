<?php
require_once 'db.php';
session_start();

/* Only authenticated job seekers may apply. */
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
if (($_SESSION['role'] ?? '') !== 'jobseeker') {
    header('Location: index.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$jobId = (int)($_GET['job_id'] ?? $_POST['job_id'] ?? 0);
$errors = [];
$success = false;
$alreadyApplied = false;
$job = false;

if ($jobId <= 0) {
    $errors[] = 'Invalid job selected.';
} else {
    $stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ? AND status = 'open'");
    $stmt->execute([$jobId]);
    $job = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$job) {
        $errors[] = 'This job could not be found or is no longer open.';
    }
}

if ($job) {
    $stmt = $pdo->prepare('SELECT id FROM applications WHERE job_id = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$jobId, $userId]);
    $alreadyApplied = (bool)$stmt->fetchColumn();
}

/* Simple session token prevents accidental duplicate form submissions. */
if (empty($_SESSION['apply_token'])) {
    $_SESSION['apply_token'] = bin2hex(random_bytes(24));
}
$applyToken = $_SESSION['apply_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $job && !$alreadyApplied) {
    if (!hash_equals($_SESSION['apply_token'] ?? '', $_POST['apply_token'] ?? '')) {
        $errors[] = 'This form has expired. Please go back and try again.';
    } else {
        try {
            /* Re-check the job immediately before inserting. */
            $stmt = $pdo->prepare("SELECT id FROM jobs WHERE id = ? AND status = 'open'");
            $stmt->execute([$jobId]);
            if (!$stmt->fetchColumn()) {
                $errors[] = 'This job is no longer accepting applications.';
            } else {
                /* Check again to protect against repeated submissions. */
                $stmt = $pdo->prepare('SELECT id FROM applications WHERE job_id = ? AND user_id = ? LIMIT 1');
                $stmt->execute([$jobId, $userId]);
                if ($stmt->fetchColumn()) {
                    $alreadyApplied = true;
                } else {
                    $stmt = $pdo->prepare('INSERT INTO applications (job_id, user_id) VALUES (?, ?)');
                    $stmt->execute([$jobId, $userId]);
                    $success = true;
                    $alreadyApplied = true;
                    $_SESSION['apply_token'] = bin2hex(random_bytes(24));
                }
            }
        } catch (PDOException $e) {
            /* If the DB has a unique constraint, treat it as a duplicate instead of crashing. */
            if ((int)($e->errorInfo[1] ?? 0) === 1062) {
                $alreadyApplied = true;
            } else {
                $errors[] = 'We could not submit your application right now. Please try again.';
            }
        }
    }
}

function applyTime($time)
{
    return $time ? date('g:i A', strtotime($time)) : '';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply — Flexiwork</title>
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


    <main class="apply-page">
        <div class="wrap apply-grid">
            <section class="apply-card apply-intro">
                <div class="eyebrow">Application</div>
                <h1>One step closer to your next shift.</h1>
                <p>Review the job information below, then confirm your application. You can track its status from My Applications.</p><?php if ($job): ?><div class="job-name"><?= htmlspecialchars($job['title']) ?></div>
                    <div class="employer"><?= htmlspecialchars($job['employer_name']) ?></div>
                    <div class="apply-summary">
                        <div class="apply-row">
                            <small>Schedule</small>
                            <strong><?= htmlspecialchars($job['shift_day']) ?>
                                <br>
                                <?= htmlspecialchars(applyTime($job['shift_start'])) ?>
                                – <?= htmlspecialchars(applyTime($job['shift_end'])) ?>
                            </strong>
                        </div>

                        <div class="apply-row">
                            <small>Location</small>
                            <strong>
                                <?= htmlspecialchars($job['location']) ?>
                            </strong>
                        </div>
                        <div class="apply-row">
                            <small>Pay</small>
                            <strong>
                                <?= number_format((float)$job['pay_rate'], 0) ?> MMK/hr<?= $job['pay_note'] ? ' · ' . htmlspecialchars($job['pay_note']) : '' ?>
                            </strong>
                        </div>
                    </div><?php endif; ?>
            </section>

            <section class="apply-card right">
                <h2>Confirm your application</h2>
                <p>Make sure the details look right before submitting.</p>
                <?php if ($errors): ?>
                    <div class="notice error">
                        <strong>Unable to apply</strong>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li>
                                    <?= htmlspecialchars($error) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <a class="back" href="categories.php">← Back to jobs</a>

                <?php elseif ($success): ?>
                    <div class="notice success">
                        <h2>Application sent ✓</h2>
                        <p>Your application for <strong><?= htmlspecialchars($job['title']) ?></strong> at <?= htmlspecialchars($job['employer_name']) ?> has been submitted successfully.</p><a href="applications.php">View My Applications →</a>
                    </div><a class="back" href="categories.php">Find more jobs</a>
                <?php elseif ($alreadyApplied): ?>
                    <div class="notice success">
                        <strong>Already applied</strong>
                        <br>You have already submitted an application for this job.
                    </div>
                    <a class="back" href="applications.php">View My Applications →</a>
                    <a class="back" href="job_detail.php?id=<?= (int)$jobId ?>">Back to job details</a>
                <?php elseif ($job): ?>
                    <?php if (!empty($job['description'])): ?>
                        <div class="description">
                            <?= nl2br(htmlspecialchars($job['description'])) ?>
                        </div>
                    <?php endif; ?>
                    <form class="confirm" method="post" action="apply.php?job_id=<?= (int)$jobId ?>">
                        <input type="hidden" name="job_id" value="<?= (int)$jobId ?>">
                        <input type="hidden" name="apply_token" value="<?= htmlspecialchars($applyToken) ?>">
                        <button class="confirm-btn" type="submit">Confirm application →</button>
                    </form>
                    <a class="back" href="job_detail.php?id=<?= (int)$jobId ?>">← Review job details</a>
                <?php endif; ?>
            </section>
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