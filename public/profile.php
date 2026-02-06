<?php
session_start();
require_once 'auth.php';
require_once __DIR__ . '/../database/db.php';

// Require login
require_login();

$userId = $_SESSION['user_id'];

/* Load profile */
$stmt = $pdo->prepare("
    SELECT bio, education, contact_info, publications_summary
    FROM profile
    WHERE user_id = ?
");
$stmt->execute([$userId]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profile) {
    $profile = [
        'bio' => '',
        'education' => '',
        'contact_info' => '',
        'publications_summary' => ''
    ];
}

/* Load career history */
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
    <title>My Profile</title>
</head>
<body>

<?php include 'navbar.php'; ?>

<main>
    <div class="page-title">
        <h1>My Profile</h1>
    </div>

    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 16px;">
            <h2 style="margin: 0;">Profile Information</h2>
            <a href="profileEdit.php" class="btn">Edit Profile</a>
        </div>

        <div class="profile-details">
            <div class="detail-row">
                <span class="detail-label">Bio</span>
                <span class="detail-value">
                    <?php if (!empty($profile['bio'])): ?>
                        <?= nl2br(htmlspecialchars($profile['bio'])) ?>
                    <?php else: ?>
                        <span style="color: var(--text-secondary);">Not provided</span>
                    <?php endif; ?>
                </span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Education</span>
                <span class="detail-value">
                    <?php if (!empty($profile['education'])): ?>
                        <?= htmlspecialchars($profile['education']) ?>
                    <?php else: ?>
                        <span style="color: var(--text-secondary);">Not provided</span>
                    <?php endif; ?>
                </span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Contact Information</span>
                <span class="detail-value">
                    <?php if (!empty($profile['contact_info'])): ?>
                        <?= htmlspecialchars($profile['contact_info']) ?>
                    <?php else: ?>
                        <span style="color: var(--text-secondary);">Not provided</span>
                    <?php endif; ?>
                </span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Publications Summary</span>
                <span class="detail-value">
                    <?php if (!empty($profile['publications_summary'])): ?>
                        <?= nl2br(htmlspecialchars($profile['publications_summary'])) ?>
                    <?php else: ?>
                        <span style="color: var(--text-secondary);">No summary available</span>
                    <?php endif; ?>
                </span>
            </div>
        </div>
    </div>

    <div class="container">
        <h2>Career History</h2>

        <?php if (empty($careers)): ?>
            <p style="color: var(--text-secondary);">No career history added yet.</p>
        <?php else: ?>
            <?php foreach ($careers as $index => $career): ?>
                <article class="list-item" style="<?= $index > 0 ? 'margin-top: 20px;' : '' ?>">
                    <h3><?= htmlspecialchars($career['job_title']) ?></h3>
                    <p><strong style="color: var(--accent);"><?= htmlspecialchars($career['company_name']) ?></strong></p>
                    <p style="color: var(--text-secondary); font-size: 14px;">
                        <?= htmlspecialchars($career['start_date']) ?>
                        —
                        <?= $career['end_date'] ? htmlspecialchars($career['end_date']) : 'Present' ?>
                    </p>

                    <?php if (!empty($career['description'])): ?>
                        <p style="margin-top: 12px;">
                            <?= nl2br(htmlspecialchars($career['description'])) ?>
                        </p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<footer>
    <p>&copy; <?php echo date('Y'); ?> Alumni Management System. All rights reserved.</p>
</footer>

</body>
</html>