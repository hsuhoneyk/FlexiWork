<?php
require_once 'db.php';
session_start();

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'jobseeker') {
    header('Location: jobseeker_profile.php');
    exit;
}

$userId = $_SESSION['user_id'];
$errors = [];
$success = false;

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name        = trim($_POST['name'] ?? '');
    $email       = trim($_POST['email'] ?? '');
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

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->execute([$email, $userId]);
        if ($stmt->fetch()) {
            $errors[] = 'That email is already used by another account.';
        }
    }

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
                'UPDATE users SET name = ?, email = ?, phone = ?, bio = ?, password = ? WHERE id = ?'
            );
            $stmt->execute([$name, $email, $phone, $bio, $hash, $userId]);
        } else {
            $stmt = $pdo->prepare(
                'UPDATE users SET name = ?, email = ?, phone = ?, bio = ? WHERE id = ?'
            );
            $stmt->execute([$name, $email, $phone, $bio, $userId]);
        }

        $_SESSION['name'] = $name;
        $success = true;

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
                    <a href="categories.php" >Find Jobs</a>
                    <a href="about.php">About</a>
                    <a href="contact.php">Contact Us</a>

                <?php elseif ($_SESSION['role'] === 'jobseeker'): ?>

                    <a href="index.php">Home</a>
                    <a href="categories.php">Find Jobs</a>
                    <a href="applications.php">My Applications</a>
                    <a href="about.php">About</a>
                    <a href="contact.php">Contact Us</a>
                    <a href="jobseeker_profile.php" class="active">Profile</a>

                <?php elseif ($_SESSION['role'] === 'employer'): ?>

                    <a href="employer_jobs.php">Dashboard</a>
                    <a href="employer_applications.php">Applicants</a>
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
        <section class="jobseeker-profile-section">
            <div class="jobseeker-profile-wrap">

                <!-- Page Heading -->
                <div class="jobseeker-profile-heading">
                    <div>
                        <p class="jobseeker-eyebrow">JOB SEEKER ACCOUNT</p>
                        <h2>My Profile</h2>
                        <p class="jobseeker-profile-subtitle">
                            Manage your personal information and account details.
                        </p>
                    </div>

                    <span class="jobseeker-role-badge">Job Seeker</span>
                </div>

                <!-- Messages -->
                <?php if ($success): ?>
                    <p class="jobseeker-notice jobseeker-notice-success">
                        Profile updated successfully.
                    </p>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="jobseeker-notice jobseeker-notice-error">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Profile Layout -->
                <div class="jobseeker-profile-layout">

                    <!-- LEFT: Profile Card -->
                    <aside class="jobseeker-profile-card">

                        <div class="jobseeker-profile-cover"></div>

                        <div class="jobseeker-profile-content">

                            <div class="jobseeker-avatar">
                                <?= strtoupper(substr($user['name'], 0, 1)) ?>
                            </div>

                            <div class="jobseeker-name-area">
                                <h3><?= htmlspecialchars($user['name']) ?></h3>
                                <span class="jobseeker-verified">
                                    ✓ Job Seeker
                                </span>
                            </div>

                            <p class="jobseeker-location">
                                📍 Myanmar
                            </p>

                            <span class="jobseeker-category-tag">
                                Looking for Part-Time Jobs
                            </span>

                            <div class="jobseeker-divider"></div>

                            <!-- About -->
                            <div class="jobseeker-about-box">
                                <h4>About Me</h4>
                                <p>
                                    <?= !empty($user['bio'])
                                        ? htmlspecialchars($user['bio'])
                                        : 'Add a short description about yourself, your experience, and availability.' ?>
                                </p>
                            </div>

                            <!-- Contact -->
                            <div class="jobseeker-contact-box">
                                <h4>Contact Information</h4>

                                <div class="jobseeker-contact-item">
                                    <span class="jobseeker-contact-icon">✉</span>
                                    <div>
                                        <small>Email</small>
                                        <p><?= htmlspecialchars($user['email']) ?></p>
                                    </div>
                                </div>

                                <div class="jobseeker-contact-item">
                                    <span class="jobseeker-contact-icon">☎</span>
                                    <div>
                                        <small>Phone</small>
                                        <p>
                                            <?= !empty($user['phone'])
                                                ? htmlspecialchars($user['phone'])
                                                : 'Not provided' ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="jobseeker-profile-bottom">
                                <span>Account Type</span>
                                <strong>Job Seeker</strong>
                            </div>

                        </div>
                    </aside>


                    <!-- RIGHT: Edit Profile -->
                    <div class="jobseeker-edit-card">

                        <div class="jobseeker-edit-heading">
                            <div>
                                <p class="jobseeker-edit-label">ACCOUNT SETTINGS</p>
                                <h3>Edit Profile</h3>
                            </div>
                        </div>

                        <form method="post"
                            class="jobseeker-edit-form"
                            novalidate>

                            <!-- Personal Information -->
                            <div class="jobseeker-form-section">
                                <h4>Personal Information</h4>

                                <div class="jobseeker-form-group">
                                    <label for="name">Your Name</label>
                                    <input
                                        type="text"
                                        id="name"
                                        name="name"
                                        value="<?= htmlspecialchars($user['name']) ?>"
                                        required>
                                </div>

                                <div class="jobseeker-form-group">
                                    <label for="email">Email Address</label>
                                    <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        value="<?= htmlspecialchars($user['email']) ?>"
                                        required>
                                </div>

                                <div class="jobseeker-form-group">
                                    <label for="phone">Phone Number</label>
                                    <input
                                        type="text"
                                        id="phone"
                                        name="phone"
                                        value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                        placeholder="Enter your phone number">
                                </div>

                                <div class="jobseeker-form-group">
                                    <label for="bio">About You</label>
                                    <textarea
                                        id="bio"
                                        name="bio"
                                        rows="5"
                                        placeholder="Tell employers about your experience, skills, or availability."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>

                                    <p class="jobseeker-form-hint">
                                        This information may be visible to employers.
                                    </p>
                                </div>
                            </div>


                            <!-- Password -->
                            <div class="jobseeker-form-divider"></div>

                            <div class="jobseeker-form-section">
                                <h4>Change Password</h4>

                                <p class="jobseeker-password-description">
                                    Leave these fields blank if you do not want to change your password.
                                </p>

                                <div class="jobseeker-form-group">
                                    <label for="new_password">New Password</label>
                                    <input
                                        type="password"
                                        id="new_password"
                                        name="new_password"
                                        minlength="8"
                                        placeholder="Enter new password">
                                </div>

                                <div class="jobseeker-form-group">
                                    <label for="confirm_password">
                                        Confirm New Password
                                    </label>
                                    <input
                                        type="password"
                                        id="confirm_password"
                                        name="confirm_password"
                                        minlength="8"
                                        placeholder="Confirm new password">
                                </div>
                            </div>


                            <!-- Buttons -->
                            <div class="jobseeker-form-actions">
                                <a href="index.php"
                                    class="jobseeker-cancel-button">
                                    Cancel
                                </a>

                                <button type="submit"
                                    class="jobseeker-save-button">
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