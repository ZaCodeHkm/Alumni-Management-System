<?php
session_start();


// Block access if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<?php include 'navbar.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>

<h2>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>

<p>This is the homepage.</p>


<a href="logout.php">Logout</a>

</body>
</html>
