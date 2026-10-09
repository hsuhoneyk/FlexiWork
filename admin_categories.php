
<?php
require_once 'db.php';
session_start();

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$errors = [];
$success = '';

/* =====================================================
   ADD A NEW CATEGORY
===================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {

    $name = trim($_POST['name'] ?? '');

    if ($name === '') {
        $errors[] = 'Please enter a category name.';
    } else {

        // Check whether category already exists
        $stmt = $pdo->prepare(
            'SELECT id FROM categories WHERE name = ?'
        );
        $stmt->execute([$name]);

        if ($stmt->fetch()) {
            $errors[] = 'That category already exists.';
        } else {

            $stmt = $pdo->prepare(
                'INSERT INTO categories (name) VALUES (?)'
            );
            $stmt->execute([$name]);

            $success = 'Category added.';
        }
    }
}


/* =====================================================
   RENAME AN EXISTING CATEGORY
===================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'rename') {

    $id   = (int) ($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');

    if ($name === '') {

        $errors[] = 'Category name cannot be empty.';

    } else {

        // Check if another category already has this name
        $stmt = $pdo->prepare(
            'SELECT id FROM categories WHERE name = ? AND id != ?'
        );
        $stmt->execute([$name, $id]);

        if ($stmt->fetch()) {

            $errors[] = 'That category already exists.';

        } else {

            /*
             * Only update the categories table.
             *
             * Jobs are connected through:
             * jobs.category_id -> categories.id
             *
             * Therefore, renaming the category automatically
             * changes the category name everywhere.
             */
            $stmt = $pdo->prepare(
                'UPDATE categories SET name = ? WHERE id = ?'
            );
            $stmt->execute([$name, $id]);

            $success = 'Category updated.';
        }
    }
}


/* =====================================================
   DELETE A CATEGORY
===================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {

    $id = (int) ($_POST['id'] ?? 0);

    try {

        /*
         * ON DELETE RESTRICT in the database prevents deleting
         * a category that is currently used by jobs.
         */
        $stmt = $pdo->prepare(
            'DELETE FROM categories WHERE id = ?'
        );
        $stmt->execute([$id]);

        $success = 'Category removed.';

    } catch (PDOException $e) {

        $errors[] = 'This category cannot be deleted because it is being used by existing jobs.';
    }
}


/* =====================================================
   LOAD CATEGORIES
   Count open jobs using category_id
===================================================== */

$stmt = $pdo->query(
    "SELECT
        c.id,
        c.name,
        (
            SELECT COUNT(*)
            FROM jobs j
            WHERE j.category_id = c.id
              AND j.status = 'open'
        ) AS job_count
     FROM categories c
     ORDER BY c.name ASC"
);

$categories = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Job categories — Flexiwork Admin</title>

    <link rel="stylesheet" href="admin.css">
</head>

<body>

    <div class="admin-layout">

        <nav class="sidebar">

            <a href="admin.php" class="brand">
                Flexi<span>work</span> Admin
            </a>

            <nav>

                <a href="admin_dashboard.php">
                    Dashboard
                </a>

                <a href="admin_categories.php" class="active">
                    Job categories
                </a>

                <a href="admin_users.php">
                    Users
                </a>

                <a href="admin_contact.php">
                    Message
                </a>

                <a href="admin_settings.php">
                    Settings
                </a>

            </nav>

            <div class="sidebar-foot">

                <p>
                    Signed in as
                    <?= htmlspecialchars($_SESSION['name']) ?>
                </p>

                <a href="index.php">
                    View site
                </a>
                <br>

                <a href="logout.php">
                    Log out
                </a>

            </div>

        </nav>


        <main class="admin-main">

            <div class="admin-header">

                <div>

                    <h1>Job categories</h1>

                    <p>
                        These populate the category picker on the homepage
                        and the job-posting form.
                    </p>

                </div>

            </div>


            <!-- ERROR / SUCCESS MESSAGE -->

            <?php if (!empty($errors)): ?>

                <div class="notice notice-error">

                    <ul>

                        <?php foreach ($errors as $error): ?>

                            <li>
                                <?= htmlspecialchars($error) ?>
                            </li>

                        <?php endforeach; ?>

                    </ul>

                </div>

            <?php elseif ($success): ?>

                <p class="notice notice-success">
                    <?= htmlspecialchars($success) ?>
                </p>

            <?php endif; ?>


            <!-- ADD CATEGORY -->

            <div class="panel">

                <h2>
                    Add a category
                </h2>

                <form method="post" class="admin-form">

                    <input
                        type="hidden"
                        name="action"
                        value="add"
                    >

                    <div class="field">

                        <label for="name">
                            Category name
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            placeholder="e.g. Pet care"
                            required
                        >

                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Add category
                    </button>

                </form>

            </div>


            <!-- ALL CATEGORIES -->

            <div class="panel">

                <h2>
                    All categories
                    (<?= count($categories) ?>)
                </h2>


                <?php if (empty($categories)): ?>

                    <p>
                        No categories yet.
                    </p>

                <?php else: ?>

                    <table class="admin-table">

                        <thead>

                            <tr>

                                <th>
                                    Name
                                </th>

                                <th>
                                    Open jobs
                                </th>

                                <th>
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach ($categories as $cat): ?>

                                <tr>

                                    <!-- CATEGORY NAME -->

                                    <td>

                                        <form
                                            method="post"
                                            style="display:flex;gap:8px;align-items:center;"
                                        >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="rename"
                                            >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= (int) $cat['id'] ?>"
                                            >

                                            <input
                                                type="text"
                                                name="name"
                                                value="<?= htmlspecialchars($cat['name']) ?>"
                                                style="padding:6px 8px;border:1px solid #DEDACD;border-radius:4px;font-size:0.88rem;"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-primary"
                                                style="padding:6px 12px;font-size:0.8rem;"
                                            >
                                                Save
                                            </button>

                                        </form>

                                    </td>


                                    <!-- OPEN JOB COUNT -->

                                    <td>
                                        <?= (int) $cat['job_count'] ?>
                                    </td>


                                    <!-- DELETE -->

                                    <td>

                                        <form
                                            method="post"
                                            onsubmit="return confirm('Remove this category? A category currently used by jobs cannot be deleted.');"
                                        >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="delete"
                                            >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= (int) $cat['id'] ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-danger"
                                            >
                                                Delete
                                            </button>

                                        </form>

                                    </td>

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

