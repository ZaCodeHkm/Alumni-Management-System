<?php
session_start();
require_once 'auth.php';
require_once __DIR__ . '/../database/db.php';

// Only alumni can respond to mentorship requests
require_role('alumni');

$mentorshipId = $_GET['id'] ?? 0;
$action = $_GET['action'] ?? '';

if (!in_array($action, ['approve', 'reject'])) {
    header("Location: mentorship.php");
    exit;
}

// Verify this request is for the current user (mentor)
$stmt = $pdo->prepare("
    SELECT mr.*, u.name as mentee_name 
    FROM mentorship_request mr
    JOIN user u ON mr.mentee_id = u.user_id
    WHERE mr.mentorship_id = ? AND mr.mentor_id = ?
");
$stmt->execute([$mentorshipId, $_SESSION['user_id']]);
$request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    $_SESSION['error'] = "Invalid mentorship request.";
    header("Location: mentorship.php");
    exit;
}

if ($request['status'] !== 'pending') {
    $_SESSION['error'] = "This request has already been processed.";
    header("Location: mentorship.php");
    exit;
}

// Update request status
$newStatus = $action === 'approve' ? 'approved' : 'rejected';

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Update mentorship request status
    $stmt = $pdo->prepare("
        UPDATE mentorship_request 
        SET status = ? 
        WHERE mentorship_id = ?
    ");
    $stmt->execute([$newStatus, $mentorshipId]);
    
    // Send notification to the mentee
    $mentorName = $_SESSION['name'] ?? 'A mentor';
    
    if ($newStatus === 'approved') {
        $notificationTitle = "Mentorship Request Approved";
        $notificationBody = "$mentorName has approved your mentorship request. You can now connect with them!";
    } else {
        $notificationTitle = "Mentorship Request Update";
        $notificationBody = "$mentorName has declined your mentorship request at this time.";
    }
    
    // Insert notification for the mentee
    $stmt = $pdo->prepare("
        INSERT INTO notification (receive_by, title, body, sent_by, role_target, created_at)
        VALUES (?, ?, ?, ?, 'student', NOW())
    ");
    $stmt->execute([
        $request['mentee_id'],  // Send to the student who requested
        $notificationTitle,
        $notificationBody,
        $_SESSION['user_id']    // Sent by the alumni (mentor)
    ]);
    
    // Commit transaction
    $pdo->commit();
    
    $_SESSION['success'] = "Mentorship request " . $newStatus . " successfully!";
    
} catch (PDOException $e) {
    // Rollback on error
    $pdo->rollBack();
    $_SESSION['error'] = "Error updating request: " . $e->getMessage();
}

header("Location: mentorship.php");
exit;
?>