<?php
require_once 'db.php';
session_start();

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header('Location: employer_profile.php');
    exit;
}

$userId = $_SESSION['user_id'];
$errors = [];
$success = false;

// ---- Load current profile ----
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name        = trim($_POST['name'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $companyName = trim($_POST['company_name'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');
    $bio         = trim($_POST['bio'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirm     = $_POST['confirm_password'] ?? '';

    if ($name === '') {
        $errors[] = 'Please enter your name.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    // Email must stay unique across users (excluding this account).
    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->execute([$email, $userId]);
        if ($stmt->fetch()) {
            $errors[] = 'That email is already used by another account.';
        }
    }

    // Password change is optional — only validate if they typed something.
    $changingPassword = $newPassword !== '' || $confirm !== '';
    if ($changingPassword) {
        if (strlen($newPassword) < 8) {
            $errors[] = 'New password must be at least 8 characters.';
        }
        if ($newPassword !== $confirm) {
            $errors[] = 'New passwords do not match.';
        }
    }

    if (empty($errors)) {
        if ($changingPassword) {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                'UPDATE users SET name = ?, email = ?, company_name = ?, phone = ?, bio = ?, password = ? WHERE id = ?'
            );
            $stmt->execute([$name, $email, $companyName, $phone, $bio, $hash, $userId]);
        } else {
            $stmt = $pdo->prepare(
                'UPDATE users SET name = ?, email = ?, company_name = ?, phone = ?, bio = ? WHERE id = ?'
            );
            $stmt->execute([$name, $email, $companyName, $phone, $bio, $userId]);
        }

        $_SESSION['name'] = $name;
        $success = true;

        // Reload to show saved values.
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My profile — Flexiwork</title>
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
                    <a href="about.php">About</a>
                    <a href="contact.php">Contact Us</a>
                    <a href="employer_profile.php" class="active">Profile</a>

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

        <section class="employer-profile-section">

            <div class="employer-profile-wrap">

                <!-- Page Header -->
                <div class="employer-profile-heading">

                    <div>
                        <p class="employer-eyebrow">EMPLOYER ACCOUNT</p>

                        <h2>My Profile</h2>

                        <p class="employer-profile-subtitle">
                            Manage your business information and account details.
                        </p>
                    </div>

                    <span class="employer-role-badge">
                        Employer
                    </span>

                </div>


                <!-- Success Message -->
                <?php if ($success): ?>

                    <p class="employer-notice employer-notice-success">
                        Profile updated successfully.
                    </p>

                <?php endif; ?>


                <!-- Error Messages -->
                <?php if (!empty($errors)): ?>

                    <div class="employer-notice employer-notice-error">

                        <ul>

                            <?php foreach ($errors as $error): ?>

                                <li>
                                    <?= htmlspecialchars($error) ?>
                                </li>

                            <?php endforeach; ?>

                        </ul>

                    </div>

                <?php endif; ?>


                <!-- Main Profile Layout -->
                <div class="employer-profile-layout">


                    <!-- =====================================
                     LEFT - EMPLOYER PROFILE
                     ===================================== -->

                    <div class="employer-profile-card">

                        <div class="employer-profile-cover"></div>

                        <div class="employer-profile-content">


                            <!-- Avatar -->
                            <div class="employer-profile-avatar">

                                <?= strtoupper(
                                    substr(
                                        $user['company_name']
                                            ?: $user['name'],
                                        0,
                                        1
                                    )
                                ) ?>

                            </div>


                            <!-- Company Name -->
                            <div class="employer-name-area">

                                <div>

                                    <h1>
                                        <?= htmlspecialchars(
                                            $user['company_name']
                                                ?: $user['name']
                                        ) ?>
                                    </h1>

                                    <p class="employer-owner-name">
                                        <?= htmlspecialchars($user['name']) ?>
                                    </p>

                                </div>

                                <span class="employer-verified-badge">
                                    ✓ Verified Employer
                                </span>

                            </div>


                            <!-- Location -->
                            <p class="employer-location">
                                📍 Myanmar
                            </p>


                            <!-- Category -->
                            <span class="employer-category-tag">
                                Delivery &amp; Logistics
                            </span>


                            <div class="employer-divider"></div>


                            <!-- About Business -->
                            <div class="employer-about-box">

                                <h3>
                                    About the Business
                                </h3>

                                <?php if (!empty($user['bio'])): ?>

                                    <p>
                                        <?= nl2br(
                                            htmlspecialchars($user['bio'])
                                        ) ?>
                                    </p>

                                <?php else: ?>

                                    <p class="employer-empty-text">
                                        No business description has been added yet.
                                    </p>

                                <?php endif; ?>

                            </div>


                            <!-- Contact -->
                            <div class="employer-contact-box">

                                <h3>
                                    Contact Information
                                </h3>


                                <div class="employer-contact-item">

                                    <span class="employer-contact-icon">
                                        ✉
                                    </span>

                                    <div>

                                        <small>Email</small>

                                        <p>
                                            <?= htmlspecialchars(
                                                $user['email']
                                            ) ?>
                                        </p>

                                    </div>

                                </div>


                                <div class="employer-contact-item">

                                    <span class="employer-contact-icon">
                                        ☎
                                    </span>

                                    <div>

                                        <small>Phone</small>

                                        <p>

                                            <?php if (!empty($user['phone'])): ?>

                                                <?= htmlspecialchars(
                                                    $user['phone']
                                                ) ?>

                                            <?php else: ?>

                                                <span class="employer-empty-text">
                                                    Not provided
                                                </span>

                                            <?php endif; ?>

                                        </p>

                                    </div>

                                </div>

                            </div>


                            <!-- Profile Bottom -->
                            <div class="employer-profile-bottom">

                                <div>
                                    <strong>Account Type</strong>
                                    <span>Employer</span>
                                </div>

                                <div>
                                    <strong>Member Since</strong>
                                    <span><?= date('Y') ?></span>
                                </div>

                            </div>

                        </div>

                    </div>



                    <!-- =====================================
                     RIGHT - EDIT PROFILE
                     ===================================== -->

                    <div class="employer-edit-card">


                        <div class="employer-edit-heading">

                            <p class="employer-eyebrow">
                                ACCOUNT SETTINGS
                            </p>

                            <h2>
                                Edit Profile
                            </h2>

                            <p>
                                Update your personal and business information.
                            </p>

                        </div>


                        <form
                            method="post"
                            class="employer-edit-form"
                            novalidate>


                            <!-- Name -->
                            <div class="employer-form-group">

                                <label for="name">
                                    Your name
                                </label>

                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    value="<?= htmlspecialchars(
                                                $user['name']
                                            ) ?>"
                                    required>

                            </div>


                            <!-- Email -->
                            <div class="employer-form-group">

                                <label for="email">
                                    Email
                                </label>

                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="<?= htmlspecialchars(
                                                $user['email']
                                            ) ?>"
                                    required>

                            </div>


                            <!-- Business Name -->
                            <div class="employer-form-group">

                                <label for="company_name">
                                    Business name
                                </label>

                                <input
                                    type="text"
                                    id="company_name"
                                    name="company_name"
                                    value="<?= htmlspecialchars(
                                                $user['company_name'] ?? ''
                                            ) ?>"
                                    placeholder="Shown to jobseekers on your listings">

                            </div>


                            <!-- Phone -->
                            <div class="employer-form-group">

                                <label for="phone">
                                    Phone
                                </label>

                                <input
                                    type="text"
                                    id="phone"
                                    name="phone"
                                    value="<?= htmlspecialchars(
                                                $user['phone'] ?? ''
                                            ) ?>"
                                    placeholder="09xxxxxxxxx">

                            </div>


                            <!-- Business Bio -->
                            <div class="employer-form-group">

                                <label for="bio">
                                    About your business
                                </label>

                                <textarea
                                    id="bio"
                                    name="bio"
                                    rows="5"
                                    placeholder="Tell jobseekers about your business..."><?= htmlspecialchars(
                                                                                                $user['bio'] ?? ''
                                                                                            ) ?></textarea>

                            </div>


                            <!-- Password Divider -->
                            <div class="employer-form-divider"></div>


                            <!-- Password Heading -->
                            <div class="employer-password-heading">

                                <h3>
                                    Change Password
                                </h3>

                                <p>
                                    Leave these fields blank if you want
                                    to keep your current password.
                                </p>

                            </div>


                            <!-- New Password -->
                            <div class="employer-form-group">

                                <label for="new_password">
                                    New password
                                </label>

                                <input
                                    type="password"
                                    id="new_password"
                                    name="new_password"
                                    minlength="8">

                                <p class="employer-form-hint">
                                    Minimum 8 characters.
                                </p>

                            </div>


                            <!-- Confirm Password -->
                            <div class="employer-form-group">

                                <label for="confirm_password">
                                    Confirm new password
                                </label>

                                <input
                                    type="password"
                                    id="confirm_password"
                                    name="confirm_password"
                                    minlength="8">

                            </div>


                            <!-- Buttons -->
                            <div class="employer-form-actions">

                                <a
                                    href="index.php"
                                    class="employer-cancel-button">
                                    Cancel
                                </a>

                                <button
                                    type="submit"
                                    class="employer-save-button">
                                    Save Changes
                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        </section>

    </main>

</body>

</html>