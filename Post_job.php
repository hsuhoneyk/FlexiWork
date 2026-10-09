
<?php
require_once 'db.php';
session_start();

// Only logged-in employers can access this page.
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header('Location: login.php');
    exit;
}

$employerId = $_SESSION['user_id'];
$errors = [];
$success = false;


/* =====================================================
   LOAD CATEGORIES FROM DATABASE
   Use category ID + category name
===================================================== */

$categoryStmt = $pdo->query(
    "SELECT id, name
     FROM categories
     ORDER BY name ASC"
);

$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title         = trim($_POST['title'] ?? '');
    $employerName  = trim($_POST['employer_name'] ?? '');
    $categoryId    = (int) ($_POST['category_id'] ?? 0);
    $description   = trim($_POST['description'] ?? '');
    $payRate       = $_POST['pay_rate'] ?? '';
    $payNote       = trim($_POST['pay_note'] ?? '');
    $shiftDay      = trim($_POST['shift_day'] ?? '');
    $shiftStart    = $_POST['shift_start'] ?? '';
    $shiftEnd      = $_POST['shift_end'] ?? '';
    $location      = trim($_POST['location'] ?? '');
    $tag           = $_POST['tag'] ?? 'none';


    /* =====================================================
       VALIDATION
    ===================================================== */

    if ($title === '') {
        $errors[] = 'Please enter a job title.';
    }

    if ($employerName === '') {
        $errors[] = 'Please enter your business name.';
    }


    // Check that the selected category actually exists
    if ($categoryId <= 0) {

        $errors[] = 'Please choose a category.';

    } else {

        $categoryCheck = $pdo->prepare(
            "SELECT id
             FROM categories
             WHERE id = ?"
        );

        $categoryCheck->execute([$categoryId]);

        if (!$categoryCheck->fetch()) {
            $errors[] = 'Please choose a valid category.';
        }
    }


    if (!is_numeric($payRate) || $payRate <= 0) {
        $errors[] = 'Please enter a valid hourly pay rate.';
    }

    if ($shiftDay === '') {
        $errors[] = 'Please enter the shift day.';
    }

    if ($shiftStart === '' || $shiftEnd === '' || $shiftStart >= $shiftEnd) {
        $errors[] = 'Please enter a valid start and end time.';
    }

    if ($location === '') {
        $errors[] = 'Please enter a location.';
    }

    if (!in_array($tag, ['today', 'weekend', 'multi', 'none'], true)) {
        $tag = 'none';
    }


    /* =====================================================
       INSERT JOB
       Save category_id instead of category name
    ===================================================== */

    if (empty($errors)) {

        $stmt = $pdo->prepare(
            'INSERT INTO jobs
                (
                    employer_id,
                    title,
                    employer_name,
                    category_id,
                    description,
                    pay_rate,
                    pay_note,
                    shift_day,
                    shift_start,
                    shift_end,
                    location,
                    tag,
                    status
                )
             VALUES
                (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "open"
                )'
        );

        $stmt->execute([
            $employerId,
            $title,
            $employerName,
            $categoryId,
            $description,
            $payRate,
            $payNote !== '' ? $payNote : null,
            $shiftDay,
            $shiftStart,
            $shiftEnd,
            $location,
            $tag,
        ]);

        $success = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Post a job — Flexiwork</title>

    <!-- Fonts: Newsreader + Inter -->

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Newsreader:opsz,wght@6..72,500&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="style.css"
    >

</head>


<body class="post-job-page">


<header>

    <nav
        class="wrap"
        aria-label="Main navigation"
    >

        <a
            href="index.php"
            class="logo"
        >
            Flexi<span class="chip">Work</span>
        </a>


        <div class="nav-links">

            <?php if (!isset($_SESSION['user_id'])): ?>

                <a href="index.php">
                    Home
                </a>

                <a
                    href="categories.php"
                    class="active"
                >
                    Find Jobs
                </a>

                <a href="about.php">
                    About
                </a>

                <a href="contact.php">
                    Contact Us
                </a>


            <?php elseif ($_SESSION['role'] === 'jobseeker'): ?>

                <a href="index.php">
                    Home
                </a>

                <a
                    href="categories.php"
                    class="active"
                >
                    Find Jobs
                </a>

                <a href="applications.php">
                    My Applications
                </a>

                <a href="jobseeker_profile.php">
                    Profile
                </a>

                <a href="about.php">
                    About
                </a>

                <a href="contact.php">
                    Contact Us
                </a>


            <?php elseif ($_SESSION['role'] === 'employer'): ?>

                <a href="employer_jobs.php">
                    Dashboard
                </a>

                <a href="employer_applications.php">
                    Applicants
                </a>

                <a
                    href="Post_job.php"
                    class="active"
                >
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

                <a
                    href="logout.php"
                    class="logout-btn"
                >
                    Logout
                </a>

            <?php else: ?>

                <a
                    href="login.php"
                    class="signin"
                >
                    Log in
                </a>

                <a
                    href="register.php"
                    class="btn btn-primary"
                >
                    Get started
                </a>

            <?php endif; ?>

        </div>


        <button
            class="mobile-toggle"
            aria-label="Open menu"
            type="button"
        >

            <span></span>
            <span></span>
            <span></span>

        </button>

    </nav>

</header>


<main>

    <section class="job-shell">

        <div class="job-wrap">

            <a
                class="job-back"
                href="employer_jobs.php"
            >
                ← Back to my jobs
            </a>


            <div class="job-card">

                <h1>
                    Post a job
                </h1>

                <p class="job-sub">
                    Fill in the details below — it'll be live on the homepage as soon as you submit.
                </p>


                <!-- SUCCESS MESSAGE -->

                <?php if ($success): ?>

                    <div
                        class="job-alert job-alert-success"
                        role="status"
                    >

                        <svg
                            viewBox="0 0 20 20"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >

                            <path d="M4 10.5l4 4 8-9" />

                        </svg>

                        <span>
                            Your job has been posted and is now visible on the homepage.

                            <a href="employer_jobs.php">
                                View my jobs
                            </a>
                        </span>

                    </div>

                <?php endif; ?>


                <!-- ERROR MESSAGE -->

                <?php if (!empty($errors)): ?>

                    <div
                        class="job-alert job-alert-error"
                        role="alert"
                    >

                        <svg
                            viewBox="0 0 20 20"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >

                            <circle
                                cx="10"
                                cy="10"
                                r="8"
                            />

                            <path d="M10 6v5M10 14h.01" />

                        </svg>


                        <ul>

                            <?php foreach ($errors as $error): ?>

                                <li>
                                    <?= htmlspecialchars($error) ?>
                                </li>

                            <?php endforeach; ?>

                        </ul>

                    </div>

                <?php endif; ?>


                <form
                    method="post"
                    class="job-form"
                    novalidate
                >


                    <!-- ROLE -->

                    <div class="job-section">

                        <span class="job-section-label">
                            The role
                        </span>


                        <div class="field">

                            <label for="title">
                                Job title
                            </label>

                            <input
                                type="text"
                                id="title"
                                name="title"
                                placeholder="e.g. Weekend barista"
                                value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                                required
                            >

                        </div>


                        <div class="job-row">

                            <div class="field">

                                <label for="employer_name">
                                    Business name
                                </label>

                                <input
                                    type="text"
                                    id="employer_name"
                                    name="employer_name"
                                    placeholder="Your business name"
                                    value="<?= htmlspecialchars($_POST['employer_name'] ?? '') ?>"
                                    required
                                >

                            </div>


                            <div class="field">

                                <label for="category_id">
                                    Category
                                </label>

                                <select
                                    id="category_id"
                                    name="category_id"
                                    required
                                >

                                    <option value="">
                                        Select a category
                                    </option>


                                    <?php foreach ($categories as $cat): ?>

                                        <option
                                            value="<?= (int) $cat['id'] ?>"
                                            <?= ((int) ($_POST['category_id'] ?? 0) === (int) $cat['id']) ? 'selected' : '' ?>
                                        >
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>

                        </div>


                        <div class="field">

                            <label for="description">
                                Description
                                <span class="hint">
                                    (optional)
                                </span>
                            </label>

                            <textarea
                                id="description"
                                name="description"
                                rows="3"
                                placeholder="What will they be doing?"
                            ><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>

                        </div>

                    </div>


                    <!-- PAY -->

                    <div class="job-section">

                        <span class="job-section-label">
                            Pay
                        </span>


                        <div class="job-row job-row-pay">

                            <div class="field">

                                <label for="pay_rate">
                                    Hourly pay (MMK)
                                </label>

                                <input
                                    type="number"
                                    id="pay_rate"
                                    name="pay_rate"
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                    value="<?= htmlspecialchars($_POST['pay_rate'] ?? '') ?>"
                                    required
                                >

                            </div>


                            <div class="field">

                                <label for="pay_note">
                                    Pay note
                                    <span class="hint">
                                        (optional)
                                    </span>
                                </label>

                                <input
                                    type="text"
                                    id="pay_note"
                                    name="pay_note"
                                    placeholder='e.g. "+ tips"'
                                    value="<?= htmlspecialchars($_POST['pay_note'] ?? '') ?>"
                                >

                            </div>

                        </div>

                    </div>


                    <!-- SHIFT DETAILS -->

                    <div class="job-section">

                        <span class="job-section-label">
                            Shift details
                        </span>


                        <div class="job-row">

                            <div class="field">

                                <label for="shift_day">
                                    Shift day
                                </label>

                                <input
                                    type="text"
                                    id="shift_day"
                                    name="shift_day"
                                    placeholder="e.g. Sat, Mon"
                                    value="<?= htmlspecialchars($_POST['shift_day'] ?? '') ?>"
                                    required
                                >

                            </div>


                            <div class="field">

                                <label for="location">
                                    Location
                                </label>

                                <input
                                    type="text"
                                    id="location"
                                    name="location"
                                    placeholder="e.g. Downtown Yangon"
                                    value="<?= htmlspecialchars($_POST['location'] ?? '') ?>"
                                    required
                                >

                            </div>

                        </div>


                        <div class="job-row">

                            <div class="field">

                                <label for="shift_start">
                                    Start time
                                </label>

                                <input
                                    type="time"
                                    id="shift_start"
                                    name="shift_start"
                                    value="<?= htmlspecialchars($_POST['shift_start'] ?? '') ?>"
                                    required
                                >

                            </div>


                            <div class="field">

                                <label for="shift_end">
                                    End time
                                </label>

                                <input
                                    type="time"
                                    id="shift_end"
                                    name="shift_end"
                                    value="<?= htmlspecialchars($_POST['shift_end'] ?? '') ?>"
                                    required
                                >

                            </div>

                        </div>


                        <div class="field">

                            <label for="tag">
                                Tag
                            </label>

                            <select
                                id="tag"
                                name="tag"
                            >

                                <option value="none">
                                    None
                                </option>

                                <option value="today">
                                    Today
                                </option>

                                <option value="weekend">
                                    This weekend
                                </option>

                                <option value="multi">
                                    Multiple shifts
                                </option>

                            </select>

                        </div>

                    </div>


                    <button
                        type="submit"
                        class="job-submit"
                    >
                        Post job
                    </button>

                </form>

            </div>

        </div>

    </section>

</main>


</body>

</html>
