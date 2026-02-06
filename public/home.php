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
    <link rel="stylesheet" href="styles.css">
    <title>Home</title>
</head>
<body>

<h2>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>



<a href="logout.php">Logout</a>

</body>
</html>