<?php
session_start();
require_once 'auth.php';
require_once __DIR__ . '/../database/db.php';

require_login();

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];

// Determine what jobs to show based on role
if ($userRole === 'admin') {
    $stmt = $pdo->query("
        SELECT j.*, u.name as posted_by_name, a.name as approved_by_name,
               COUNT(DISTINCT ja.application_id) as application_count
        FROM jobposting j
        LEFT JOIN user u ON j.posted_by = u.user_id
        LEFT JOIN user a ON j.approved_by = a.user_id
        LEFT JOIN job_application ja ON j.job_id = ja.job_id
        WHERE j.status = 'approved'
        GROUP BY j.job_id
        ORDER BY j.created_at DESC
    ");
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($userRole === 'event_manager') {
    $stmt = $pdo->query("
        SELECT j.*, u.name as posted_by_name, a.name as approved_by_name,
               COUNT(DISTINCT ja.application_id) as application_count
        FROM jobposting j
        LEFT JOIN user u ON j.posted_by = u.user_id
        LEFT JOIN user a ON j.approved_by = a.user_id
        LEFT JOIN job_application ja ON j.job_id = ja.job_id
        GROUP BY j.job_id
        ORDER BY j.status ASC, j.created_at DESC
    ");
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($userRole === 'alumni') {
    $stmt = $pdo->prepare("
        SELECT j.*, u.name as posted_by_name, a.name as approved_by_name,
               COUNT(DISTINCT ja.application_id) as application_count,
               EXISTS(SELECT 1 FROM job_application WHERE job_id = j.job_id AND user_id = ?) as has_applied
        FROM jobposting j
        LEFT JOIN user u ON j.posted_by = u.user_id
        LEFT JOIN user a ON j.approved_by = a.user_id
        LEFT JOIN job_application ja ON j.job_id = ja.job_id
        WHERE j.status = 'approved' OR j.posted_by = ?
        GROUP BY j.job_id
        ORDER BY j.created_at DESC
    ");
    $stmt->execute([$userId, $userId]);
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($userRole === 'student') {
    $stmt = $pdo->prepare("
        SELECT j.*, u.name as posted_by_name, a.name as approved_by_name,
               COUNT(DISTINCT ja.application_id) as application_count,
               EXISTS(SELECT 1 FROM job_application WHERE job_id = j.job_id AND user_id = ?) as has_applied
        FROM jobposting j
        LEFT JOIN user u ON j.posted_by = u.user_id
        LEFT JOIN user a ON j.approved_by = a.user_id
        LEFT JOIN job_application ja ON j.job_id = ja.job_id
        WHERE j.status = 'approved'
        GROUP BY j.job_id
        ORDER BY j.created_at DESC
    ");
    $stmt->execute([$userId]);
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    header("Location: home.php");
    exit;
}
?>

<?php include 'navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="styles.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Postings</title>
</head>
<body>

    <main>
        <h1 class="page-title">Job Postings</h1>

        <?php if ($userRole === 'alumni' || $userRole === 'event_manager'): ?>
        <div class="container">
            <a href="job_create.php" class="btn">Post a Job</a>
        </div>
        <?php endif; ?>

        <?php if ($userRole === 'event_manager'): ?>
        <div class="container">
            <h3>Pending Approval</h3>
            <?php 
            $pendingJobs = array_filter($jobs, function($job) {
                return $job['status'] === 'pending';
            });
            
            if (empty($pendingJobs)): ?>
                <p>No pending jobs.</p>
            <?php else: ?>
                <?php foreach ($pendingJobs as $job): ?>
                <article class="list-item" style="border-left: 4px solid orange;">
                    <div class="flex-between">
                        <h2><?= htmlspecialchars($job['title']) ?></h2>
                        <div>
                            <a href="job_details.php?id=<?= $job['job_id'] ?>" class="btn btn-secondary">View</a>
                            <a href="job_approve.php?id=<?= $job['job_id'] ?>&action=approve" class="btn">Approve</a>
                            <a href="job_approve.php?id=<?= $job['job_id'] ?>&action=reject" class="btn btn-danger">Reject</a>
                        </div>
                    </div>
                    <p><strong>Company:</strong> <?= htmlspecialchars($job['company_name']) ?></p>
                    <p><strong>Posted by:</strong> <?= htmlspecialchars($job['posted_by_name']) ?></p>
                    <p><strong>Status:</strong> <span style="color: orange;">Pending Approval</span></p>
                    <p><strong>Description:</strong> <?= htmlspecialchars(substr($job['description'], 0, 150)) ?>...</p>
                </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <section class="list-container">
            <h3>Available Jobs</h3>
            
            <?php 
            $approvedJobs = array_filter($jobs, function($job) {
                return $job['status'] === 'approved';
            });
            
            if (empty($approvedJobs)): ?>
                <p>No job postings available.</p>
            <?php else: ?>
                <?php foreach ($approvedJobs as $job): ?>
                <article class="list-item">
                    <div class="flex-between">
                        <h2>
                            <?= htmlspecialchars($job['title']) ?>
                            <?php if (isset($job['has_applied']) && $job['has_applied']): ?>
                                <span style="background: #d4edda; color: #155724; padding: 3px 8px; border-radius: 3px; font-size: 0.8em; margin-left: 10px;">✓ Applied</span>
                            <?php endif; ?>
                            <?php if (($userRole === 'event_manager' || ($userRole === 'alumni' && $job['posted_by'] == $userId)) && $job['application_count'] > 0): ?>
                                <span style="background: #cce5ff; color: #004085; padding: 3px 8px; border-radius: 3px; font-size: 0.8em; margin-left: 10px;">
                                    <?= $job['application_count'] ?> applicant<?= $job['application_count'] != 1 ? 's' : '' ?>
                                </span>
                            <?php endif; ?>
                        </h2>
                        <a href="job_details.php?id=<?= $job['job_id'] ?>" class="card-link">View Details</a>
                    </div>
                    <p><strong>Company:</strong> <?= htmlspecialchars($job['company_name']) ?></p>
                    <p><strong>Description:</strong> <?= htmlspecialchars(substr($job['description'], 0, 150)) ?>...</p>
                    <p>
                        <strong>Availability:</strong> 
                        <span style="color: <?= $job['availability'] === 'available' ? 'var(--accent)' : '#888' ?>;">
                            <?= $job['availability'] === 'available' ? '📢 Open' : '✕ Filled' ?>
                        </span>
                    </p>
                </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 Alumni Engagement System</p>
    </footer>

</body>
</html>