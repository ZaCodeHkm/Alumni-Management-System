<?php
session_start();
require_once 'auth.php';
require_once __DIR__ . '/../database/db.php';

require_login();

$userId = $_SESSION['user_id'];

// Get all notifications for current user
$stmt = $pdo->prepare("
    SELECT 
        n.notification_id,
        n.title,
        n.body,
        n.created_at,
        u.name as sender_name,
        u.role as sender_role
    FROM notification n
    LEFT JOIN user u ON n.sent_by = u.user_id
    WHERE n.receive_by = ?
    ORDER BY n.created_at DESC
    LIMIT 50
");
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Notifications</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .notification-card {
            background: white;
            border-left: 4px solid #4f46e5;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .notification-card.system {
            border-left-color: #10b981;
        }
        
        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 0.75rem;
        }
        
        .notification-title {
            font-weight: 600;
            font-size: 1.1rem;
            color: #1f2937;
        }
        
        .notification-date {
            color: #6b7280;
            font-size: 0.875rem;
            white-space: nowrap;
            margin-left: 1rem;
        }
        
        .notification-body {
            color: #4b5563;
            line-height: 1.6;
            margin-bottom: 0.75rem;
        }
        
        .notification-sender {
            color: #6b7280;
            font-size: 0.875rem;
        }
        
        .no-notifications {
            text-align: center;
            padding: 4rem 1rem;
            color: #6b7280;
        }
        
        .no-notifications-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <main>
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div>
                    <h1>My Notifications</h1>
                    <p style="color: var(--text-secondary); margin: 0;">
                        Stay updated with announcements and activity updates
                    </p>
                </div>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="notification_admin.php" class="btn" style="white-space: nowrap;">
                        + Create Notification
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="container">
            <?php if (empty($notifications)): ?>
                <div class="no-notifications">
                    <div class="no-notifications-icon">📭</div>
                    <h2>No notifications yet</h2>
                    <p>You'll see updates about events, mentorship, and announcements here</p>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $notif): ?>
                    <?php 
                        $senderType = 'system';
                        $senderDisplay = 'System';
                        
                        if ($notif['sender_name']) {
                            if ($notif['sender_role'] === 'admin') {
                                $senderDisplay = 'Admin';
                            } else {
                                $senderDisplay = htmlspecialchars($notif['sender_name']);
                            }
                        }
                    ?>
                    
                    <div class="notification-card <?= $senderType ?>">
                        <div class="notification-header">
                            <div class="notification-title">
                                <?= htmlspecialchars($notif['title']) ?>
                            </div>
                            <div class="notification-date">
                                <?= date('M d, Y g:i A', strtotime($notif['created_at'])) ?>
                            </div>
                        </div>
                        
                        <div class="notification-body">
                            <?= nl2br(htmlspecialchars($notif['body'])) ?>
                        </div>
                        
                        <div class="notification-sender">
                            From: <?= $senderDisplay ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 Alumni Engagement System</p>
    </footer>
</body>
</html>