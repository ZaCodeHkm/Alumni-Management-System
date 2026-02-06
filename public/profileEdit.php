<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../database/db.php';
require_once 'auth.php'; // Ensure require_login() exists here

require_login();

$userId = $_SESSION['user_id'];
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
    $success = "Profile updated successfully!";
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
    $stmt = $pdo->prepare("DELETE FROM career_history WHERE career_id = ? AND user_id = ?");
    $stmt->execute([$_GET['delete_career'], $userId]);
    header("Location: profileEdit.php?msg=deleted");
    exit;
}

/* ================= LOAD DATA ================= */
$stmt = $pdo->prepare("SELECT * FROM profile WHERE user_id = ?");
$stmt->execute([$userId]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
    'bio'=>'','education'=>'','contact_info'=>'','publications_summary'=>''
];

$stmt = $pdo->prepare("SELECT * FROM career_history WHERE user_id = ? ORDER BY start_date DESC");
$stmt->execute([$userId]);
$careers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch role for theme color
$stmt = $pdo->prepare("SELECT role FROM user WHERE user_id = ?");
$stmt->execute([$userId]);
$userBase = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles.css">
    <title>Edit My Profile</title>
</head>
<body class="theme-<?= htmlspecialchars($userBase['role'] ?? 'student') ?>">

<?php include 'navbar.php'; ?>

<main class="container">
    <div class="profile-header">
        <div class="profile-info">
            <h1>Edit Your Profile</h1>
            <p class="text-secondary">Keep your information up to date for the alumni network.</p>
        </div>
        <div style="margin-left: auto;">
            <a href="profile.php" class="btn btn-secondary">View Profile</a>
        </div>
    </div>

    <?php if ($success || isset($_GET['msg'])): ?>
        <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <?= $success ?: "Entry removed successfully." ?>
        </div>
    <?php endif; ?>

    <div class="dashboard-grid">
        <div class="card">
            <h3>General Information</h3>
            <form method="post" class="auth-form" style="max-width: 100%; box-shadow: none; padding: 0;">
                <label>Professional Bio</label>
                <textarea name="bio" rows="5" class="input-field"><?= htmlspecialchars($profile['bio']) ?></textarea>

                <label>Education (Highest Degree)</label>
                <input type="text" name="education" class="input-field" value="<?= htmlspecialchars($profile['education']) ?>">

                <label>Contact Email/Phone</label>
                <input type="text" name="contact_info" class="input-field" value="<?= htmlspecialchars($profile['contact_info']) ?>">

                <label>Publications / Projects Summary</label>
                <textarea name="publications_summary" rows="4" class="input-field"><?= htmlspecialchars($profile['publications_summary']) ?></textarea>

                <button name="save_profile" class="btn mt-2">Update Profile Info</button>
            </form>
        </div>

        <div class="card">
            <h3>Add New Experience</h3>
            <form method="post" class="auth-form" style="max-width: 100%; box-shadow: none; padding: 0;">
                <label>Job Title</label>
                <input type="text" name="job_title" class="input-field" required placeholder="e.g. Software Engineer">

                <label>Company Name</label>
                <input type="text" name="company_name" class="input-field" required placeholder="e.g. Google">

                <div style="display: flex; gap: 10px;">
                    <div style="flex: 1;">
                        <label>Start Date</label>
                        <input type="date" name="start_date" class="input-field" required>
                    </div>
                    <div style="flex: 1;">
                        <label>End Date</label>
                        <input type="date" name="end_date" class="input-field">
                        <small class="text-secondary">Leave blank if current</small>
                    </div>
                </div>

                <label>Job Description</label>
                <textarea name="description" rows="3" class="input-field"></textarea>

                <button name="add_career" class="btn mt-2">Add Career Entry</button>
            </form>
        </div>
    </div>

    <div class="card mt-3">
        <h3>Current Career History</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Role & Company</th>
                        <th>Dates</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$careers): ?>
                        <tr><td colspan="3" class="text-secondary">No history found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($careers as $c): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($c['job_title']) ?></strong> at <?= htmlspecialchars($c['company_name']) ?>
                            </td>
                            <td><?= $c['start_date'] ?> – <?= $c['end_date'] ?: 'Present' ?></td>
                            <td>
                                <a href="?delete_career=<?= $c['career_id'] ?>" 
                                   class="text-danger" 
                                   onclick="return confirm('Delete this entry?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<footer class="mt-3">
    <p>&copy; 2026 Alumni Portal</p>
</footer>

</body>
</html>