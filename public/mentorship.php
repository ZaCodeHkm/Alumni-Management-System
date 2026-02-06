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
    <link rel="stylesheet" href="styles.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentorship Dashboard</title>   
</head>
<body>
    <main>
        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="container">
                <div class="alert alert-success">✓ <?= htmlspecialchars($_SESSION['success']) ?></div>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="container">
                <div class="alert alert-error">✕ <?= htmlspecialchars($_SESSION['error']) ?></div>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="container">
            <h1>Mentorship Dashboard</h1>
            <p>Connect with experienced alumni mentors.</p>
        </div>

        <!-- Search Section -->
        <div class="container">
            <div class="search-section">
                <h2>Find a Mentor</h2>
                <form method="GET" action="mentorship.php" class="mentorship-search-form">
                    <div class="search-bar">
                        <input type="text" name="search" placeholder="Search by name..." value="<?= htmlspecialchars($searchQuery) ?>">
                        <button type="submit" class="btn">Search</button>
                        <?php if ($searchQuery): ?>
                            <a href="mentorship.php" class="btn btn-secondary">Clear</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Available Mentors Grid -->
        <div class="container">
            <h2>Available Mentors (<?= count($mentors) ?>)</h2>
            <?php if (empty($mentors)): ?>
                <div class="empty-state">
                    <p>No mentors found matching your search.</p>
                    <a href="mentorship.php" class="btn">View All Mentors</a>
                </div>
            <?php else: ?>
                <div class="alumni-grid">
                    <?php foreach ($mentors as $m): ?>
                        <div class="alumni-card card" onclick="window.location.href='alumni_profile.php?id=<?= $m['user_id'] ?>'">
                            <div class="alumni-avatar"><?= strtoupper(substr($m['name'], 0, 1)) ?></div>
                            <div class="alumni-info">
                                <h3 class="alumni-name"><?= htmlspecialchars($m['name']) ?></h3>
                                <p class="alumni-email"><?= htmlspecialchars($m['email']) ?></p>
                            </div>
                            <a href="alumni_profile.php?id=<?= $m['user_id'] ?>" class="view-profile-btn btn" onclick="event.stopPropagation()">View Profile</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Your Mentorship Requests (Sent) -->
        <div class="container">
            <h2>Your Mentorship Requests</h2>
            <p class="section-description">Requests you have sent to alumni mentors.</p>
            
            <?php if (empty($sentRequests)): ?>
                <div class="empty-state">
                    <p>You haven't sent any mentorship requests yet.</p>
                </div>
            <?php else: ?>
                <div class="mentorship-requests-list">
                    <?php foreach ($sentRequests as $r): ?>
                        <div class="request-card">
                            <div class="request-header">
                                <div>
                                    <h3 class="request-mentor-name">
                                        <a href="alumni_profile.php?id=<?= $r['mentor_id'] ?>"><?= htmlspecialchars($r['mentor_name']) ?></a>
                                    </h3>
                                    <p class="request-date"><?= date('M d, Y', strtotime($r['created_at'])) ?></p>
                                </div>
                                <span class="request-status status-<?= $r['status'] ?>">
                                    <?= ucfirst($r['status']) ?>
                                </span>
                            </div>
                            <div class="request-message">
                                <strong>Message:</strong> <?= htmlspecialchars($r['message']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Incoming Mentorship Requests (Alumni Only) -->
        <?php if ($userRole === 'alumni'): ?>
        <div class="container">
            <h2>Incoming Mentorship Requests</h2>
            <p class="section-description">Requests from students seeking your mentorship.</p>
            
            <?php if (empty($incomingRequests)): ?>
                <div class="empty-state">
                    <p>No incoming mentorship requests.</p>
                </div>
            <?php else: ?>
                <div class="mentorship-requests-list">
                    <?php foreach ($incomingRequests as $r): ?>
                        <div class="request-card">
                            <div class="request-header">
                                <div>
                                    <h3 class="request-mentor-name"><?= htmlspecialchars($r['mentee_name']) ?></h3>
                                    <p class="request-date">
                                        <span class="role-badge role-<?= strtolower($r['mentee_role']) ?>"><?= ucfirst($r['mentee_role']) ?></span>
                                        · <?= date('M d, Y', strtotime($r['created_at'])) ?>
                                    </p>
                                </div>
                                <span class="request-status status-<?= $r['status'] ?>">
                                    <?= ucfirst($r['status']) ?>
                                </span>
                            </div>
                            <div class="request-message">
                                <strong>Message:</strong> <?= htmlspecialchars($r['message']) ?>
                            </div>
                            
                            <?php if ($r['status'] === 'pending'): ?>
                                <div class="request-actions">
                                    <a href="mentorship_respond.php?id=<?= $r['mentorship_id'] ?>&action=approve" class="btn btn-success">
                                        ✓ Approve
                                    </a>
                                    <a href="mentorship_respond.php?id=<?= $r['mentorship_id'] ?>&action=reject" class="btn btn-danger">
                                        ✕ Reject
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2026 Alumni Engagement System</p>
    </footer>
</body>
</html>