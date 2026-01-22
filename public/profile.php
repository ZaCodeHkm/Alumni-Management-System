<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../db.php';

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

<?php include 'navbar.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
</head>
<body>

<h2>My Profile</h2>

<h3>Bio</h3>
<p><?= nl2br(htmlspecialchars($profile['bio'])) ?: 'Not provided' ?></p>

<h3>Education</h3>
<p><?= htmlspecialchars($profile['education']) ?: 'Not provided' ?></p>

<hr>

<h3>Career History</h3>

<?php if (empty($careers)): ?>
    <p>No career history added yet.</p>
<?php else: ?>
    <ul>
        <?php foreach ($careers as $career): ?>
            <li>
                <strong><?= htmlspecialchars($career['job_title']) ?></strong>
                at <?= htmlspecialchars($career['company_name']) ?><br>

                <?= htmlspecialchars($career['start_date']) ?>
                —
                <?= $career['end_date'] ? htmlspecialchars($career['end_date']) : 'Present' ?><br>

                <?php if (!empty($career['description'])): ?>
                    <em><?= nl2br(htmlspecialchars($career['description'])) ?></em>
                <?php endif; ?>
            </li>
            <br>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<hr>

<h3>Contact Information</h3>
<p><?= htmlspecialchars($profile['contact_info']) ?: 'Not provided' ?></p>

<h3>Publications Summary</h3>
<p><?= nl2br(htmlspecialchars($profile['publications_summary'])) ?: 'No summary available' ?></p>

<br>

<a href="profileEdit.php">
    <button>Edit Profile</button>
</a>

</body>
</html>
