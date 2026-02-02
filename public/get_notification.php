<?php
session_start();
require_once 'auth.php';
require_once __DIR__ . '/../database/db.php';

// Only admins can view this
require_role('admin');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get recent notifications (grouped by title, body, role_target since same notification goes to multiple users)
    // We'll get distinct notifications sent by admin
    $stmt = $pdo->prepare("
        SELECT 
            n.title,
            n.role_target,
            n.created_at,
            COUNT(DISTINCT n.receive_by) as recipient_count
        FROM notification n
        WHERE n.sent_by IN (SELECT user_id FROM user WHERE role = 'admin')
        GROUP BY n.title, n.body, n.role_target, DATE(n.created_at)
        ORDER BY MAX(n.created_at) DESC
        LIMIT 10
    ");
    
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the data for frontend
    $formattedNotifications = array_map(function($notif) {
        // Map role_target back to friendly names
        $audienceMap = [
            'All Users' => 'All Users',
            'student' => 'Students',
            'alumni' => 'Alumni',
            'eventmanager' => 'Event Managers'
        ];
        
        return [
            'title' => $notif['title'],
            'audience' => $audienceMap[$notif['role_target']] ?? $notif['role_target'],
            'date' => date('M d, Y', strtotime($notif['created_at'])),
            'recipient_count' => $notif['recipient_count']
        ];
    }, $notifications);
    
    echo json_encode([
        'success' => true,
        'notifications' => $formattedNotifications
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>