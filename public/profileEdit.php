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
$error = '';
$success = '';

/* ================= SAVE PROFILE ================= */
if (isset($_POST['save_profile'])) {
    $bio = trim($_POST['bio']);
    $education = trim($_POST['education']);
    $contact = trim($_POST['contact_info']);
    $pubSummary = trim($_POST['publications_summary']);

    $stmt = $pdo->prepare("
        INSERT INTO profile (user_id, bio, education, contact_info, publications_summary)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            bio = VALUES(bio),
            education = VALUES(education),
            contact_info = VALUES(contact_info),
            publications_summary = VALUES(publications_summary)
    ");
    $stmt->execute([$userId, $bio, $education, $contact, $pubSummary]);
    $success = "Profile updated.";
}

/* ================= ADD CAREER ================= */
if (isset($_POST['add_career'])) {
    $stmt = $pdo->prepare("
        INSERT INTO career_history
        (user_id, job_title, company_name, start_date, end_date, description)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $userId,
        $_POST['job_title'],
        $_POST['company_name'],
        $_POST['start_date'],
        $_POST['end_date'] ?: null,
        $_POST['description']
    ]);
    $success = "Career entry added.";
}

/* ================= DELETE CAREER ================= */
if (isset($_GET['delete_career'])) {
    $stmt = $pdo->prepare("
        DELETE FROM career_history
        WHERE career_id = ? AND user_id = ?
    ");
    $stmt->execute([$_GET['delete_career'], $userId]);
    $success = "Career entry deleted.";
}

/* ================= LOAD DATA ================= */
$stmt = $pdo->prepare("SELECT * FROM profile WHERE user_id = ?");
$stmt->execute([$userId]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
    'bio'=>'','education'=>'','contact_info'=>'','publications_summary'=>''
];

$stmt = $pdo->prepare("
    SELECT * FROM career_history
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
    <title>Edit Profile</title>
</head>
<body>

<h2>Edit Profile</h2>

<?php if ($success): ?>
<p style="color:green"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<!-- ================= PROFILE ================= -->
<h3>Profile Information</h3>
<form method="post">

    <label>Bio</label><br>
    <textarea name="bio" rows="4"><?= htmlspecialchars($profile['bio']) ?></textarea><br><br>

    <label>Education</label><br>
    <input type="text" name="education"
        value="<?= htmlspecialchars($profile['education']) ?>"><br><br>

    <label>Contact Information</label><br>
    <input type="text" name="contact_info"
        value="<?= htmlspecialchars($profile['contact_info']) ?>"><br><br>

    <label>Publications Summary</label><br>
    <textarea name="publications_summary" rows="3"><?= htmlspecialchars($profile['publications_summary']) ?></textarea><br><br>

    <button name="save_profile">Save Profile</button>
</form>

<hr>

<!-- ================= CAREER LIST ================= -->
<h3>Career History</h3>

<?php if (!$careers): ?>
<p>No career history added.</p>
<?php else: ?>
<table border="1" cellpadding="6">
<tr>
    <th>Job</th>
    <th>Company</th>
    <th>Duration</th>
    <th>Description</th>
    <th>Action</th>
</tr>
<?php foreach ($careers as $c): ?>
<tr>
    <td><?= htmlspecialchars($c['job_title']) ?></td>
    <td><?= htmlspecialchars($c['company_name']) ?></td>
    <td><?= $c['start_date'] ?> – <?= $c['end_date'] ?: 'Present' ?></td>
    <td><?= nl2br(htmlspecialchars($c['description'])) ?></td>
    <td>
        <a href="?delete_career=<?= $c['career_id'] ?>"
           onclick="return confirm('Delete this entry?')">
           Delete
        </a>
    </td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

<hr>

<!-- ================= ADD CAREER ================= -->
<h3>Add Career Entry</h3>

<form method="post">
    <label>Job Title</label><br>
    <input type="text" name="job_title" required><br><br>

    <label>Company</label><br>
    <input type="text" name="company_name" required><br><br>

    <label>Start Date</label><br>
    <input type="date" name="start_date" required><br><br>

    <label>End Date</label><br>
    <input type="date" name="end_date"><br><br>

    <label>Description</label><br>
    <textarea name="description" rows="3"></textarea><br><br>

    <button name="add_career">Add Career</button>
</form>

<br>
<a href="profile.php">Back to Profile</a>

</body>
</html>
