<?php
require_once __DIR__ . '/../database/db.php';
session_start();

// Check if user is already logged in and has a role
$current_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'student';

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare(
        "SELECT * FROM user WHERE email = ? AND is_active = 1"
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role']; // This is 'alumni', 'student', etc.

        $pdo->prepare(
            "UPDATE user SET last_login = NOW() WHERE user_id = ?"
        )->execute([$user['user_id']]);

        header("Location: home.php");
        exit();
    } else {
        $error = "Invalid email or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Alumni Management</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="theme-<?php echo htmlspecialchars($current_role); ?>">

<div class="container" style="max-width: 400px; margin-top: 100px;">
    <h2>Login</h2>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="post" class="form">
        <label>Email Address</label>
        <input type="email" name="email" placeholder="Email" required>
        
        <label>Password</label>
        <input type="password" name="password" placeholder="Password" required>
        
        <button type="submit" class="btn">Login</button>
    </form>

    <p style="margin-top: 20px; text-align: center;">
        New here? <a href="signup.php">Create account</a>
    </p>
</div>

</body>
</html>