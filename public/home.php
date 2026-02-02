<?php
session_start();
require_once 'auth.php';

// Require any logged-in user
require_login();
?>

<?php include 'navbar.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
</head>
<body>

<h2>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>

<p>This is the homepage.</p>
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <a href="user_check.php" style="text-decoration:none; color:black; font-weight:bold;">
            Admin
        </a>
    <?php endif; ?>

<a href="logout.php">Logout</a>

</body>
</html>