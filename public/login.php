<?php
require_once __DIR__ . '/../db.php';
session_start();

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
        $_SESSION['role'] = $user['role'];

        $pdo->prepare(
            "UPDATE user SET last_login = NOW() WHERE user_id = ?"
        )->execute([$user['user_id']]);

        header("Location: home.php");
        exit();
    } else {
        $error = "Invalid email or password";
    }
    
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['role'] = $user['role'];

    echo '<pre>';
    var_dump($_SESSION);
    echo '</pre>';
    exit;

}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Login</title>
</head>
<body>

<h2>Login</h2>

<?php if ($error): ?><p style="color:red;"><?php echo $error; ?></p><?php endif; ?>

<form method="post">
  <input type="email" name="email" placeholder="Email" required><br><br>
  <input type="password" name="password" placeholder="Password" required><br><br>
  <button type="submit">Login</button>
</form>

<p><a href="signup.php">Create account</a></p>

</body>
</html>
