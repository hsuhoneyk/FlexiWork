
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


// =====================================================
// LOAD CATEGORIES FROM DATABASE
// =====================================================

$stmt = $pdo->query("
    SELECT id, name
    FROM categories
    ORDER BY name ASC
");

$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);


// =====================================================
// LOAD THE JOB
// =====================================================

$jobId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($jobId <= 0) {
    header('Location: employer_jobs.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT *
    FROM jobs
    WHERE id = ? AND employer_id = ?
");

$stmt->execute([$jobId, $employerId]);
$job = $stmt->fetch();

if (!$job) {
    // Either it doesn't exist, or it isn't this employer's job.
    header('Location: employer_jobs.php');
    exit;
}


// =====================================================
// HANDLE THE UPDATE
// =====================================================

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
    $status        = $_POST['status'] ?? 'open';


    // -------------------------------------------------
    // VALIDATION
    // -------------------------------------------------

    if ($title === '') {
        $errors[] = 'Please enter a job title.';
    }

    if ($employerName === '') {
        $errors[] = 'Please enter your business name.';
    }


    // Check that selected category exists.
    $validCategoryIds = array_column($categories, 'id');

    if ($categoryId <= 0 || !in_array($categoryId, $validCategoryIds, true)) {
        $errors[] = 'Please choose a valid category.';
    }


    if (!is_numeric($payRate) || $payRate <= 0) {
        $errors[] = 'Please enter a valid hourly pay rate.';
    }

    if ($shiftDay === '') {
        $errors[] = 'Please enter the shift day.';
    }

    if (
        $shiftStart === '' ||
        $shiftEnd === '' ||
        $shiftStart >= $shiftEnd
    ) {
        $errors[] = 'Please enter a valid start and end time.';
    }

    if ($location === '') {
        $errors[] = 'Please enter a location.';
    }

    if (!in_array($tag, ['today', 'weekend', 'multi', 'none'], true)) {
        $tag = 'none';
    }

    if (!in_array($status, ['open', 'closed'], true)) {
        $status = 'open';
    }


    // -------------------------------------------------
    // UPDATE JOB
    // -------------------------------------------------

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            UPDATE jobs SET
                title = ?,
                employer_name = ?,
                category_id = ?,
                description = ?,
                pay_rate = ?,
                pay_note = ?,
                shift_day = ?,
                shift_start = ?,
                shift_end = ?,
                location = ?,
                tag = ?,
                status = ?
            WHERE id = ? AND employer_id = ?
        ");

        $stmt->execute([
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
            $status,
            $jobId,
            $employerId,
        ]);

        $success = true;


        // Refresh local copy so the form reflects what's saved.
        $stmt = $pdo->prepare("
            SELECT *
            FROM jobs
            WHERE id = ? AND employer_id = ?
        ");

        $stmt->execute([$jobId, $employerId]);
        $job = $stmt->fetch();
    }
}


/*
 * Pick a field's display value:
 * whatever was just submitted (so a failed save
 * doesn't lose the user's edits), falling back
 * to what's stored in the database.
 */
function old(string $key, array $job, string $default = '')
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        return $_POST[$key] ?? $default;
    }

    return $job[$key] ?? $default;
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

    <title>Edit job — Flexiwork</title>

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

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="post-job.css">

</head>


<body class="post-job-page">


    <header>

        <nav class="wrap" aria-label="Main navigation">

            <a href="index.php" class="logo">
                Flexi<span class="chip">Work</span>
            </a>


            <div class="nav-links">

                <?php if (!isset($_SESSION['user_id'])): ?>

                    <a href="index.php">Home</a>
                    <a href="categories.php">Find Jobs</a>
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
                    <a href="employer_applications.php">Applicants</a>
                    <a href="Post_job.php" class="active">Post</a>
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

                    <h1>Edit job</h1>

                    <p class="job-sub">
                        Update the details below and save your changes.
                    </p>


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
                                Your changes have been saved.
                                <a href="employer_jobs.php">
                                    View my jobs
                                </a>
                            </span>

                        </div>

                    <?php endif; ?>


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


                        <!-- =================================
                             ROLE
                        ================================== -->

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
                                    value="<?= htmlspecialchars(old('title', $job)) ?>"
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
                                        value="<?= htmlspecialchars(old('employer_name', $job)) ?>"
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


                                        <?php foreach ($categories as $category): ?>

                                            <option
                                                value="<?= (int) $category['id'] ?>"
                                                <?=
                                                    ((int) old(
                                                        'category_id',
                                                        $job,
                                                        $job['category_id'] ?? 0
                                                    ) === (int) $category['id'])
                                                    ? 'selected'
                                                    : ''
                                                ?>
                                            >

                                                <?= htmlspecialchars($category['name']) ?>

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
                                ><?= htmlspecialchars(old('description', $job)) ?></textarea>

                            </div>

                        </div>


                        <!-- =================================
                             PAY
                        ================================== -->

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
                                        value="<?= htmlspecialchars(old('pay_rate', $job)) ?>"
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
                                        placeholder='+ tips'
                                        value="<?= htmlspecialchars(old('pay_note', $job)) ?>"
                                    >

                                </div>

                            </div>

                        </div>


                        <!-- =================================
                             SHIFT DETAILS
                        ================================== -->

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
                                        value="<?= htmlspecialchars(old('shift_day', $job)) ?>"
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
                                        value="<?= htmlspecialchars(old('location', $job)) ?>"
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
                                        value="<?= htmlspecialchars(old('shift_start', $job)) ?>"
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
                                        value="<?= htmlspecialchars(old('shift_end', $job)) ?>"
                                        required
                                    >

                                </div>

                            </div>


                            <div class="job-row">


                                <div class="field">

                                    <label for="tag">
                                        Tag
                                    </label>

                                    <select
                                        id="tag"
                                        name="tag"
                                    >

                                        <?php

                                        $tagOptions = [
                                            'none'    => 'None',
                                            'today'   => 'Today',
                                            'weekend' => 'This weekend',
                                            'multi'   => 'Multiple shifts',
                                        ];

                                        $currentTag = old(
                                            'tag',
                                            $job,
                                            'none'
                                        );

                                        foreach (
                                            $tagOptions
                                            as $value => $label
                                        ):

                                        ?>

                                            <option
                                                value="<?= $value ?>"
                                                <?= $currentTag === $value
                                                    ? 'selected'
                                                    : '' ?>
                                            >

                                                <?= $label ?>

                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </div>


                                <div class="field">

                                    <label for="status">
                                        Status
                                    </label>

                                    <select
                                        id="status"
                                        name="status"
                                    >

                                        <?php

                                        $statusOptions = [
                                            'open' =>
                                                'Open — accepting applicants',

                                            'closed' =>
                                                'Closed — no longer accepting applicants',
                                        ];

                                        $currentStatus = old(
                                            'status',
                                            $job,
                                            'open'
                                        );

                                        foreach (
                                            $statusOptions
                                            as $value => $label
                                        ):

                                        ?>

                                            <option
                                                value="<?= $value ?>"
                                                <?= $currentStatus === $value
                                                    ? 'selected'
                                                    : '' ?>
                                            >

                                                <?= $label ?>

                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </div>

                            </div>

                        </div>


                        <button
                            type="submit"
                            class="job-submit"
                        >
                            Save changes
                        </button>

                    </form>

                </div>

            </div>

        </section>

    </main>

</body>

</html>

