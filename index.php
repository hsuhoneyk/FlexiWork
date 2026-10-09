
<?php
require_once 'db.php';
session_start();

/* =====================================================
   FLEXIWORK HOMEPAGE - DATABASE CONNECTION
   Loads live categories and open jobs from MySQL.
===================================================== */

// Search/filter values
$search = trim($_GET['search'] ?? '');
$categoryFilter = trim($_GET['category'] ?? '');

// Load categories from database
$categories = [];

try {
    $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}


// =====================================================
// LOAD OPEN JOBS
// =====================================================

$sql = "SELECT 
            j.id,
            j.title,
            j.employer_name,
            c.name AS category_name,
            j.description,
            j.pay_rate,
            j.pay_note,
            j.shift_day,
            j.shift_start,
            j.shift_end,
            j.location,
            j.tag,
            j.status,
            j.created_at
        FROM jobs j
        INNER JOIN categories c ON j.category_id = c.id
        WHERE j.status = 'open'";

$params = [];


// Search filter
if ($search !== '') {

    $sql .= " AND (
                j.title LIKE ?
                OR j.employer_name LIKE ?
                OR j.location LIKE ?
                OR c.name LIKE ?
              )";

    $term = '%' . $search . '%';

    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}


// Category filter
if ($categoryFilter !== '') {

    $sql .= " AND c.name = ?";

    $params[] = $categoryFilter;
}


$sql .= " ORDER BY j.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);


// =====================================================
// HOMEPAGE STATISTICS
// =====================================================

$openJobsCount = (int) $pdo
    ->query("SELECT COUNT(*) FROM jobs WHERE status = 'open'")
    ->fetchColumn();

$employerCount = (int) $pdo
    ->query("SELECT COUNT(*) FROM users WHERE role = 'employer'")
    ->fetchColumn();

$jobseekerCount = (int) $pdo
    ->query("SELECT COUNT(*) FROM users WHERE role = 'jobseeker'")
    ->fetchColumn();


// =====================================================
// CATEGORY JOB COUNTS
// =====================================================

$categoryCounts = [];

foreach ($categories as $cat) {

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM jobs
        WHERE category_id = ?
        AND status = 'open'
    ");

    $stmt->execute([$cat['id']]);

    $categoryCounts[$cat['id']] = (int) $stmt->fetchColumn();
}


// =====================================================
// SMALL HELPER FOR TIME FORMATTING
// =====================================================

function formatJobTime($time)
{
    if (!$time) {
        return '';
    }

    return date('g:i A', strtotime($time));
}


// =====================================================
// SMALL HELPER FOR TAG LABEL
// =====================================================

