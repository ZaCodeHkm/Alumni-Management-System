<?php
session_start();
require_once 'auth.php';
require_once __DIR__ . '/../database/db.php';

// Require login
require_login();

$userId = $_SESSION['user_id'];

/* 1. Load User Base (For Theme and Header) 
   FIX: Using 'user_id' instead of 'id' to prevent the Fatal Error */
$stmt = $pdo->prepare("SELECT name, role FROM user WHERE user_id = ?");
$stmt->execute([$userId]);
$userBase = $stmt->fetch(PDO::FETCH_ASSOC);

/* 2. Load profile details */
$stmt = $pdo->prepare("
    SELECT bio, education, contact_info, publications_summary
    FROM profile
    WHERE user_id = ?
");
$stmt->execute([$userId]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

// Default empty values if no profile exists yet
if (!$profile) {
    $profile = [
        'bio' => '',
        'education' => '',
        'contact_info' => '',
        'publications_summary' => ''
    ];
}

/* 3. Load career history */
$stmt = $pdo->prepare("
    SELECT job_title, company_name, start_date, end_date, description
    FROM career_history
    WHERE user_id = ?
    ORDER BY start_date DESC
");
$stmt->execute([$userId]);
$careers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>My Profile | Alumni Portal</title>
</head>
<body class="theme-<?= htmlspecialchars($userBase['role'] ?? 'student') ?>">

    <?php include 'navbar.php'; ?>

    <main>
        <div class="container">
            
            <div class="profile-header">
                <div class="profile-info">
                    <h1><?= htmlspecialchars($userBase['name'] ?? 'User') ?></h1>
                    <span class="badge badge-accent"><?= strtoupper($userBase['role'] ?? 'Member') ?></span>
                </div>
                <div style="margin-left: auto;">
                    <a href="profileEdit.php" class="btn">Edit Profile</a>
                </div>
            </div>

            <div class="dashboard-grid">
                
                <div class="card">
                    <h3>Bio</h3>
                    <p><?= nl2br(htmlspecialchars($profile['bio'])) ?: '<span class="text-secondary">Not provided</span>' ?></p>
                </div>

                <div class="card">
                    <h3>Education</h3>
                    <p><?= htmlspecialchars($profile['education']) ?: '<span class="text-secondary">Not provided</span>' ?></p>
                    
                    <h3 class="mt-2">Contact Information</h3>
                    <p><?= htmlspecialchars($profile['contact_info']) ?: '<span class="text-secondary">Not provided</span>' ?></p>
                </div>

            </div>

            <div class="card mt-3">
                <h3>Career History</h3>
                <?php if (empty($careers)): ?>
                    <p class="text-secondary">No career history added yet.</p>
                <?php else: ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Job Title & Company</th>
                                    <th>Duration</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($careers as $career): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($career['job_title']) ?></strong><br>
                                            <small class="text-secondary"><?= htmlspecialchars($career['company_name']) ?></small>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($career['start_date']) ?> — 
                                            <?= $career['end_date'] ? htmlspecialchars($career['end_date']) : 'Present' ?>
                                        </td>
                                        <td>
                                            <?= nl2br(htmlspecialchars($career['description'] ?? '')) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card mt-3">
                <h3>Publications Summary</h3>
                <p><?= nl2br(htmlspecialchars($profile['publications_summary'])) ?: '<span class="text-secondary">No summary available</span>' ?></p>
            </div>

        </div>
    </main>

    <footer>
        <p>&copy; 2026 Alumni Association Portal</p>
    </footer>

</body>
</html>