<?php
session_start();
require_once 'auth.php';
require_once __DIR__ . '/../database/db.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mentorId = $_POST['mentor_id'] ?? 0;
    $message = trim($_POST['message'] ?? '');
    $menteeId = $_SESSION['user_id'];
    
    // Validate message is not empty
    if (empty($message)) {
        $_SESSION['error'] = "Message is required.";
        header("Location: mentorship.php");
        exit;
    }
    
    // Validate that mentor is actually an alumni
    $stmt = $pdo->prepare("SELECT role, name FROM user WHERE user_id = ?");
    $stmt->execute([$mentorId]);
    $mentor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$mentor || $mentor['role'] !== 'alumni') {
        $_SESSION['error'] = "Invalid mentor selected.";
        header("Location: mentorship.php");
        exit;
    }
    
    // Check if request already exists
    $stmt = $pdo->prepare("
        SELECT mentorship_id 
        FROM mentorship_request 
        WHERE mentor_id = ? AND mentee_id = ? AND status = 'pending'
    ");
    $stmt->execute([$mentorId, $menteeId]);
    
    if ($stmt->fetch()) {
        $_SESSION['error'] = "You already have a pending request with this mentor.";
        header("Location: mentorship.php");
        exit;
    }
    
    // Insert mentorship request
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("
            INSERT INTO mentorship_request 
            (mentor_id, mentee_id, message, status)
            VALUES (?, ?, ?, 'pending')
        ");
        
        $stmt->execute([
            $mentorId, 
            $menteeId, 
            $message
        ]);
        
        // Send notification to the mentor (alumni)
        $menteeName = $_SESSION['name'] ?? 'A student';
        
        $notificationTitle = "New Mentorship Request";
        $notificationBody = "$menteeName has sent you a mentorship request. Please review it in your mentorship dashboard.";
        
        $stmt = $pdo->prepare("
            INSERT INTO notification (receive_by, title, body, sent_by, role_target, created_at)
            VALUES (?, ?, ?, ?, 'alumni', NOW())
        ");
        $stmt->execute([
            $mentorId,              // Send to the alumni (mentor)
            $notificationTitle,
            $notificationBody,
            $menteeId               // Sent by the student (mentee)
        ]);
        
        // Commit transaction
        $pdo->commit();
        
        $_SESSION['success'] = "Mentorship request sent successfully!";
        
    } catch (PDOException $e) {
        // Rollback on error
        $pdo->rollBack();
        $_SESSION['error'] = "Error submitting request: " . $e->getMessage();
    }
}

header("Location: mentorship.php");
exit;
?>