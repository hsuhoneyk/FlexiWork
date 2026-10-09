<?php
require_once 'db.php';
session_start();

// Only logged-in admins can access any admin-*.php page.
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_dashboard.php');
    exit;
}

// ---- Dashboard stats ----
$totalUsers      = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalJobseekers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'jobseeker'")->fetchColumn();
$totalEmployers  = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'employer'")->fetchColumn();
$openJobs        = $pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'open'")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();

// ---- Most recently registered users, for a quick glance ----
$stmt = $pdo->query("SELECT name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5");
$recentUsers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin dashboard — Flexiwork</title>
    <link rel="stylesheet" href="admin.css">
</head>

<body>

    <div class="admin-layout">

        <nav class="sidebar">
            <a href="admin_dashboard.php" class="brand">Flexi<span>work</span> Admin</a>

            <nav>
                <a href="admin_dashboard.php" class="active">Dashboard</a>
                <a href="admin_categories.php">Job categories</a>
                <a href="admin_users.php">Users</a>
                <a href="admin_contact.php">Message</a>
                <a href="admin_settings.php">Settings</a>
            </nav>

            <div class="sidebar-foot">
                <p>Signed in as <?= htmlspecialchars($_SESSION['name']) ?></p>
                <a href="index.php">View site</a><br>
                <a href="logout.php">Log out</a>
            </div>
        </nav>

        <main class="admin-main">
            <div class="admin-header">
                <div>
                    <h1>Dashboard</h1>
                    <p>An overview of Flexiwork activity.</p>
                </div>
            </div>

            <div class="stat-grid">
                <div class="stat-card">
                    <div class="num"><?= (int) $totalUsers ?></div>
                    <div class="lbl">Total users</div>
                </div>
                <div class="stat-card">
                    <div class="num"><?= (int) $totalJobseekers ?></div>
                    <div class="lbl">Jobseekers</div>
                </div>
                <div class="stat-card">
                    <div class="num"><?= (int) $totalEmployers ?></div>
                    <div class="lbl">Employers</div>
                </div>
                <div class="stat-card">
                    <div class="num"><?= (int) $openJobs ?></div>
                    <div class="lbl">Open jobs</div>
                </div>
            </div>

            <div class="panel">
                <h2>Recently registered users</h2>
                <?php if (empty($recentUsers)): ?>
                    <p>No users yet.</p>
                <?php else: ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentUsers as $u): ?>
                                <tr>
                                    <td><?= htmlspecialchars($u['name']) ?></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td><span class="pill pill-<?= htmlspecialchars($u['role']) ?>"><?= htmlspecialchars(ucfirst($u['role'])) ?></span></td>
                                    <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p style="margin-top:14px;"><a class="link" href="admin_users.php">View all users →</a></p>
                <?php endif; ?>
            </div>

            <div class="panel">
                <h2>Quick links</h2>
                <p><a class="link" href="admin_categories.php"><?= (int) $totalCategories ?> job categories</a> — add, rename, or remove categories.</p>
                <p><a class="link" href="admin_settings.php">System settings</a> — site name, contact email, login security.</p>
            </div>
        </main>

    </div>

</body>

</html>