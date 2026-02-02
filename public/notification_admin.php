<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'auth.php';
require_once __DIR__ . '/../database/db.php';

// Only admins can access
require_role('admin');

$userId = $_SESSION['user_id'];

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    // Clear any output that might have been sent
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');
    
    if ($_POST['ajax'] === 'send') {
        // Send notification
        $title = trim($_POST['title'] ?? '');
        $body = trim($_POST['body'] ?? '');
        $roleTarget = $_POST['role_target'] ?? 'All Users';
        
        if (empty($title) || empty($body)) {
            echo json_encode(['success' => false, 'message' => 'Title and message are required']);
            exit;
        }
        
        // Map audience to role
        $roleMapping = [
            'All Users' => 'All Users',
            'Students' => 'student',
            'Alumni' => 'alumni',
            'Event Managers' => 'eventmanager'
        ];
        
        $mappedRole = $roleMapping[$roleTarget] ?? 'All Users';
        
        try {
            // Get users
            if ($mappedRole === 'All Users') {
                $stmt = $pdo->prepare("SELECT user_id FROM user WHERE is_active = TRUE");
                $stmt->execute();
            } else {
                $stmt = $pdo->prepare("SELECT user_id FROM user WHERE role = ? AND is_active = TRUE");
                $stmt->execute([$mappedRole]);
            }
            
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($users)) {
                echo json_encode(['success' => false, 'message' => 'No users found']);
                exit;
            }
            
            // Insert notifications
            $insertStmt = $pdo->prepare("
                INSERT INTO notification (receive_by, title, body, sent_by, role_target, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            $count = 0;
            $errors = [];
            foreach ($users as $user) {
                try {
                    $insertStmt->execute([$user['user_id'], $title, $body, $userId, $mappedRole]);
                    $count++;
                } catch (PDOException $e) {
                    $errors[] = "User {$user['user_id']}: " . $e->getMessage();
                }
            }
            
            if ($count > 0) {
                $message = "Notification sent to $count user(s)";
                if (!empty($errors)) {
                    $message .= " (with some errors)";
                }
                echo json_encode(['success' => true, 'message' => $message, 'count' => $count]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to send notifications: ' . implode('; ', $errors)]);
            }
            exit;
            
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            exit;
        }
    }
    
    if ($_POST['ajax'] === 'get') {
        // Get recent notifications
        try {
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
            
            echo json_encode(['success' => true, 'notifications' => $notifications]);
            exit;
            
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            exit;
        }
    }
    
    // Unknown ajax action
    echo json_encode(['success' => false, 'message' => 'Unknown action']);
    exit;
}

// Only include navbar if not AJAX request
include 'navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Admin</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }
        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
    </style>
</head>
<body>
    <main>
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div>
                    <h1>Create Notification</h1>
                    <p class="text-secondary" style="margin: 0;">Broadcast announcements to specific user groups</p>
                </div>
                <a href="notification.php" class="btn btn-secondary">
                    ← Back to Notifications
                </a>
            </div>
        </div>

        <div class="container">
            <form id="notificationForm" class="form">
                <h2>Notification Details</h2>

                <label>Title</label>
                <input type="text" name="notification_title" placeholder="Enter notification title..." required>

                <label>Message</label>
                <textarea name="notification_message" placeholder="Write the notification message..." required></textarea>

                <label>Audience</label>
                <select name="notification_audience" required>
                    <option>All Users</option>
                    <option>Students</option>
                    <option>Alumni</option>
                    <option>Event Managers</option>
                </select>

                <div class="button-container">
                    <button class="btn btn-secondary" type="reset">Clear</button>
                    <button class="btn" type="submit">Send Notification</button>
                </div>
            </form>
        </div>

        <div class="container">
            <h2>Recent Notifications</h2>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Audience</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody id="recentNotificationsTable">
                    <tr>
                        <td colspan="3" style="text-align: center; color: var(--text-secondary);">
                            Loading...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 Alumni Engagement System</p>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('notificationForm');
        const tableBody = document.getElementById('recentNotificationsTable');

        // Load recent notifications on page load
        loadNotifications();

        // Handle form submission
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(form);
            const title = formData.get('notification_title');
            const body = formData.get('notification_message');
            const roleTarget = formData.get('notification_audience');

            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';

            try {
                console.log('Sending notification with:', {title, body, roleTarget});
                
                // Use current page URL instead of hardcoded filename
                const response = await fetch(window.location.pathname, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: new URLSearchParams({
                        ajax: 'send',
                        title: title,
                        body: body,
                        role_target: roleTarget
                    })
                });

                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                const responseText = await response.text();
                console.log('Raw response:', responseText);
                
                let data;
                try {
                    data = JSON.parse(responseText);
                    console.log('Parsed data:', data);
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    console.error('Response was:', responseText);
                    showAlert('error', '✕ Server returned invalid response. Check console.');
                    return;
                }

                if (data.success) {
                    showAlert('success', '✓ ' + data.message);
                    form.reset();
                    loadNotifications();
                } else {
                    showAlert('error', '✕ ' + data.message);
                }
            } catch (error) {
                console.error('Fetch error:', error);
                showAlert('error', '✕ Network error: ' + error.message);
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send Notification';
            }
        });

        // Show alert message
        function showAlert(type, message) {
            // Remove existing alerts
            const existingAlerts = document.querySelectorAll('.alert');
            existingAlerts.forEach(alert => alert.remove());

            // Create alert
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-' + type;
            alertDiv.textContent = message;
            alertDiv.style.marginBottom = '1rem';

            // Insert before form
            const container = form.closest('.container');
            container.insertBefore(alertDiv, form);

            // Auto-remove after 5 seconds
            setTimeout(() => alertDiv.remove(), 5000);
        }

        // Load recent notifications
        async function loadNotifications() {
            try {
                // Use current page URL instead of hardcoded filename
                const response = await fetch(window.location.pathname, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: new URLSearchParams({ajax: 'get'})
                });

                const data = await response.json();

                if (data.success && data.notifications) {
                    displayNotifications(data.notifications);
                }
            } catch (error) {
                console.error('Error loading notifications:', error);
            }
        }

        // Display notifications in table
        function displayNotifications(notifications) {
            const roleMap = {
                'All Users': 'All Users',
                'student': 'Students',
                'alumni': 'Alumni',
                'eventmanager': 'Event Managers'
            };

            if (notifications.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="3" style="text-align: center; color: var(--text-secondary);">
                            No notifications sent yet
                        </td>
                    </tr>
                `;
                return;
            }

            tableBody.innerHTML = notifications.map(n => `
                <tr>
                    <td>${escapeHtml(n.title)}</td>
                    <td>${roleMap[n.role_target] || n.role_target}</td>
                    <td>${formatDate(n.created_at)}</td>
                </tr>
            `).join('');
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'});
        }
    });
    </script>
</body>
</html>