<?php
require_once __DIR__ . '/../db.php';
require_once 'admin_c.php';

/* Fetch all users */
$stmt = $pdo->query("
    SELECT user_id, name, email, role, is_active, last_login, created_at
    FROM user
    ORDER BY created_at DESC
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'navbar.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin – User Management</title>
</head>
<body>

<h2>User Management</h2>

<table border="1" cellpadding="6">
<tr>
    <th>Name</th>
    <th>Email</th>
    <th>Role</th>
    <th>Status</th>
    <th>Last Login</th>
    <th>Actions</th>
</tr>

<?php foreach ($users as $u): ?>
<tr>
    <td><?= htmlspecialchars($u['name']) ?></td>
    <td><?= htmlspecialchars($u['email']) ?></td>
    <td><?= htmlspecialchars($u['role']) ?></td>
    <td><?= $u['is_active'] ? 'Active' : 'Disabled' ?></td>
    <td><?= $u['last_login'] ?: 'Never' ?></td>
    <td>
        <?php if ($u['is_active']): ?>
            <a href="admin_toggle_user.php?id=<?= $u['user_id'] ?>&action=disable">
                Disable
            </a>
        <?php else: ?>
            <a href="admin_toggle_user.php?id=<?= $u['user_id'] ?>&action=enable">
                Enable
            </a>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</table>

</body>
</html>
