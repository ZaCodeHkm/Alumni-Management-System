<?php
session_start();
header('Content-Type: application/json');

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display, just log

// Log errors to a file instead
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/chat_errors.log');

try {
    // Use your existing database connection
    require_once __DIR__ . '/../database/db.php';
    
    // Get current user ID
    $currentUserId = $_SESSION['user_id'] ?? null;
    
    if (!$currentUserId) {
        echo json_encode(['success' => false, 'error' => 'Not logged in']);
        exit;
    }
    
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    switch($action) {
        case 'get_conversations':
            $sql = "SELECT DISTINCT
                        c.conversation_id,
                        c.conversation_name,
                        c.created_at,
                        (SELECT message_text FROM message
                         WHERE conversation_id = c.conversation_id
                         ORDER BY sent_at DESC LIMIT 1) as last_message
                    FROM conversation c
                    INNER JOIN conversation_participant cp ON c.conversation_id = cp.conversation_id
                    WHERE cp.user_id = ?
                    ORDER BY c.created_at DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$currentUserId]);
            $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'conversations' => $conversations]);
            break;
        
        case 'get_messages':
            $conversationId = $_GET['conversation_id'] ?? null;
            if (!$conversationId) {
                echo json_encode(['success' => false, 'error' => 'Missing conversation_id']);
                exit;
            }
            
            // Verify user is participant
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM conversation_participant WHERE conversation_id = ? AND user_id = ?");
            $checkStmt->execute([$conversationId, $currentUserId]);
            if ($checkStmt->fetchColumn() == 0) {
                echo json_encode(['success' => false, 'error' => 'Not authorized']);
                exit;
            }
            
            $sql = "SELECT
                        m.message_id,
                        m.message_text,
                        m.sent_at,
                        u.name as sender_name,
                        CASE WHEN m.sender_id = ? THEN 1 ELSE 0 END as is_mine
                    FROM message m
                    INNER JOIN user u ON m.sender_id = u.user_id
                    WHERE m.conversation_id = ?
                    ORDER BY m.sent_at ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$currentUserId, $conversationId]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Convert is_mine to boolean
            foreach ($messages as &$msg) {
                $msg['is_mine'] = (bool)$msg['is_mine'];
            }
            
            echo json_encode(['success' => true, 'messages' => $messages]);
            break;
        
        case 'send_message':
            $conversationId = $_POST['conversation_id'] ?? null;
            $message = $_POST['message'] ?? null;
            
            if (!$conversationId || !$message) {
                echo json_encode(['success' => false, 'error' => 'Missing parameters']);
                exit;
            }
            
            // Verify user is participant
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM conversation_participant WHERE conversation_id = ? AND user_id = ?");
            $stmt->execute([$conversationId, $currentUserId]);
            
            if ($stmt->fetchColumn() == 0) {
                echo json_encode(['success' => false, 'error' => 'Not a participant']);
                exit;
            }
            
            // Insert message
            $sql = "INSERT INTO message (conversation_id, sender_id, message_text, sent_at)
                    VALUES (?, ?, ?, NOW())";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$conversationId, $currentUserId, $message]);
            
            echo json_encode(['success' => true, 'message_id' => $pdo->lastInsertId()]);
            break;
        
        case 'search_users':
            $query = $_GET['query'] ?? '';
            
            // Search for active users (excluding current user)
            // Removed is_verified check since column doesn't exist
            $sql = "SELECT user_id, name, email
                    FROM user
                    WHERE is_active = 1
                    AND user_id != ?
                    AND (name LIKE ? OR email LIKE ?)
                    ORDER BY name
                    LIMIT 20";
            
            $searchTerm = "%$query%";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$currentUserId, $searchTerm, $searchTerm]);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'users' => $users]);
            break;
        
        case 'create_conversation':
            $userId = $_POST['user_id'] ?? null;
            $conversationName = $_POST['conversation_name'] ?? null;
            
            if (!$userId || !$conversationName) {
                echo json_encode(['success' => false, 'error' => 'Missing parameters']);
                exit;
            }
            
            // Check if conversation already exists between these two users
            $sql = "SELECT c.conversation_id
                    FROM conversation c
                    INNER JOIN conversation_participant cp1 ON c.conversation_id = cp1.conversation_id
                    INNER JOIN conversation_participant cp2 ON c.conversation_id = cp2.conversation_id
                    WHERE cp1.user_id = ? AND cp2.user_id = ?
                    AND (SELECT COUNT(*) FROM conversation_participant WHERE conversation_id = c.conversation_id) = 2
                    LIMIT 1";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$currentUserId, $userId]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                echo json_encode(['success' => true, 'conversation_id' => $existing['conversation_id']]);
                exit;
            }
            
            // Create new conversation
            $pdo->beginTransaction();
            
            try {
                // Insert conversation
                $stmt = $pdo->prepare("INSERT INTO conversation (conversation_name, created_at) VALUES (?, NOW())");
                $stmt->execute([$conversationName]);
                $conversationId = $pdo->lastInsertId();
                
                // Add both participants
                $stmt = $pdo->prepare("INSERT INTO conversation_participant (conversation_id, user_id, joined_at) VALUES (?, ?, NOW())");
                $stmt->execute([$conversationId, $currentUserId]);
                $stmt->execute([$conversationId, $userId]);
                
                $pdo->commit();
                echo json_encode(['success' => true, 'conversation_id' => $conversationId]);
            } catch (Exception $e) {
                $pdo->rollBack();
                error_log("Create conversation error: " . $e->getMessage());
                echo json_encode(['success' => false, 'error' => 'Failed to create conversation']);
            }
            break;
        
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    error_log("Chat API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Server error occurred']);
}
?>