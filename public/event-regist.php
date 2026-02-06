<?php
require_once __DIR__ . '/../database/db.php';
session_start();

// Error message function
function redirectWithError($message, $event_id) {
    $_SESSION['error'] = $message;
    header("Location: event-details.php?id=" . $event_id);
    exit;
}
// Success message function
function redirectWithSuccess($message, $event_id) {
    $_SESSION['success'] = $message;
    header("Location: event-details.php?id=" . $event_id);
    exit;
}

$event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
$user_id  = $_SESSION['user_id'] ?? null;

if (!$event_id || !$user_id) {
    redirectWithError("Invalid request.", $event_id);
}

/* Get event capacity */
$stmt = $pdo->prepare("SELECT capacity FROM event WHERE event_id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    redirectWithError("Event not found.", $event_id);
}

$capacity = (int)$event['capacity'];

/* Count current registrations */
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM event_attendance 
    WHERE event_id = ?
");
$stmt->execute([$event_id]);
$currentCount = (int)$stmt->fetchColumn();

/* Check if event is full */
if ($currentCount >= $capacity) {
    redirectWithError("Sorry, this event is already full.", $event_id);
}

/* Check if user already registered */
$stmt = $pdo->prepare("
    SELECT 1 
    FROM event_attendance 
    WHERE event_id = ? AND user_id = ?
");
$stmt->execute([$event_id, $user_id]);

if ($stmt->fetch()) {
    redirectWithError("You are already registered for this event.", $event_id);
}

/* Register user */
$stmt = $pdo->prepare("
    INSERT INTO event_attendance (event_id, user_id, status)
    VALUES (?, ?, 'registered')
");
$stmt->execute([$event_id, $user_id]);

redirectWithSuccess("You have successfully joined this event.", $event_id);
?>
