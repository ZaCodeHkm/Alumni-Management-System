<?php
session_start();
require_once 'auth.php';
require_once __DIR__ . '/../database/db.php';

// Only event managers can approve
require_role('event_manager');

$jobId = $_GET['id'] ?? 0;
$action = $_GET['action'] ?? '';

if ($action === 'approve') {
    $stmt = $pdo->prepare("
        UPDATE jobposting 
        SET status = 'approved', 
            approved_by = ?
        WHERE job_id = ?
    ");
    $stmt->execute([$_SESSION['user_id'], $jobId]);
    
} elseif ($action === 'reject') {
    $stmt = $pdo->prepare("
        UPDATE jobposting 
        SET status = 'rejected'
        WHERE job_id = ?
    ");
    $stmt->execute([$jobId]);
}

header("Location: job_list.php");
exit;