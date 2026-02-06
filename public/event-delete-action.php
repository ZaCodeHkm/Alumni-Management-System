<?php
require_once __DIR__ . '/../database/db.php';
session_start();

/**
 * 1. Authorization check
 */
if (
    !isset($_SESSION['role']) ||
    !in_array($_SESSION['role'], ['event_manager'])
) {
    die('Unauthorized access.');
}

/**
 * 2. Validate input
 */
$event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);

if (!$event_id) {
    die('Invalid event ID.');
}

/**
 * 3. Delete event
 */
$sql = "DELETE FROM event WHERE event_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$event_id]);

/**
 * 4. Optional: check if anything was deleted
 */
if ($stmt->rowCount() === 0) {
    die('Event not found or already deleted.');
}

header('Location: events.php?deleted=1');
exit;
