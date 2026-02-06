<?php
require_once __DIR__ . '/../database/db.php';
session_start();

// Retrieve and sanitize form inputs from request page
$event_title = filter_input(INPUT_POST, 'event_title', FILTER_SANITIZE_STRING);
$description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
$event_date = filter_input(INPUT_POST, 'event_date', FILTER_SANITIZE_STRING);
$location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
$start_time = filter_input(INPUT_POST, 'start_time', FILTER_SANITIZE_STRING);
$end_time = filter_input(INPUT_POST, 'end_time', FILTER_SANITIZE_STRING);
$capacity = filter_input(INPUT_POST, 'capacity', FILTER_SANITIZE_NUMBER_INT);
$type = filter_input(INPUT_POST, 'event_type', FILTER_SANITIZE_STRING);
$is_alumni_exclusive = filter_input(INPUT_POST, 'is_alumni_exclusive', FILTER_SANITIZE_NUMBER_INT);
$user_id  = $_SESSION['user_id'] ?? null;

if (!$event_title || !$user_id) {
    echo "!FOR TESTING! Event title: " . htmlspecialchars($event_title);
    echo "!FOR TESTING! User ID: " . htmlspecialchars($user_id);
    die("Invalid request.");
}

$stmt = $pdo->prepare(" INSERT INTO event (title, description, event_date, location, type, start_time, end_time, capacity, is_alumni_exclusive, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$event_title, $description, $event_date, $location, $type, $start_time, $end_time, $capacity, $is_alumni_exclusive, $user_id]);
$event_id = $pdo->lastInsertId();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Event Request</title>
</head>
<body>
    <h1>Event Request Successful</h1>
    Event ID is: <?= htmlspecialchars($event_id); ?>
    User ID is: <?= htmlspecialchars($user_id); ?>

    <a href="events.php">Return to Events Page</a>
</body>

</html>