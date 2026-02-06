<?php
require_once __DIR__ . '/../database/db.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    if (!isset($_SESSION['user_id'])) {
        die('You must be logged in to comment.');
    }

    $content = trim($_POST['content']);
    $event_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    if ($content !== '') {
        $insertSql = "
            INSERT INTO comments (event_id, user_id, content, created_at)
            VALUES (?, ?, ?, NOW())
        ";

        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->execute([$event_id, $user_id, $content]);

        // Prevent form resubmission
        header("Location: event-details.php?id=$event_id");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Commenting</title>
</head>
<body>
    <h1>Comment Submitted Successfully</h1>
    Comment ID is: <?= htmlspecialchars($comment_id); ?>
    User ID is: <?= htmlspecialchars($user_id); ?>

    <a href="event-details.php?id=<?= $event_id ?>">Return to Event Details</a>
</body>

</html>