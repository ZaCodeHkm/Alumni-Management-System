<?php
session_start();
require_once 'auth.php';
require_once __DIR__ . '/../database/db.php';

require_login();

$jobId = $_GET['id'] ?? 0;
$userRole = $_SESSION['role'];
$userId = $_SESSION['user_id'];

// Fetch job details (NOW includes filled_by)
$stmt = $pdo->prepare("
    SELECT j.*, 
           u.name as posted_by_name, 
           u.role as posted_by_role,
           a.name as approved_by_name,
           f.name as filled_by_name
    FROM jobposting j
    LEFT JOIN user u ON j.posted_by = u.user_id
    LEFT JOIN user a ON j.approved_by = a.user_id
    LEFT JOIN user f ON j.filled_by = f.user_id
    WHERE j.job_id = ?
");
$stmt->execute([$jobId]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    header("Location: job_list.php");
    exit;
}

$isOwner = ($job['posted_by'] == $userId);

// Check if current user has already applied
$hasApplied = false;
$stmt = $pdo->prepare("SELECT * FROM job_application WHERE job_id = ? AND user_id = ?");
$stmt->execute([$jobId, $userId]);
$applicationRecord = $stmt->fetch(PDO::FETCH_ASSOC);
if ($applicationRecord) {
    $hasApplied = true;
}

// Get applicants if authorized
$applicants = [];
$canEdit = false;

if ($userRole === 'event_manager') {
    $canEdit = true;
} elseif ($userRole === 'alumni' && $job['posted_by'] == $userId) {
    $canEdit = true;
}

if ($canEdit) {
    $stmt = $pdo->prepare("
        SELECT ja.*, u.name, u.email, u.role 
        FROM job_application ja
        JOIN user u ON ja.user_id = u.user_id
        WHERE ja.job_id = ?
        ORDER BY ja.applied_at DESC
    ");
    $stmt->execute([$jobId]);
    $applicants = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$isApproved = ($job['status'] === 'approved');
$isAvailable = ($job['availability'] === 'available');

// ================= APPLY =================
$applySuccess = false;
$applyError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply'])) {

    if ($isOwner) {
        $applyError = "You cannot apply to your own job posting.";
    } elseif ($hasApplied) {
        $applyError = "You have already applied for this position.";
    } elseif (!$isApproved) {
        $applyError = "This job is not yet approved.";
    } elseif (!$isAvailable) {
        $applyError = "This position is no longer available.";
    } else {
        try {
            $pdo->beginTransaction();

            // Insert application
            $stmt = $pdo->prepare("
                INSERT INTO job_application (job_id, user_id, status) 
                VALUES (?, ?, 'pending')
            ");
            $stmt->execute([$jobId, $userId]);

            // Mark job as filled AND store who filled it
            $stmt = $pdo->prepare("
                UPDATE jobposting 
                SET availability = 'unavailable', filled_by = ? 
                WHERE job_id = ?
            ");
            $stmt->execute([$userId, $jobId]);

            $pdo->commit();

            header("Location: job_details.php?id=" . $jobId . "&success=applied");
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack();
            $applyError = "Database error occurred.";
        }
    }
}

// Toggle availability (owner/admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_availability']) && $canEdit) {

    if ($job['availability'] === 'available') {
        $newAvailability = 'unavailable';
    } else {
        $newAvailability = 'available';
    }

    $stmt = $pdo->prepare("
        UPDATE jobposting 
        SET availability = ?, filled_by = NULL
        WHERE job_id = ?
    ");
    $stmt->execute([$newAvailability, $jobId]);

    header("Location: job_details.php?id=" . $jobId);
    exit;
}

if (isset($_GET['success']) && $_GET['success'] === 'applied') {
    $applySuccess = true;
}
?>

<?php include 'navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="styles.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Details</title>
</head>
<body>

<main>
    <h1 class="page-title">Job Details</h1>

    <?php if ($applySuccess): ?>
        <div class="container">
            <div class="success-message">
                ✓ Your application has been submitted successfully!
            </div>
        </div>
    <?php endif; ?>

    <?php if ($applyError): ?>
        <div class="container">
            <div class="error-message">
                ⚠ <?= htmlspecialchars($applyError) ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="container">
        <h2><?= htmlspecialchars($job['title']) ?></h2>

        <section class="details-section">
            <p>
                <strong>Status:</strong> 
                <?= $isApproved ? "<span style='color: green;'>Approved</span>" 
                                : "<span style='color: orange;'>Pending</span>" ?>

                <?= $isAvailable 
                    ? "<span style='color: var(--accent);'> | Open</span>" 
                    : "<span style='color: #888;'> | Filled</span>" ?>
            </p>

            <p><strong>Company:</strong> <?= htmlspecialchars($job['company_name']) ?></p>

            <p><strong>Description:</strong></p>
            <div style="background-color:#525151; padding:15px; border-radius:5px;">
                <?= nl2br(htmlspecialchars($job['description'])) ?>
            </div>
        </section>

        <section class="details-section">
            <p><strong>Posted by:</strong> <?= htmlspecialchars($job['posted_by_name']) ?></p>

            <?php if ($job['approved_by_name']): ?>
                <p><strong>Approved by:</strong> <?= htmlspecialchars($job['approved_by_name']) ?></p>
            <?php endif; ?>

            <p><strong>Date:</strong> <?= date('F j, Y', strtotime($job['created_at'])) ?></p>
        </section>

        <!-- APPLY SECTION -->
        <?php if ($isOwner): ?>
            <div class="apply-section">
                <h3>Your Job Posting</h3>
                <p>You cannot apply to your own job posting.</p>
            </div>

        <?php elseif ($isApproved && $isAvailable && ($userRole === 'student' || $userRole === 'alumni')): ?>

            <?php if ($hasApplied): ?>
                <div class="apply-section">
                    <h3>✓ Application Submitted</h3>
                    <p>Status: <strong><?= ucfirst($applicationRecord['status']) ?></strong></p>
                </div>
            <?php else: ?>
                <div class="apply-section">
                    <h3>Interested in this position?</h3>
                    <form method="POST">
                        <button type="submit" name="apply" class="btn">
                            📩 Apply for this Position
                        </button>
                    </form>
                </div>
            <?php endif; ?>

        <?php elseif (!$isAvailable): ?>
            <div class="apply-section">
                <h3>Position Filled</h3>
                <p>
                    This position has been filled
                    <?php if (!empty($job['filled_by_name'])): ?>
                        by <strong><?= htmlspecialchars($job['filled_by_name']) ?></strong>.
                    <?php endif; ?>
                </p>
            </div>

        <?php elseif (!$isApproved): ?>
            <div class="apply-section">
                <h3>Pending Approval</h3>
                <p>This job posting is not yet accepting applications.</p>
            </div>
        <?php endif; ?>

        <div class="button-container">
            <a href="job_list.php" class="btn btn-secondary">← Back to Jobs</a>
        </div>

    </div>
</main>

<footer>
    <p>&copy; 2026 Alumni Engagement System</p>
</footer>

</body>
</html>
