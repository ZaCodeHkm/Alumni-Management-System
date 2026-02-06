<?php
require_once __DIR__ . '/../database/db.php';
session_start();

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $role = $_POST['role'];

    // passwords match checker
    if ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        // password hasher
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        
        // database query
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO user (email, password_hash, name, role)
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$email, $passwordHash, $name, $role]);

            $success = "Account created successfully. You may now log in.";
        } catch (PDOException $e) {
            $error = "Email already exists.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="styles.css">
    <title>Sign Up</title>
</head>
<body>

<div class="Sign-up">
    <h2>Sign Up</h2>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="post" class="form">

        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" required>
        </div>

        <div class="form-group">
            <label>Role</label>
            <select name="role" required>
                <option value="student">Student</option>
                <option value="alumni">Alumni</option>
            </select>
        </div>

        <button type="submit">Sign Up</button>
    </form>

    <p>Already have an account? <a href="login.php">Login</a></p>
</div>

</body>
</html>