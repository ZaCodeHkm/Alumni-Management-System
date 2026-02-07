<?php
session_start();
require_once 'auth.php';
require_once __DIR__ . '/../database/db.php';

// Only alumni and eventmanager can create jobs
require_role(['alumni', 'event_manager']);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['job_title']);
    $companyName = trim($_POST['company_name']);
    $description = trim($_POST['job_description']);
    
    $postedBy = $_SESSION['user_id'];
    
    // EventManager jobs are auto-approved
    if ($_SESSION['role'] === 'eventmanager') {
        $status = 'approved';
        $approvedBy = $_SESSION['user_id'];
    } else {
        // Alumni jobs need approval
        $status = 'pending';
        $approvedBy = null;
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO jobposting 
            (title, description, company_name, posted_by, status, approved_by)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $title, $description, $companyName, $postedBy, $status, $approvedBy
        ]);
        
        if ($status === 'approved') {
            $success = "Job posted successfully!";
        } else {
            $success = "Job submitted for approval. You will be notified once it's approved.";
        }
        
        // Redirect after 2 seconds
        header("refresh:2;url=job_list.php");
        
    } catch (PDOException $e) {
        $error = "Error posting job: " . $e->getMessage();
    }
}
?>

<?php include 'navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="styles.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Job</title>
</head>
<body>

    <main>
        <h1 class="page-title">Post a Job</h1>

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
                <input type="text" name="job_title" placeholder="Enter job title..." required>

                <label>Company Name</label>
                <input type="text" name="company_name" placeholder="Enter company name..." required>

                <label>Job Description</label>
                <textarea name="job_description" placeholder="Describe the job role, requirements, and responsibilities..." required></textarea>

                <div class="button-container">
                    <a href="job_list.php" class="btn btn-secondary">Cancel</a>
                    <button class="btn" type="submit">Post Job</button>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 Alumni Engagement System</p>
    </footer>

</body>
</html>