<?php
session_start();
require_once 'auth.php';
require_once __DIR__ . '/../database/db.php';

// Require admin role
require_role(allowed_roles: 'admin');

/* Fetch active users */
$stmt = $pdo->query("
    SELECT user_id, name, email, role, is_active, last_login, created_at
    FROM user
    ORDER BY created_at DESC
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* MOCK pending verification users */
$pending_users = [
    [
        'user_id' => 101,
        'name' => 'John Pending',
        'email' => 'johnpending@example.com',
        'role' => 'student',
        'created_at' => '2026-01-20'
    ],
    [
        'user_id' => 102,
        'name' => 'Alice Alumni',
        'email' => 'alicealumni@example.com',
        'role' => 'alumni',
        'created_at' => '2026-01-22'
    ]
];
?>

<?php include 'navbar.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin – User Management</title>
</head>
<body>

<h2>Users Pending Verification</h2>

<table border="1" cellpadding="6">
<tr>
    <th>Name</th>
    <th>Email</th>
    <th>Role</th>
    <th>Registered On</th>
    <th>Actions</th>
</tr>

<?php foreach ($pending_users as $pu): ?>
<tr>
    <td><?= htmlspecialchars($pu['name']) ?></td>
    <td><?= htmlspecialchars($pu['email']) ?></td>
    <td><?= htmlspecialchars($pu['role']) ?></td>
    <td><?= $pu['created_at'] ?></td>
    <td>
        <a href="#">Verify</a> |
        <a href="#">Reject</a>
    </td>
</tr>
<?php endforeach; ?>
</table>

<br><br>

<h2>All Verified Users</h2>

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
    <td><?= $u['is_active'] ? 'Active' : 'Suspended' ?></td>
    <td><?= $u['last_login'] ?: 'Never' ?></td>
    <td>
        <a href="#">Suspend</a> |
        <a href="#">Unsuspend</a> |
        <a href="#" style="color:red;">Delete</a>
    </td>
</tr>
<?php endforeach; ?>
</table>

</body>
</html>