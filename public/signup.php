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

    if ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        
        try {
            // Insert new user with is_active = 0 (pending verification)
            $stmt = $pdo->prepare(
                "INSERT INTO user (email, password_hash, name, role, is_active)
                 VALUES (?, ?, ?, ?, 0)"
            );
            $stmt->execute([$email, $passwordHash, $name, $role]);
            $success = "Account created successfully! Please wait for admin verification before logging in.";
        } catch (PDOException $e) {
            $error = "Email already exists.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up — Alumni Network</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Adobe-style split layout -->
    <div class="auth-container">
        <!-- Left Side: Branding -->
        <div class="auth-branding">
            <div class="auth-logo">ALUMNI ENGAGEMENT SYSTEM</div>
            <p class="auth-tagline">Connect with professionals, mentors, and peers in your network</p>
        </div>

<<<<<<< Updated upstream
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
=======
        <!-- Right Side: Form -->
        <div class="auth-form-section">
            <div class="auth-card">
                <div class="auth-header">
                    <h1>Create Account</h1>
                    <p>Join the professional network</p>
                </div>

                <!-- Display error/success messages -->
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <form method="post" class="form">
                    <div>
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" placeholder="John Doe" required>
                    </div>

                    <div>
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="you@example.com" required>
                    </div>

                    <div>
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Create a password" required>
                    </div>

                    <div>
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat your password" required>
                    </div>

                    <div>
                        <label for="role">Your Role</label>
                        <select id="role" name="role" required>
                            <option value="student">Student</option>
                            <option value="alumni">Alumni</option>
                        </select>
                    </div>

                    <button type="submit" class="btn">Create Account</button>
                </form>
>>>>>>> Stashed changes

                <div class="auth-footer">
                    <p>Already have an account? <a href="login.php">Sign in</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>