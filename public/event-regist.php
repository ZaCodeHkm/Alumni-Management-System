<?php
require_once __DIR__ . '/../database/db.php';
session_start();


$event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
$user_id  = $_SESSION['user_id'] ?? null;

if (!$event_id || !$user_id) {
    die("Invalid request.");
}

$stmt = $pdo->prepare(" INSERT INTO event_attendance (event_id, user_id, status) VALUES (?, ?, 'registered')");
$stmt->execute([$event_id, $user_id]);
?>

<!-- to add a check for existing registered user -->
 
<!DOCTYPE html>
<html>
<head>
    <title>Event Registration</title>
</head>
<body>
    <h1>Event Registration Successful</h1>
    Event ID is: <?= htmlspecialchars($event_id); ?>
    User ID is: <?= htmlspecialchars($user_id); ?>

    <a href="events.php">Return to Events Page</a>
</body>

</html>