<?php
require_once __DIR__ . '/../database/db.php';
session_start();

$event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
$user_id  = $_SESSION['user_id'] ?? null;

if (!$event_id || !$user_id) {
    die("Invalid request.");
}

/* 1️⃣ Get event capacity */
$stmt = $pdo->prepare("SELECT capacity FROM event WHERE event_id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    die("Event not found.");
}

$capacity = (int)$event['capacity'];

/* 2️⃣ Count current registrations */
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM event_attendance 
    WHERE event_id = ?
");
$stmt->execute([$event_id]);
$currentCount = (int)$stmt->fetchColumn();

/* 3️⃣ Check if event is full */
if ($currentCount >= $capacity) {
    header("Location: event-details.php?id=$event_id&error=event_full");
    echo "Event is full. Cannot register.";
    exit();
}

/* 4️⃣ Check if user already registered */
$stmt = $pdo->prepare("
    SELECT 1 
    FROM event_attendance 
    WHERE event_id = ? AND user_id = ?
");
$stmt->execute([$event_id, $user_id]);

if ($stmt->fetch()) {
    die("You are already registered for this event.");
}

/* 5️⃣ Register user */
$stmt = $pdo->prepare("
    INSERT INTO event_attendance (event_id, user_id, status)
    VALUES (?, ?, 'registered')
");
$stmt->execute([$event_id, $user_id]);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Event Registration</title>
</head>
<body>
    <h1>Event Registration Successful</h1>
    Event ID is: <?= htmlspecialchars($event_id); ?><br>
    User ID is: <?= htmlspecialchars($user_id); ?>

    <br>
    <?php header("Location: event-details.php?id=$event_id"); 
            exit()?>
</body>
</html>
