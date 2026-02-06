<?php
session_start();
require_once 'auth.php';
require_once __DIR__ . '/../database/db.php';

require_login();

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];

// Get search parameter
$searchQuery = $_GET['search'] ?? '';

// Build query for fetching mentors - ONLY USING: user_id, name, email, role, is_active
$sql = "SELECT user_id, name, email FROM user WHERE role = 'alumni' AND is_active = TRUE";
$params = [];

if (!empty($searchQuery)) {
    $sql .= " AND name LIKE ?";
    $params[] = "%$searchQuery%";
}
$sql .= " ORDER BY name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$mentors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user's sent requests
$stmt = $pdo->prepare("SELECT mr.*, u.name as mentor_name FROM mentorship_request mr JOIN user u ON mr.mentor_id = u.user_id WHERE mr.mentee_id = ? ORDER BY mr.created_at DESC");
$stmt->execute([$userId]);
$sentRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch incoming requests (alumni only)
$incomingRequests = [];
if ($userRole === 'alumni') {
    $stmt = $pdo->prepare("SELECT mr.*, u.name as mentee_name, u.role as mentee_role FROM mentorship_request mr JOIN user u ON mr.mentee_id = u.user_id WHERE mr.mentor_id = ? ORDER BY mr.created_at DESC");
    $stmt->execute([$userId]);
    $incomingRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

include 'navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentorship Dashboard</title>
    <link rel="stylesheet" href="styles.css">
   
</head>
<body>
    <main>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="container"><div class="alert alert-success">✓ <?= htmlspecialchars($_SESSION['success']) ?></div></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="container"><div class="alert alert-error">✕ <?= htmlspecialchars($_SESSION['error']) ?></div></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="container">
            <h1>Mentorship Dashboard</h1>
            <p>Connect with experienced alumni mentors.</p>
        </div>

        <!-- Search -->
        <div class="container">
            <div class="search-section">
                <h2>Find a Mentor</h2>
                <form method="GET" action="mentorship.php">
                    <div class="search-bar">
                        <input type="text" name="search" placeholder="Search by name..." value="<?= htmlspecialchars($searchQuery) ?>">
                        <button type="submit" class="btn">Search</button>
                        <?php if ($searchQuery): ?>
                            <a href="mentorship.php" class="btn btn-clear">Clear</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Alumni Grid -->
        <div class="container">
            <h2>Available Mentors (<?= count($mentors) ?>)</h2>
            <?php if (empty($mentors)): ?>
                <div class="no-results">
                    <p>No mentors found.</p>
                    <a href="mentorship.php" class="btn">View All Mentors</a>
                </div>
            <?php else: ?>
                <div class="alumni-grid">
                    <?php foreach ($mentors as $m): ?>
                        <div class="alumni-card" onclick="window.location.href='alumni_profile.php?id=<?= $m['user_id'] ?>'">
                            <div class="alumni-avatar"><?= strtoupper(substr($m['name'], 0, 1)) ?></div>
                            <div class="alumni-name"><?= htmlspecialchars($m['name']) ?></div>
                            <div class="alumni-email"><?= htmlspecialchars($m['email']) ?></div>
                            <a href="alumni_profile.php?id=<?= $m['user_id'] ?>" class="view-profile-btn" onclick="event.stopPropagation()">View Profile</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Your Requests -->
        <div class="container">
            <h2>Your Mentorship Requests</h2>
            <p style="color: var(--text-secondary);">Requests you have sent to alumni mentors.</p>
            <?php if (empty($sentRequests)): ?>
                <p>You haven't sent any mentorship requests yet.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr><th>Mentor Name</th><th>Message</th><th>Status</th><th>Date</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sentRequests as $r): ?>
                        <tr>
                            <td><a href="alumni_profile.php?id=<?= $r['mentor_id'] ?>" style="color: #4f46e5;"><?= htmlspecialchars($r['mentor_name']) ?></a></td>
                            <td><?= htmlspecialchars(substr($r['message'], 0, 50)) ?><?= strlen($r['message']) > 50 ? '...' : '' ?></td>
                            <td style="color: <?= $r['status'] === 'approved' ? 'green' : ($r['status'] === 'rejected' ? 'red' : 'orange') ?>;"><?= ucfirst($r['status']) ?></td>
                            <td><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Incoming Requests (Alumni Only) -->
        <?php if ($userRole === 'alumni'): ?>
        <div class="container">
            <h2>Incoming Mentorship Requests</h2>
            <p style="color: var(--text-secondary);">Requests from students seeking your mentorship.</p>
            <?php if (empty($incomingRequests)): ?>
                <p>No incoming mentorship requests.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr><th>Requester</th><th>Role</th><th>Message</th><th>Status</th><th>Date</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($incomingRequests as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['mentee_name']) ?></td>
                            <td><?= ucfirst($r['mentee_role']) ?></td>
                            <td><?= htmlspecialchars(substr($r['message'], 0, 50)) ?><?= strlen($r['message']) > 50 ? '...' : '' ?></td>
                            <td style="color: <?= $r['status'] === 'approved' ? 'green' : ($r['status'] === 'rejected' ? 'red' : 'orange') ?>;"><?= ucfirst($r['status']) ?></td>
                            <td><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                            <td>
                                <?php if ($r['status'] === 'pending'): ?>
                                    <a href="mentorship_respond.php?id=<?= $r['mentorship_id'] ?>&action=approve" class="btn">Approve</a>
                                    <a href="mentorship_respond.php?id=<?= $r['mentorship_id'] ?>&action=reject" class="btn btn-danger">Reject</a>
                                <?php else: ?>
                                    <span style="color: var(--text-secondary);">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2026 Alumni Engagement System</p>
    </footer>
</body>
</html>