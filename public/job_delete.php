<?php
session_start();
require_once 'auth.php';
require_once __DIR__ . '/../database/db.php';

require_login();

$jobId = $_GET['id'] ?? 0;
$userRole = $_SESSION['role'];
$userId = $_SESSION['user_id'];

// Fetch job to check ownership
$stmt = $pdo->prepare("SELECT * FROM JobPosting WHERE job_id = ?");
$stmt->execute([$jobId]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    header("Location: job_list.php");
    exit;
}

// Check if user can delete this job
$canDelete = false;
if ($userRole === 'event_manager') {
    $canDelete = true;
} elseif ($userRole === 'alumni' && $job['posted_by'] == $userId) {
    $canDelete = true;
}

if (!$canDelete) {
    header("Location: job_list.php");
    exit;
}

// Delete the job
$stmt = $pdo->prepare("DELETE FROM JobPosting WHERE job_id = ?");
$stmt->execute([$jobId]);

header("Location: job_list.php");
exit;