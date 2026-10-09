<?php
require_once 'db.php';
session_start();

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_settings.php');
    exit;
}

$errors = [];
$success = false;

// ---- Which settings this page manages, with labels + input type ----
$fields = [
    'site_name'          => ['label' => 'Site name',                     'type' => 'text'],
    'contact_email'      => ['label' => 'Support contact email',         'type' => 'email'],
    'max_login_attempts' => ['label' => 'Max failed logins before lockout', 'type' => 'number'],
    'lockout_minutes'    => ['label' => 'Lockout duration (minutes)',    'type' => 'number'],
    'maintenance_mode'   => ['label' => 'Maintenance mode',              'type' => 'checkbox'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $siteName = trim($_POST['site_name'] ?? '');
    $email    = trim($_POST['contact_email'] ?? '');
    $maxAttempts = $_POST['max_login_attempts'] ?? '';
    $lockoutMin  = $_POST['lockout_minutes'] ?? '';
    $maintenance = isset($_POST['maintenance_mode']) ? '1' : '0';

    if ($siteName === '') {
        $errors[] = 'Site name cannot be empty.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid contact email.';
    }
    if (!ctype_digit((string) $maxAttempts) || $maxAttempts < 1) {
        $errors[] = 'Max login attempts must be a positive number.';
    }
    if (!ctype_digit((string) $lockoutMin) || $lockoutMin < 1) {
        $errors[] = 'Lockout duration must be a positive number.';
    }

    if (empty($errors)) {
        $values = [
            'site_name'          => $siteName,
            'contact_email'      => $email,
            'max_login_attempts' => $maxAttempts,
            'lockout_minutes'    => $lockoutMin,
            'maintenance_mode'   => $maintenance,
        ];

        $admin_id = $_SESSION['user_id'];

        $stmt = $pdo->prepare(
            'INSERT INTO settings (setting_key, setting_value, updated_by)
     VALUES (?, ?, ?)
     ON DUPLICATE KEY UPDATE
        setting_value = VALUES(setting_value),
        updated_by = VALUES(updated_by)'
        );

        foreach ($values as $key => $value) {
            $stmt->execute([$key, $value, $admin_id]);
        }

        $success = true;
    }
}

// ---- Load current settings ----
$stmt = $pdo->query('SELECT setting_key, setting_value FROM settings');
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings — Flexiwork Admin</title>
    <link rel="stylesheet" href="admin.css">
</head>

<body>

    <div class="admin-layout">

        <nav class="sidebar">
            <a href="admin.php" class="brand">Flexi<span>work</span> Admin</a>
            <nav>
                <a href="admin_dashboard.php">Dashboard</a>
                <a href="admin_categories.php">Job categories</a>
                <a href="admin_users.php">Users</a>
                <a href="admin_contact.php">Message</a>
                <a href="admin_settings.php" class="active">Settings</a>
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
                    <h1>System settings</h1>
                    <p>Site-wide configuration.</p>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="notice notice-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php elseif ($success): ?>
                <p class="notice notice-success">Settings saved.</p>
            <?php endif; ?>

            <div class="panel">
                <form method="post" class="admin-form stacked">

                    <div class="field">
                        <label for="site_name">Site name</label>
                        <input type="text" id="site_name" name="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>" required>
                    </div>

                    <div class="field">
                        <label for="contact_email">Support contact email</label>
                        <input type="email" id="contact_email" name="contact_email" value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>" required>
                    </div>

                    <div class="field">
                        <label for="max_login_attempts">Max failed logins before lockout</label>
                        <input type="number" id="max_login_attempts" name="max_login_attempts" min="1" value="<?= htmlspecialchars($settings['max_login_attempts'] ?? '5') ?>" required>
                    </div>

                    <div class="field">
                        <label for="lockout_minutes">Lockout duration (minutes)</label>
                        <input type="number" id="lockout_minutes" name="lockout_minutes" min="1" value="<?= htmlspecialchars($settings['lockout_minutes'] ?? '15') ?>" required>
                    </div>

                    <div class="field" style="flex-direction:row;align-items:center;gap:8px;">
                        <input type="checkbox" id="maintenance_mode" name="maintenance_mode" value="1" style="width:auto;"
                            <?= (($settings['maintenance_mode'] ?? '0') === '1') ? 'checked' : '' ?>>
                        <label for="maintenance_mode" style="margin:0;">Maintenance mode (hides the site from visitors)</label>
                    </div>

                    <button type="submit" class="btn btn-primary" style="margin-top:8px;width:fit-content;">Save settings</button>
                </form>
            </div>

            <div class="panel">
                <h2>Note</h2>
                <p style="color:var(--ink-soft);font-size:0.9rem;">
                    Max failed logins and lockout duration are stored here for reference, but
                    <code>login.php</code> currently uses its own fixed values. Update the
                    <code>MAX_ATTEMPTS</code> and <code>LOCKOUT_MINUTES</code> constants in
                    <code>login.php</code> to read from this table if you want changes here
                    to take effect immediately.
                </p>
            </div>
        </main>

    </div>

</body>

</html>