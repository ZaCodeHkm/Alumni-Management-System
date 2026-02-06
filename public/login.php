<?php
require_once __DIR__ . '/../database/db.php';
session_start();

// Check if user is already logged in and has a role
$current_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'student';

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Fetch user from database
    $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        // Check if account is verified (is_active = 1)
        if ($user['is_active'] == 0) {
            $error = "Your account is pending admin verification. Please wait for approval.";
        } else {
            // Update last_login timestamp
            $updateStmt = $pdo->prepare("UPDATE user SET last_login = NOW() WHERE user_id = ?");
            $updateStmt->execute([$user['user_id']]);
            
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect to home page
            header("Location: home.php");
            exit();
        }
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Alumni Network</title>
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

        <!-- Right Side: Form -->
        <div class="auth-form-section">
            <div class="auth-card">
                <div class="auth-header">
                    <h1>Welcome Back</h1>
                    <p>Sign in to your account</p>
                </div>

                <!-- Display error message -->
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="post" class="form">
                    <div>
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="you@example.com" required>
                    </div>

                    <div>
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>

                    <button type="submit" class="btn">Sign In</button>
                </form>

                <div class="auth-footer">
                    <p>Don't have an account? <a href="signup.php">Create one</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>