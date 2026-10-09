
<?php
require_once 'db.php';
session_start();

// Only logged-in admins can access this page.
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_dashboard.php');
    exit;
}


// =====================================================
// DELETE MESSAGE
// =====================================================

if (isset($_GET['delete'])) {

    $id = (int) $_GET['delete'];

    $stmt = $pdo->prepare("DELETE FROM contact WHERE id = ?");
    $stmt->execute([$id]);

    header('Location: admin_contact.php');
    exit;
}


// =====================================================
// GET CONTACT MESSAGES
// =====================================================

$stmt = $pdo->query("
    SELECT
        c.id,
        c.user_id,
        c.name,
        c.email,
        c.message,
        c.created_at,
        u.name AS user_name
    FROM contact c
    LEFT JOIN users u ON c.user_id = u.id
    ORDER BY c.created_at DESC
");

$messages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Messages — Flexiwork Admin</title>

    <link rel="stylesheet" href="admin.css">

</head>


<body>

    <div class="admin-layout">


        <!-- =========================================
             SIDEBAR
        ========================================== -->

        <nav class="sidebar">

            <a href="admin_dashboard.php" class="brand">
                Flexi<span>work</span> Admin
            </a>


            <nav>

                <a href="admin_dashboard.php">
                    Dashboard
                </a>

                <a href="admin_categories.php">
                    Job categories
                </a>

                <a href="admin_users.php">
                    Users
                </a>

                <a href="admin_contact.php" class="active">
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


        <!-- =========================================
             MAIN CONTENT
        ========================================== -->

        <main class="admin-main">

            <div class="admin-header">

                <div>

                    <h1>Messages</h1>

                    <p>
                        Messages submitted through the Contact Us page.
                    </p>

                </div>

            </div>


            <!-- =========================================
                 MESSAGE PANEL
        ========================================== -->

            <div class="panel">

                <h2>Contact Messages</h2>


                <?php if (empty($messages)): ?>

                    <p class="empty-message">
                        No messages yet.
                    </p>

                <?php else: ?>

                    <table class="admin-table">

                        <thead>

                            <tr>

                                <th>User</th>

                                <th>Email</th>

                                <th>Message</th>

                                <th>Received</th>

                                <th>Action</th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach ($messages as $message): ?>

                                <tr>

                                    <td>

                                        <?php if (!empty($message['user_id'])): ?>

                                            <?= htmlspecialchars(
                                                $message['user_name'] ?? $message['name']
                                            ) ?>

                                        <?php else: ?>

                                            Visitor

                                        <?php endif; ?>

                                    </td>


                                    <td class="message-email">

                                        <?= htmlspecialchars(
                                            $message['email']
                                        ) ?>

                                    </td>


                                    <td class="message-text">

                                        <?= htmlspecialchars(
                                            $message['message']
                                        ) ?>

                                    </td>


                                    <td class="message-date">

                                        <?= date(
                                            'M j, Y',
                                            strtotime($message['created_at'])
                                        ) ?>

                                        <br>

                                        <?= date(
                                            'g:i A',
                                            strtotime($message['created_at'])
                                        ) ?>

                                    </td>


                                    <td>

                                        <a
                                            href="admin_contact.php?delete=<?= (int) $message['id'] ?>"
                                            class="delete-message"
                                            onclick="return confirm('Are you sure you want to delete this message?');">

                                            Delete

                                        </a>

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

