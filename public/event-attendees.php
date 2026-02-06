<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Attendees</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <!--sql query-->
    <?php
        $host = "localhost";
        $db = "sofeng";
        $user = "root";
        $pass = "";
        $event_id = $_GET['id'];
        $role = $_SESSION['role'];

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

    // $sql = "SELECT 
    //             user.name,
    //             user.user_id,
    //             user.email,
    //             user.role
    //         FROM event_attendance
    //         INNER JOIN user ON event_attendance.user_id = user.user_id
    //         WHERE event_id = ?
    //         ORDER BY user.name ASC";

    $sql = "SELECT event_attendance.user_id, user.name, user.email, user.role FROM event_attendance INNER JOIN user ON event_attendance.user_id = user.user_id WHERE event_id = ?";

    $stmt = $pdo->prepare(query: $sql);
    $stmt->execute(params: [$event_id]);
    $attendees = $stmt->fetchAll(mode: PDO::FETCH_ASSOC);
    ?>

    <main>
        <h1 class="page-title">Attendee List</h1>
        Event ID: <?= htmlspecialchars($event_id) ?>
        <div class="container">
            <div class="flex-between">
                <a href="event-details.php?id=<?= $event_id ?>" class="btn btn-secondary">Back to Event</a>
            </div>
        </div>

        <section class="list-container">
        <br>
            <?php if (count($attendees) === 0): ?>
                <p>No attendees for this event.</p>
            <?php endif; ?>
            <?php foreach ($attendees as $attendee): ?>
                <article class="list-item">
                    <h2><?= htmlspecialchars($attendee['name']) ?></h2>
                    <p><strong>Role:</strong> <?= htmlspecialchars($attendee['role']) ?></p>
                    <p><strong>ID:</strong> <span class="attendeeID"><?= htmlspecialchars($attendee['user_id']) ?></span></p>
                    <p><strong>Email:</strong> <span><?= htmlspecialchars($attendee['email']) ?></span></p>
                </article>
            <?php endforeach; ?>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 Alumni Engagement System</p>
    </footer>

</body>
</html>