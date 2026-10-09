<?php
require_once 'db.php';
session_start();

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: admin_users.php');
  exit;
}

// ---- Optional role filter ----
$roleFilter = $_GET['role'] ?? '';
$validRoles = ['jobseeker', 'employer', 'admin'];

if (in_array($roleFilter, $validRoles, true)) {
  $stmt = $pdo->prepare('SELECT * FROM users WHERE role = ? ORDER BY created_at DESC');
  $stmt->execute([$roleFilter]);
} else {
  $stmt = $pdo->query('SELECT * FROM users ORDER BY created_at DESC');
}
$users = $stmt->fetchAll();

$counts = $pdo->query(
  "SELECT role, COUNT(*) AS total FROM users GROUP BY role"
)->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Users — Flexiwork Admin</title>
  <link rel="stylesheet" href="admin.css">
</head>

<body>

  <div class="admin-layout">

    <nav class="sidebar">
      <a href="admin.php" class="brand">Flexi<span>work</span> Admin</a>
      <nav>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="admin_categories.php">Job categories</a>
        <a href="admin_users.php" class="active">Users</a>
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
          <h1>Registered users</h1>
          <p>Read-only list of everyone who has signed up.</p>
        </div>
      </div>

      <div class="panel">
        <h2>
          Users
          <span style="font-weight:400;color:var(--ink-soft);font-size:0.85rem;">
            (<?= count($users) ?> shown)
          </span>
        </h2>

        <form method="get" class="admin-form" style="margin-bottom:18px;">
          <div class="field">
            <label for="role">Filter by role</label>
            <select id="role" name="role" onchange="this.form.submit()">
              <option value="">All roles (<?= array_sum($counts) ?>)</option>
              <option value="jobseeker" <?= $roleFilter === 'jobseeker' ? 'selected' : '' ?>>
                Jobseeker (<?= (int) ($counts['jobseeker'] ?? 0) ?>)
              </option>
              <option value="employer" <?= $roleFilter === 'employer' ? 'selected' : '' ?>>
                Employer (<?= (int) ($counts['employer'] ?? 0) ?>)
              </option>
              <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>
                Admin (<?= (int) ($counts['admin'] ?? 0) ?>)
              </option>
            </select>
          </div>
        </form>

        <?php if (empty($users)): ?>
          <p>No users match this filter.</p>
        <?php else: ?>
          <table class="admin-table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Joined</th>
                <th>Failed logins</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users as $u): ?>
                <tr>
                  <td><?= htmlspecialchars($u['name']) ?></td>
                  <td><?= htmlspecialchars($u['email']) ?></td>
                  <td><span class="pill pill-<?= htmlspecialchars($u['role']) ?>"><?= htmlspecialchars(ucfirst($u['role'])) ?></span></td>
                  <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                  <td><?= (int) $u['failed_attempts'] ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </main>

  </div>

</body>

</html>