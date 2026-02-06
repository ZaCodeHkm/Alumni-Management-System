<?php
session_start();
require_once 'auth.php';
require_once __DIR__ . '/../database/db.php';

// Require admin role
require_role(allowed_roles: 'admin');

// ==========================================
// HANDLE POST ACTIONS (Verify, Reject, Suspend, Unsuspend, Delete)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = $_POST['user_id'] ?? 0;
    
    try {
        switch ($action) {
            case 'verify':
                // Set user as active (verified)
                $stmt = $pdo->prepare("UPDATE user SET is_active = 1 WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $_SESSION['success_message'] = "User verified successfully.";
                break;
                
            case 'reject':
                // Delete the pending user completely
                $stmt = $pdo->prepare("DELETE FROM user WHERE user_id = ? AND last_login IS NULL");
                $stmt->execute([$user_id]);
                $_SESSION['success_message'] = "User rejected and removed.";
                break;
                
            case 'suspend':
                // Suspend verified user
                $stmt = $pdo->prepare("UPDATE user SET is_active = 0 WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $_SESSION['success_message'] = "User suspended.";
                break;
                
            case 'unsuspend':
                // Reactivate suspended user
                $stmt = $pdo->prepare("UPDATE user SET is_active = 1 WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $_SESSION['success_message'] = "User unsuspended.";
                break;
                
            case 'delete':
                // Hard delete with manual cascade through all related tables
                $pdo->beginTransaction();
                
                // Delete all related records first
                $stmt = $pdo->prepare("DELETE FROM Profile WHERE user_id = ?");
                $stmt->execute([$user_id]);
                
                $stmt = $pdo->prepare("DELETE FROM career_history WHERE user_id = ?");
                $stmt->execute([$user_id]);
                
                $stmt = $pdo->prepare("DELETE FROM Publication WHERE user_id = ?");
                $stmt->execute([$user_id]);
                
                $stmt = $pdo->prepare("DELETE FROM Event_Attendance WHERE user_id = ?");
                $stmt->execute([$user_id]);
                
                $stmt = $pdo->prepare("DELETE FROM Event WHERE created_by = ?");
                $stmt->execute([$user_id]);
                
                $stmt = $pdo->prepare("DELETE FROM JobPosting WHERE posted_by = ?");
                $stmt->execute([$user_id]);
                
                $stmt = $pdo->prepare("DELETE FROM MentorshipRequest WHERE mentor_id = ? OR mentee_id = ?");
                $stmt->execute([$user_id, $user_id]);
                
                $stmt = $pdo->prepare("DELETE FROM ConversationParticipant WHERE user_id = ?");
                $stmt->execute([$user_id]);
                
                $stmt = $pdo->prepare("DELETE FROM Message WHERE sender_id = ?");
                $stmt->execute([$user_id]);
                
                $stmt = $pdo->prepare("DELETE FROM Notification WHERE receive_by = ? OR sent_by = ?");
                $stmt->execute([$user_id, $user_id]);
                
                // Finally delete the user
                $stmt = $pdo->prepare("DELETE FROM user WHERE user_id = ?");
                $stmt->execute([$user_id]);
                
                $pdo->commit();
                $_SESSION['success_message'] = "User and all related data deleted permanently.";
                break;
        }
    } catch (PDOException $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }
    
    // Redirect to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// ==========================================
// FETCH PENDING USERS (is_active = 0 AND last_login IS NULL)
// These are users who registered but haven't been verified yet
// ==========================================
$stmt = $pdo->query("
    SELECT user_id, name, email, role, created_at
    FROM user
    WHERE is_active = 0 AND last_login IS NULL
    ORDER BY created_at DESC
");
$pending_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ==========================================
// FETCH ALL VERIFIED USERS (last_login IS NOT NULL OR is_active = 1)
// These are users who have been verified at some point
// ==========================================
$stmt = $pdo->query("
    SELECT user_id, name, email, role, is_active, last_login, created_at
    FROM user
    WHERE last_login IS NOT NULL OR is_active = 1
    ORDER BY created_at DESC
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'navbar.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="styles.css">
    <title>Admin – User Management</title>
</head>
<body>

<div class="admin-container">

    <!-- Display Success/Error Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="message success">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="message error">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- ==========================================
         PENDING VERIFICATION SECTION
         Users who registered but admin hasn't verified yet
         ========================================== -->
    <div class="admin-section-header">
        <h2>Users Pending Verification</h2>
        <span class="user-count"><?= count($pending_users) ?> pending</span>
    </div>

    <?php if (empty($pending_users)): ?>
        <div class="empty-state">
            <p>✓ No users pending verification at this time.</p>
        </div>
    <?php else: ?>
    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending_users as $pu): ?>
                <tr>
                    <td>
                        <div class="user-info-cell">
                            <div class="user-avatar-small">
                                <?= strtoupper(substr($pu['name'], 0, 1)) ?>
                            </div>
                            <strong><?= htmlspecialchars($pu['name']) ?></strong>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($pu['email']) ?></td>
                    <td><span class="role-badge role-<?= strtolower($pu['role']) ?>"><?= htmlspecialchars($pu['role']) ?></span></td>
                    <td><?= date('M d, Y', strtotime($pu['created_at'])) ?></td>
                    <td>
                        <div class="action-buttons">
                            <!-- Verify Button -->
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?= $pu['user_id'] ?>">
                                <input type="hidden" name="action" value="verify">
                                <button type="submit" class="btn-action btn-verify">Verify</button>
                            </form>
                            
                            <!-- Reject Button -->
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to reject this user? They will be permanently deleted.');">
                                <input type="hidden" name="user_id" value="<?= $pu['user_id'] ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="btn-action btn-reject">Reject</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- ==========================================
         ALL VERIFIED USERS SECTION
         Users who have been verified (active or suspended)
         ========================================== -->
    <div class="admin-section-header">
        <h2>All Verified Users</h2>
        <span class="user-count"><?= count($users) ?> users</span>
    </div>

    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td>
                        <div class="user-info-cell">
                            <div class="user-avatar-small">
                                <?= strtoupper(substr($u['name'], 0, 1)) ?>
                            </div>
                            <strong><?= htmlspecialchars($u['name']) ?></strong>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="role-badge role-<?= strtolower($u['role']) ?>"><?= htmlspecialchars($u['role']) ?></span></td>
                    <td><span class="status-badge status-<?= $u['is_active'] ? 'active' : 'suspended' ?>"><?= $u['is_active'] ? 'Active' : 'Suspended' ?></span></td>
                    <td><?= $u['last_login'] ? date('M d, Y', strtotime($u['last_login'])) : 'Never' ?></td>
                    <td>
                        <div class="action-buttons">
                            <?php if ($u['is_active']): ?>
                                <!-- Suspend Button (for active users) -->
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                    <input type="hidden" name="action" value="suspend">
                                    <button type="submit" class="btn-action btn-suspend">Suspend</button>
                                </form>
                            <?php else: ?>
                                <!-- Unsuspend Button (for suspended users) -->
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                    <input type="hidden" name="action" value="unsuspend">
                                    <button type="submit" class="btn-action btn-unsuspend">Unsuspend</button>
                                </form>
                            <?php endif; ?>
                            
                            <!-- Delete Button -->
                            <form method="POST" style="display:inline;" onsubmit="return confirm('⚠️ WARNING: This will permanently delete the user and ALL their data (profile, events, messages, publications, etc.). This action cannot be undone. Are you absolutely sure?');">
                                <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn-action btn-delete">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>