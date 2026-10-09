
<?php
require_once 'db.php';
session_start();

/* =====================================================
   FLEXIWORK - FIND JOBS / CATEGORIES PAGE
   Search + Category + Location filters only
===================================================== */

$search = trim($_GET['search'] ?? '');
$categoryFilter = trim($_GET['category'] ?? '');
$locationFilter = trim($_GET['location'] ?? '');

/* =====================================================
   LOAD CATEGORIES
   Categories are loaded from the categories table
===================================================== */

$categories = [];

try {
    $stmt = $pdo->query("
        SELECT id, name
        FROM categories
        ORDER BY name ASC
    ");

    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}

/* =====================================================
   LOAD DISTINCT LOCATIONS
===================================================== */

$locations = [];

try {
    $stmt = $pdo->query("
        SELECT DISTINCT location
        FROM jobs
        WHERE status = 'open'
          AND location IS NOT NULL
          AND location <> ''
        ORDER BY location ASC
    ");

    $locations = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $locations = [];
}

/* =====================================================
   SEARCH JOBS
   jobs.category_id → categories.id → categories.name
===================================================== */

$sql = "
    SELECT
        j.id,
        j.title,
        j.employer_name,
        j.category_id,
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
    INNER JOIN categories c
        ON j.category_id = c.id
    WHERE j.status = 'open'
";

$params = [];

/* =====================================================
   SEARCH FILTER
===================================================== */

if ($search !== '') {

    $sql .= "
        AND (
            j.title LIKE ?
            OR j.employer_name LIKE ?
            OR j.description LIKE ?
            OR c.name LIKE ?
        )
    ";

    $term = '%' . $search . '%';

    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

/* =====================================================
   CATEGORY FILTER
   Filter by category ID
===================================================== */

if ($categoryFilter !== '') {

    $sql .= " AND j.category_id = ?";

    $params[] = $categoryFilter;
}

/* =====================================================
   LOCATION FILTER
===================================================== */

if ($locationFilter !== '') {

    $sql .= " AND j.location = ?";

    $params[] = $locationFilter;
}

/* =====================================================
   ORDER
===================================================== */

$sql .= " ORDER BY j.created_at DESC";

/* =====================================================
   EXECUTE QUERY
===================================================== */

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =====================================================
   HELPER FUNCTIONS
===================================================== */

function formatJobTime($time)
{
    if (!$time) {
        return '';
    }

    return date('g:i A', strtotime($time));
}

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

function jobInitial($title)
{
    $title = trim($title);

    if ($title === '') {
        return 'J';
    }

    return strtoupper(substr($title, 0, 1));
}

/* =====================================================
   FILTER STATUS
===================================================== */

$hasFilters = (
    $search !== '' ||
    $categoryFilter !== '' ||
    $locationFilter !== ''
);

$resultCount = count($jobs);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Find Jobs — Flexiwork</title>

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

                    <a href="index.php">Home</a>

                    <a href="categories.php" class="active">
                        Find Jobs
                    </a>

                    <a href="about.php">About</a>

                    <a href="contact.php">Contact Us</a>

                <?php elseif ($_SESSION['role'] === 'jobseeker'): ?>

                    <a href="index.php">Home</a>

                    <a href="categories.php" class="active">
                        Find Jobs
                    </a>

                    <a href="applications.php">
                        My Applications
                    </a>

                    <a href="about.php">
                        About
                    </a>

                    <a href="contact.php">
                        Contact Us
                    </a>

                    <a href="jobseeker_profile.php">
                        Profile
                    </a>

                <?php elseif ($_SESSION['role'] === 'employer'): ?>

                    <a href="employer_jobs.php">
                        Dashboard
                    </a>

                    <a href="employer_applications.php">
                        Applicants
                    </a>

                    <a href="Post_job.php">
                        Post
                    </a>

                    <a href="about.php">
                        About
                    </a>

                    <a href="contact.php">
                        Contact Us
                    </a>

                    <a href="employer_profile.php">
                        Profile
                    </a>

                <?php elseif ($_SESSION['role'] === 'admin'): ?>

                    <a href="admin_dashboard.php">
                        Dashboard
                    </a>

                    <a href="admin_categories.php">
                        Categories
                    </a>

                    <a href="admin_users.php">
                        Users
                    </a>

                    <a href="admin_settings.php">
                        Settings
                    </a>

                <?php endif; ?>

            </div>

            <div class="nav-actions">

                <?php if (isset($_SESSION['user_id'])): ?>

                    <span class="user-pill">
                        Welcome,
                        <?= htmlspecialchars($_SESSION['name'] ?? 'User') ?>
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


    <main class="jobs-page">

        <!-- PAGE HERO -->

        <section class="jobs-hero">

            <div class="wrap">

                <div class="jobs-hero-inner">

                    <div class="jobs-eyebrow">
                        Find your next shift
                    </div>

                    <h1>
                        Work that fits your life.
                    </h1>

                    <p>
                        Explore flexible part-time jobs from employers on Flexiwork.
                        Search by keyword, choose a job category, and find opportunities
                        in your preferred location.
                    </p>

                </div>

            </div>

        </section>


        <!-- SEARCH / FILTER -->

        <section class="filter-wrap">

            <div class="wrap">

                <div class="filter-card">

                    <form
                        class="filter-form"
                        method="get"
                        action="categories.php">

                        <!-- SEARCH -->

                        <div class="filter-field">

                            <label for="search">
                                Search
                            </label>

                            <input
                                id="search"
                                type="text"
                                name="search"
                                value="<?= htmlspecialchars($search) ?>"
                                placeholder="Job title, keyword or employer">

                        </div>


                        <!-- CATEGORY -->

                        <div class="filter-field">

                            <label for="category">
                                Job Filter
                            </label>

                            <select
                                id="category"
                                name="category">

                                <option value="">
                                    All categories
                                </option>

                                <?php foreach ($categories as $cat): ?>

                                    <option
                                        value="<?= (int) $cat['id'] ?>"
                                        <?= $categoryFilter == $cat['id']
                                            ? 'selected'
                                            : '' ?>>

                                        <?= htmlspecialchars($cat['name']) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- LOCATION -->

                        <div class="filter-field">

                            <label for="location">
                                Location
                            </label>

                            <select
                                id="location"
                                name="location">

                                <option value="">
                                    All locations
                                </option>

                                <?php foreach ($locations as $location): ?>

                                    <option
                                        value="<?= htmlspecialchars($location) ?>"
                                        <?= $locationFilter === $location
                                            ? 'selected'
                                            : '' ?>>

                                        <?= htmlspecialchars($location) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- ACTIONS -->

                        <div class="filter-actions">

                            <button
                                type="submit"
                                class="filter-btn">

                                Search Jobs

                            </button>

                            <?php if ($hasFilters): ?>

                                <a
                                    href="categories.php"
                                    class="clear-btn">

                                    Clear

                                </a>

                            <?php endif; ?>

                        </div>

                    </form>

                </div>

            </div>

        </section>


        <!-- RESULTS -->

        <section class="results-section">

            <div class="wrap">

                <div class="results-head">

                    <div>

                        <h2>

                            <?= $hasFilters
                                ? 'Search results'
                                : 'Latest opportunities' ?>

                        </h2>

                        <p>

                            <?php if ($hasFilters): ?>

                                Showing jobs that match your selected filters.

                            <?php else: ?>

                                Fresh part-time jobs posted by Flexiwork employers.

                            <?php endif; ?>

                        </p>

                    </div>


                    <div class="result-count">

                        <?= number_format($resultCount) ?>

                        job<?= $resultCount === 1 ? '' : 's' ?>

                        found

                    </div>

                </div>


                <div class="job-grid">

                    <?php if (empty($jobs)): ?>

                        <div class="empty-state">

                            <div class="empty-icon">
                                ⌕
                            </div>

                            <h3>
                                No jobs found
                            </h3>

                            <p>
                                We couldn't find an open job matching your search.
                                Try another keyword, category, or location.
                            </p>

                            <a
                                href="categories.php"
                                class="view-btn">

                                View all jobs

                            </a>

                        </div>

                    <?php else: ?>


                        <?php foreach ($jobs as $job): ?>

                            <article class="job-card">

                                <div class="job-top">

                                    <div class="job-brand">

                                        <div class="job-avatar">

                                            <?= htmlspecialchars(
                                                jobInitial($job['title'])
                                            ) ?>

                                        </div>

                                        <div>

                                            <p class="job-employer">

                                                <?= htmlspecialchars(
                                                    $job['employer_name']
                                                ) ?>

                                            </p>

                                            <h3 class="job-title-c">

                                                <?= htmlspecialchars(
                                                    $job['title']
                                                ) ?>

                                            </h3>

                                        </div>

                                    </div>


                                    <?php if (
                                        !empty($job['tag']) &&
                                        $job['tag'] !== 'none'
                                    ): ?>

                                        <span class="job-tag">

                                            <?= htmlspecialchars(
                                                tagLabel($job['tag'])
                                            ) ?>

                                        </span>

                                    <?php endif; ?>

                                </div>


                                <!-- CATEGORY -->

                                <?php if (!empty($job['category_name'])): ?>

                                    <span class="job-category">

                                        <?= htmlspecialchars(
                                            $job['category_name']
                                        ) ?>

                                    </span>

                                <?php endif; ?>


                                <!-- DESCRIPTION -->

                                <p class="job-description">

                                    <?= htmlspecialchars(
                                        $job['description']
                                            ?? 'Flexible part-time opportunity available on Flexiwork.'
                                    ) ?>

                                </p>


                                <!-- JOB META -->

                                <div class="job-meta">

                                    <div class="meta-item">

                                        <span class="meta-label">
                                            Schedule
                                        </span>

                                        <span class="meta-value">

                                            <?= htmlspecialchars(
                                                $job['shift_day']
                                            ) ?>

                                            <br>

                                            <?= htmlspecialchars(
                                                formatJobTime(
                                                    $job['shift_start']
                                                )
                                            ) ?>

                                            –

                                            <?= htmlspecialchars(
                                                formatJobTime(
                                                    $job['shift_end']
                                                )
                                            ) ?>

                                        </span>

                                    </div>


                                    <div class="meta-item">

                                        <span class="meta-label">
                                            Location
                                        </span>

                                        <span class="meta-value">

                                            <?= htmlspecialchars(
                                                $job['location']
                                            ) ?>

                                        </span>

                                    </div>

                                </div>


                                <!-- BOTTOM -->

                                <div class="job-bottom">

                                    <div class="job-pay">

                                        <?= number_format(
                                            (float) $job['pay_rate'],
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


                                    <a
                                        href="job_detail.php?id=<?= (int) $job['id'] ?>"
                                        class="view-btn">

                                        View More →

                                    </a>

                                </div>

                            </article>

                        <?php endforeach; ?>

                    <?php endif; ?>

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
                        A part-time job marketplace connecting flexible workers
                        with businesses that need reliable people.
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

                            <a href="<?= (
                                            isset($_SESSION['user_id']) &&
                                            $_SESSION['role'] === 'employer'
                                        )
                                            ? 'Post_job.php'
                                            : 'register.php' ?>">

                                Post a shift

                            </a>

                        </li>

                        <li>

                            <a href="<?= (
                                            isset($_SESSION['user_id']) &&
                                            $_SESSION['role'] === 'employer'
                                        )
                                            ? 'employer_jobs.php'
                                            : 'register.php' ?>">

                                My Jobs

                            </a>

                        </li>

                        <li>

                            <a href="<?= (
                                            isset($_SESSION['user_id']) &&
                                            $_SESSION['role'] === 'employer'
                                        )
                                            ? 'employer_applications.php'
                                            : 'register.php' ?>">

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
