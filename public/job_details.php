<?php
session_start();
require_once 'auth.php';
require_once __DIR__ . '/../database/db.php';

require_login();

$jobId = $_GET['id'] ?? 0;
$userRole = $_SESSION['role'];
$userId = $_SESSION['user_id'];

// Fetch job details
$stmt = $pdo->prepare("
    SELECT j.*, u.name as posted_by_name, u.role as posted_by_role,
           a.name as approved_by_name
    FROM JobPosting j
    LEFT JOIN User u ON j.posted_by = u.user_id
    LEFT JOIN User a ON j.approved_by = a.user_id
    WHERE j.job_id = ?
");
$stmt->execute([$jobId]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    header("Location: job_list.php");
    exit;
}

// Check if user can edit this job
$canEdit = false;
if ($userRole === 'event_manager') {
    $canEdit = true;
} elseif ($userRole === 'alumni' && $job['posted_by'] == $userId) {
    $canEdit = true;
}
?>

<?php include 'navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Details</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <main>
        <h1 class="page-title">Job Details</h1>

        <div class="container">
            <h2><?= htmlspecialchars($job['title']) ?></h2>
            
            <section class="details-section">
                <p><strong>Status:</strong> 
                    <span style="color: <?= $job['status'] === 'approved' ? 'green' : 'orange' ?>;">
                        <?= ucfirst($job['status']) ?>
                    </span>
                </p>
                <p><strong>Availability:</strong> 
                    <span style="color: var(--accent);"><?= ucfirst($job['availability']) ?></span>
                </p>
                <p><strong>Company:</strong> <?= htmlspecialchars($job['company_name']) ?></p>
                <p><strong>Description:</strong></p>
                <p><?= nl2br(htmlspecialchars($job['description'])) ?></p>
            </section>

            <section class="details-section">
                <p class="creation-details"><strong>Posted by:</strong> <?= htmlspecialchars($job['posted_by_name']) ?> (<?= ucfirst($job['posted_by_role']) ?>)</p>
                <?php if ($job['approved_by_name']): ?>
                    <p class="creation-details"><strong>Approved by:</strong> <?= htmlspecialchars($job['approved_by_name']) ?></p>
                <?php endif; ?>
                <p class="creation-details"><strong>Date created:</strong> <?= htmlspecialchars($job['created_at']) ?></p>
            </section>

            <div class="button-container">
                <a href="job_list.php" class="btn btn-secondary">Back to Jobs</a>
                
                <?php if ($canEdit): ?>
                    <a href="job_edit.php?id=<?= $job['job_id'] ?>" class="btn">Edit Listing</a>
                    <a href="job_delete.php?id=<?= $job['job_id'] ?>" 
                       class="btn btn-danger" 
                       onclick="return confirm('Are you sure you want to delete this job?')">
                       Delete Listing
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 Alumni Engagement System</p>
    </footer>

</body>
</html>