<?php
session_start();
require_once 'auth.php';
require_once __DIR__ . '/../database/db.php';

require_login();

$jobId = $_GET['id'] ?? 0;
$userRole = $_SESSION['role'];
$userId = $_SESSION['user_id'];

// Fetch job details
$stmt = $pdo->prepare("SELECT * FROM jobposting WHERE job_id = ?");
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

if (!$canEdit) {
    header("Location: job_list.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['job_title']);
    $companyName = trim($_POST['company_name']);
    $description = trim($_POST['job_description']);
    $availability = $_POST['availability'] ?? $job['availability'];
    
    try {
        $stmt = $pdo->prepare("
            UPDATE jobposting 
            SET title = ?, description = ?, company_name = ?, availability = ?
            WHERE job_id = ?
        ");
        
        $stmt->execute([
            $title, $description, $companyName, $availability, $jobId
        ]);
        
        $success = "Job updated successfully!";
        
        // Refresh job data
        $stmt = $pdo->prepare("SELECT * FROM jobposting WHERE job_id = ?");
        $stmt->execute([$jobId]);
        $job = $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $error = "Error updating job: " . $e->getMessage();
    }
}
?>

<?php include 'navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Job</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <main>
        <h1 class="page-title">Edit Job</h1>

        <div class="container">
            <?php if ($error): ?>
                <p style="color:red;"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <p style="color:green;"><?= htmlspecialchars($success) ?></p>
            <?php endif; ?>
            
            <form class="form" method="post">
                <h2>Job Details</h2>

                <label>Job Title</label>
                <input type="text" name="job_title" value="<?= htmlspecialchars($job['title']) ?>" required>

                <label>Company Name</label>
                <input type="text" name="company_name" value="<?= htmlspecialchars($job['company_name']) ?>" required>

                <label>Job Description</label>
                <textarea name="job_description" required><?= htmlspecialchars($job['description']) ?></textarea>

                <label>Availability Status</label>
                <select name="availability" required>
                    <option value="available" <?= $job['availability'] === 'available' ? 'selected' : '' ?>>Available</option>
                    <option value="filled" <?= $job['availability'] === 'filled' ? 'selected' : '' ?>>Filled</option>
                    <option value="closed" <?= $job['availability'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                </select>

                <div class="button-container">
                    <a href="job_details.php?id=<?= $jobId ?>" class="btn btn-secondary">Cancel</a>
                    <button class="btn" type="submit">Save Changes</button>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 Alumni Engagement System</p>
    </footer>

</body>
</html>