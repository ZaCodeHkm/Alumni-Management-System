<?php
require_once __DIR__ . '/../db.php';
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
      // pasword hasher
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        //database query
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
    <title>Sign Up</title>
</head>
<body>

<h2>Sign Up</h2>

<?php if ($error): ?>
    <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<?php if ($success): ?>
    <p style="color:green;"><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>

<form method="post">

    <label>Name</label><br>
    <input type="text" name="name" required><br><br>

    <label>Email</label><br>
    <input type="email" name="email" required><br><br>

    <label>Password</label><br>
    <input type="password" name="password" required><br><br>

    <label>Confirm Password</label><br>
    <input type="password" name="confirm_password" required><br><br>

    <label>Role</label><br>
    <select name="role" required>
        <option value="student">Student</option>
        <option value="alumni">Alumni</option>
    </select><br><br>

    <button type="submit">Sign Up</button>
</form>

<p>Already have an account? <a href="login.php">Login</a></p>

</body>
</html>