function tagLabel($tag)
{
    switch ($tag) {

        case 'today':
            return 'Today';

        case 'weekend':
            return 'This weekend';

        case 'multi':
            return 'Multiple shifts';

        default:
            return '';
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Flexiwork — Part-time work, your way</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

    <header>

        <nav class="wrap" aria-label="Main navigation">

            <a href="index.php" class="logo">
                Flexi<span class="chip">Work</span>
            </a>


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

                    <a href="logout.php" class="logout-btn">
                        Logout
                    </a>

                <?php else: ?>

                    <a href="login.php" class="signin">
                        Log in
                    </a>

                    <a href="register.php" class="btn btn-primary">
                        Get started
                    </a>

                <?php endif; ?>

            </div>


            <button
                class="mobile-toggle"
                aria-label="Open menu"
                type="button">

                <span></span>
                <span></span>
                <span></span>

            </button>

        </nav>

    </header>


    <?php include 'cookie.php'; ?>


    <main>


        <!-- =====================================================
             HERO
        ===================================================== -->

        <section class="hero">

            <div class="wrap">

                <div>

                    <h1>
                        Fill your week around your life,
                        not the other way around.
                    </h1>

                    <p class="lede">
                        Browse part-time and one-off shifts in retail,
                        hospitality, delivery and more.
                        Find work that fits your schedule and apply quickly.
                    </p>


                    <form
                        class="search-panel"
                        method="get"
                        action="index.php">

                        <input
                            type="text"
                            name="search"
                            value="<?= htmlspecialchars($search) ?>"
                            placeholder="Job title, business or location"
                            aria-label="Search jobs">


                        <select
                            name="category"
                            aria-label="Category">

                            <option value="">
                                Any category
                            </option>

                            <?php foreach ($categories as $cat): ?>

                                <option
                                    value="<?= htmlspecialchars($cat['name']) ?>"
                                    <?= $categoryFilter === $cat['name'] ? 'selected' : '' ?>>

                                    <?= htmlspecialchars($cat['name']) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>


                        <button
                            type="submit"
                            class="btn btn-primary">

                            Find shifts

                        </button>

                    </form>


                    <p class="fineprint">

                        <?= $openJobsCount ?>
                        open shift<?= $openJobsCount === 1 ? '' : 's' ?>
                        available now.

                    </p>

                </div>


                <div class="schedule-card">

                    <div class="schedule-head">

                        <span class="title">
                            Your week
                        </span>

                        <span class="week mono">
                            Flexible shifts
                        </span>

                    </div>


                    <div class="schedule-grid">

                        <div class="hd"></div>
                        <div class="hd">Mon</div>
                        <div class="hd">Tue</div>
                        <div class="hd">Wed</div>
                        <div class="hd">Thu</div>
                        <div class="hd">Fri</div>
                        <div class="hd">Sat</div>
                        <div class="hd">Sun</div>


                        <div class="hourlabel">8a</div>

                        <div class="cell"></div>
                        <div class="cell"></div>
                        <div class="cell"></div>
                        <div class="cell"></div>
                        <div class="cell"></div>

                        <div class="cell">

                            <div class="shift-block shift-amber shift-fit-a">

                                <span class="role">
                                    Morning shift
                                </span>

                                <span class="pay">
                                    Flexible work
                                </span>

                            </div>

                        </div>

                        <div class="cell"></div>


                        <div class="hourlabel">12p</div>

                        <div class="cell"></div>

                        <div class="cell">

                            <div class="shift-block shift-teal shift-fit-b">

                                <span class="role">
                                    Part-time
                                </span>

                                <span class="pay">
                                    Your schedule
                                </span>

                            </div>

                        </div>

                        <div class="cell"></div>
                        <div class="cell"></div>
                        <div class="cell"></div>
                        <div class="cell"></div>
                        <div class="cell"></div>


                        <div class="hourlabel">4p</div>

                        <div class="cell"></div>
                        <div class="cell"></div>
                        <div class="cell"></div>
                        <div class="cell"></div>

                        <div class="cell">

                            <div class="shift-block shift-amber shift-fit-b">

                                <span class="role">
                                    Evening shift
                                </span>

                                <span class="pay">
                                    Good pay
                                </span>

                            </div>

                        </div>

                        <div class="cell"></div>
                        <div class="cell"></div>


                        <div class="hourlabel">8p</div>

                        <div class="cell"></div>
                        <div class="cell"></div>
                        <div class="cell"></div>
                        <div class="cell"></div>
                        <div class="cell"></div>
                        <div class="cell"></div>
                        <div class="cell"></div>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             STATS
        ===================================================== -->

        <section class="stats">

            <div class="wrap">

                <div class="stat">

                    <div class="num">
                        <?= number_format($openJobsCount) ?>
                    </div>

                    <div class="lbl">
                        Open shifts
                    </div>

                </div>


                <div class="stat">

                    <div class="num">
                        <?= number_format($employerCount) ?>
                    </div>

                    <div class="lbl">
                        Employers registered
                    </div>

                </div>


                <div class="stat">

                    <div class="num">
                        <?= number_format($jobseekerCount) ?>
                    </div>

                    <div class="lbl">
                        Job seekers registered
                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             CATEGORIES
        ===================================================== -->

        <section class="block" id="categories">

            <div class="wrap">

                <div class="block-head">

                    <h2>
                        Work across every industry that runs on shifts
                    </h2>

                    <p>
                        Choose a category to find available part-time jobs.
                    </p>

                </div>


                <div class="cat-grid">

                    <?php if (!empty($categories)): ?>

                        <?php foreach ($categories as $cat): ?>

                            <a
                                class="cat-card"
                                href="index.php?category=<?= urlencode($cat['name']) ?>#shifts">

                                <div class="cat-mark">

                                    <?= htmlspecialchars(
                                        strtoupper(
                                            substr($cat['name'], 0, 1)
                                        )
                                    ) ?>

                                </div>


                                <h3>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </h3>


                                <span class="count">

                                    <?= number_format(
                                        $categoryCounts[$cat['id']] ?? 0
                                    ) ?>

                                    open shifts

                                </span>

                            </a>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <p>
                            No categories have been added yet.
                        </p>

                    <?php endif; ?>

                </div>

            </div>

        </section>


        <!-- =====================================================
             LIVE JOB LISTINGS
        ===================================================== -->

        <section
            class="block tight"
            id="shifts">

            <div class="wrap">

                <div class="block-head">

                    <h2>
                        Shifts open right now
                    </h2>

                    <p>

                        <?php if ($search !== '' || $categoryFilter !== ''): ?>

                            Showing jobs matching your search.

                            <a href="index.php">
                                Clear filters
                            </a>

                        <?php else: ?>

                            Live jobs posted by Flexiwork employers.

                        <?php endif; ?>

                    </p>

                </div>


                <div class="listing">

                    <?php if (empty($jobs)): ?>

                        <div class="job-row empty-job-row">

                            <div class="job-main">

                                <div class="role">
                                    No matching jobs found
                                </div>

                                <div class="employer">
                                    Try another search or choose a different category.
                                </div>

                            </div>

                        </div>


                    <?php else: ?>


                        <?php foreach ($jobs as $job): ?>

                            <div class="job-row">


                                <div class="job-main">

                                    <div class="role">

                                        <?= htmlspecialchars(
                                            $job['title']
                                        ) ?>

                                    </div>


                                    <div class="employer">

                                        <?= htmlspecialchars(
                                            $job['employer_name']
                                        ) ?>

                                    </div>


                                    <?php if (
                                        !empty($job['tag']) &&
                                        $job['tag'] !== 'none'
                                    ): ?>

                                        <span
                                            class="job-tag tag-<?= htmlspecialchars($job['tag']) ?>">

                                            <?= htmlspecialchars(
                                                tagLabel($job['tag'])
                                            ) ?>

                                        </span>

                                    <?php endif; ?>

                                </div>


                                <div class="job-time">

                                    <span class="day">

                                        <?= htmlspecialchars(
                                            $job['shift_day']
                                        ) ?>

                                    </span>

                                    <?= htmlspecialchars(
                                        formatJobTime($job['shift_start'])
                                    ) ?>

                                    –

                                    <?= htmlspecialchars(
                                        formatJobTime($job['shift_end'])
                                    ) ?>

                                </div>


                                <div class="job-pay">

                                    <?= number_format(
                                        $job['pay_rate'],
                                        0
                                    ) ?>

                                    MMK/hr


                                    <?php if (!empty($job['pay_note'])): ?>

                                        <small>

                                            <?= htmlspecialchars(
                                                $job['pay_note']
                                            ) ?>

                                        </small>

                                    <?php endif; ?>

                                </div>


                                <div class="job-loc">

                                    <?= htmlspecialchars(
                                        $job['location']
                                    ) ?>

                                </div>


                                <div class="job-action">

                                    <a
                                        href="apply.php?job_id=<?= (int) $job['id'] ?>"
                                        class="btn btn-small">

                                        Apply

                                    </a>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>

            </div>

        </section>


        <!-- =====================================================
             HOW IT WORKS
        ===================================================== -->

        <section
            class="block"
            id="how">

            <div class="wrap">

                <div class="block-head">

                    <h2>
                        From signup to your first shift, in three steps
                    </h2>

                </div>


                <div class="steps">

                    <div class="step">

                        <div class="idx mono">
                            01
                        </div>

                        <h3>
                            Set your availability
                        </h3>

                        <p>
                            Find jobs that fit your available days and working hours.
                        </p>

                    </div>


                    <div class="step">

                        <div class="idx mono">
                            02
                        </div>

                        <h3>
                            Find a suitable shift
                        </h3>

                        <p>
                            Compare job categories, pay rates, times and locations.
                        </p>

                    </div>


                    <div class="step">

                        <div class="idx mono">
                            03
                        </div>

                        <h3>
                            Apply and get hired
                        </h3>

                        <p>
                            Apply for jobs and track your application from your account.
                        </p>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             EMPLOYER SECTION
        ===================================================== -->

        <section
            class="employer-block"
            id="employers">

            <div class="wrap">

                <div>

                    <h2>
                        Need workers for your next shift?
                    </h2>

                    <p class="lede">

                        Post your part-time job on Flexiwork and manage applicants from your employer dashboard.

                    </p>


                    <ul class="employer-list">

                        <li>

                            <span class="n">
                                01
                            </span>

                            Post a shift with your job details

                        </li>


                        <li>

                            <span class="n">
                                02
                            </span>

                            Review applicants and their contact details

                        </li>


                        <li>

                            <span class="n">
                                03
                            </span>

                            Accept or reject applications from your dashboard

                        </li>

                    </ul>


                    <?php if (
                        isset($_SESSION['user_id']) &&
                        $_SESSION['role'] === 'employer'
                    ): ?>

                        <a
                            href="Post_job.php"
                            class="btn btn-primary">

                            Post a shift

                        </a>

                    <?php else: ?>

                        <a
                            href="register.php"
                            class="btn btn-primary">

                            Become an employer

                        </a>

                    <?php endif; ?>

                </div>


                <div class="employer-visual">

                    <div class="row">

                        <span class="k">
                            Platform
                        </span>

                        <span class="v">
                            Flexiwork
                        </span>

                    </div>


                    <div class="row">

                        <span class="k">
                            Jobs open
                        </span>

                        <span class="v">
                            <?= number_format($openJobsCount) ?>
                        </span>

                    </div>


                    <div class="row">

                        <span class="k">
                            Employers
                        </span>

                        <span class="v">
                            <?= number_format($employerCount) ?>
                        </span>

                    </div>


                    <div class="row">

                        <span class="k">
                            Applications
                        </span>

                        <span class="v">
                            Managed online
                        </span>

                    </div>


                    <div class="row">

                        <span class="k">
                            Status
                        </span>

                        <span class="v">
                            Ready to hire
                        </span>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             TESTIMONIALS
        ===================================================== -->

        <section class="block tight">

            <div class="wrap">

                <div class="block-head">

                    <h2>
                        Told by the people filling the shifts
                    </h2>

                </div>


                <div class="quotes">

                    <div class="quote">

                        <p>
                            Flexiwork makes it easier to find part-time work around my schedule.
                        </p>

                        <div class="who">

                            <strong>
                                Job Seeker
                            </strong>

                            — Flexiwork member

                        </div>

                    </div>


                    <div class="quote">

                        <p>
                            I can review applications and manage my job postings from one place.
                        </p>

                        <div class="who">

                            <strong>
                                Employer
                            </strong>

                            — Flexiwork member

                        </div>

                    </div>


                    <div class="quote">

                        <p>
                            The job search and application process is simple and straightforward.
                        </p>

                        <div class="who">

                            <strong>
                                Flexiwork User
                            </strong>

                            — Community member

                        </div>

                    </div>

                </div>

            </div>

        </section>


    </main>


    <footer>

        <div class="wrap">

            <div class="footer-grid">


                <div class="footer-brand">

                    <a
                        href="index.php"
                        class="footer-logo">

                        Flexi<span class="chip">Work</span>

                    </a>


                    <p>
                        A part-time job marketplace connecting flexible workers with businesses that need reliable people.
                    </p>


                    <div class="footer-social">

                        <a
                            href="https://www.facebook.com"
                            aria-label="Facebook">

                            f

                        </a>


                        <a
                            href="https://x.com"
                            aria-label="X">

                            𝕏

                        </a>


                        <a
                            href="https://www.instagram.com"
                            aria-label="Instagram">

                            ◎

                        </a>

                    </div>

                </div>


                <div class="footer-col">

                    <h4>
                        For workers
                    </h4>

                    <ul>

                        <li>
                            <a href="index.php">
                                Find shifts
                            </a>
                        </li>

                        <li>
                            <a href="categories.php">
                                Categories
                            </a>
                        </li>


                        <?php if (
                            isset($_SESSION['user_id']) &&
                            $_SESSION['role'] === 'jobseeker'
                        ): ?>

                            <li>
                                <a href="applications.php">
                                    My applications
                                </a>
                            </li>

                        <?php else: ?>

                            <li>
                                <a href="register.php">
                                    Create account
                                </a>
                            </li>

                        <?php endif; ?>

                    </ul>

                </div>


                <div class="footer-col">

                    <h4>
                        For employers
                    </h4>

                    <ul>

                        <li>

                            <a href="<?=
                                isset($_SESSION['user_id']) &&
                                $_SESSION['role'] === 'employer'
                                    ? 'Post_job.php'
                                    : 'register.php'
                            ?>">

                                Post a shift

                            </a>

                        </li>


                        <li>

                            <a href="<?=
                                isset($_SESSION['user_id']) &&
                                $_SESSION['role'] === 'employer'
                                    ? 'employer_jobs.php'
                                    : 'register.php'
                            ?>">

                                My Jobs

                            </a>

                        </li>


                        <li>

                            <a href="<?=
                                isset($_SESSION['user_id']) &&
                                $_SESSION['role'] === 'employer'
                                    ? 'employer_applications.php'
                                    : 'register.php'
                            ?>">

                                Applicants

                            </a>

                        </li>

                    </ul>

                </div>


                <div class="footer-col">

                    <h4>
                        Company
                    </h4>

                    <ul>

                        <li>
                            <a href="about.php">
                                About
                            </a>
                        </li>

                        <li>
                            <a href="contact.php">
                                Contact Us
                            </a>
                        </li>

                    </ul>

                </div>

            </div>


            <div class="footer-bottom">

                <span>
                    © 2026 Flexiwork
                </span>

                <span>
                    <a href="privacy.php">
                        Privacy
                    </a>
                    · Terms
                </span>

            </div>

        </div>

    </footer>


</body>

</html>

