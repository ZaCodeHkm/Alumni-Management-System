<?php
require_once __DIR__ . '/../database/db.php';
session_start();

if (($_SESSION['role'] !== 'event_manager')) {
    die("Access denied");
}

if (!isset($_POST['event_id'], $_POST['action'])) {
    die("Invalid request");
}

$event_id = (int) $_POST['event_id'];
$action = $_POST['action'];

if (!in_array($action, ['approve', 'reject'])) {
    die("Invalid action");
}

$status = ($action === 'approve') ? 'approved' : 'rejected';

$host = "localhost";
$db   = "sofeng";
$user = "root";
$pass = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Database connection failed");
}

$sql = "UPDATE event 
        SET status = ?, approved_at = NOW()
        WHERE event_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$status, $event_id]);
?>

    <?php header('Location: event-request-list.php'); 
        exit()?>