<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Details</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!--navbar-->
    <?php include 'navbar.php'; ?>
    <!--sql query to fetch event details-->
    <?php
        $host = "localhost";
        $db = "sofeng";
        $user = "root";
        $pass = "";
        $event_id = $_GET['id'];
        $role = $_SESSION['role'];


        $canJoin = $role === 'alumni'|| ($role === 'student' && !$isAlumniExclusive);

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

    $sql = "SELECT event_id, title, description, event_date, location, type, start_time, end_time, capacity, created_by, approved_by, approved_at, created_at, is_alumni_exclusive 
            FROM event WHERE event_id = ?";

    $stmt = $pdo->prepare(query: $sql);
    $stmt->execute(params: [$event_id]);
    $events = $stmt->fetch(mode: PDO::FETCH_ASSOC);
    $isAlumniExclusive = (bool)$events['is_alumni_exclusive'];
    ?>

    <main>
        <div class="container">
            <h1 id="detail-title"><?= htmlspecialchars($events[0]['title']) ?></h1>

            <section class="details-section">
                <h3>Event Information</h3>

                <p><a href="event-attendees.php?id=<?= $events['event_id'] ?>">View Event Attendees</a></p>

                <!-- Admin/ Event Manager Action Buttons -->
                <div class="button-container mb-20">
                    <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'event_manager')): ?>
                        <a href="event-edit.php?id=<?= $events['event_id'] ?>" class="btn">Edit Event</a>
                        <button class="btn btn-danger" type="submit">Delete Event</button>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'event_manager' || $_SESSION['role'] === 'admin')): ?> <!-- admin can only view attendees -->
                        <a href="event-attendees.php" class="btn btn-secondary">View Attendees</a> 
                    <?php endif; ?>
                </div>
                        
                <p><strong>Date:</strong> <span id="detail-date"><?= htmlspecialchars($events['event_date']) ?></span></p>
                <p><strong>Time:</strong> <span id="detail-time"><?= htmlspecialchars($events['start_time']) ?> to <?= htmlspecialchars($events['end_time']) ?></span></p>
                <p><strong>Location:</strong> <span id="detail-location"><?= htmlspecialchars($events['location']) ?></span></p>
                <p><strong>Type:</strong> <span id="detail-type"><?= htmlspecialchars($events['type']) ?></span></p>
                <p><strong>Capacity:</strong> <span id="detail-capacity"><?= htmlspecialchars($events['capacity']) ?> pax</span></p>
                <p><strong>About:</strong> <?= htmlspecialchars($events['description']) ?></p><br>

                <!-- Join Form (alumni exclusive or not based on event) -->
                <?php if (($_SESSION['role'] === 'alumni') || !$isAlumniExclusive): ?>
                    <form action="event-regist.php" method="POST">
                        <input type="hidden" name="event_id" value="<?= htmlspecialchars($events['event_id']) ?>">
                        <button type="submit" class="btn">Join This Event</button>
                    </form>
                <?php else: ?>
                    <p class="error-message">Thanks for your interest! This event is currently open to alumni only.</p>
                <?php endif; ?>
            </section>
                    <?php echo "!FOR TESTING! Event ID: " . htmlspecialchars($events['event_id']); ?>


            <section class="details-section">
                <p class="creation-details">Created by: <?= htmlspecialchars($_SESSION['role'])?> #<?= htmlspecialchars($events['created_by']) ?></p>
                <p class="creation-details">Created at: <?= htmlspecialchars($events['created_at']) ?></p>
                <p class="approval-details">Approved by: EM #<?= htmlspecialchars($events['approved_by']) ?></p>
                <p class="approval-details">Approved at: <?= htmlspecialchars($events['approved_at']) ?></p>
            </section>

            <section class="comments-section">
                <h3>Comments</h3>
                <div id="comments-list">
                    <!-- Backend loops comments here -->
                    <div class="comment">
                        <strong>John:</strong> The event is boring.
                    </div>
                </div>

                <!-- Add Comment Form -->
                <form action="#" method="POST" class="comment-form">
                    <textarea name="comment_text" placeholder="Write a comment..." required></textarea>
                    <div class="button-container">
                        <button type="submit" class="btn">Post Comment</button>
                    </div>
                </form>
            </section>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 Alumni Engagement System</p>
    </footer>

</body>
</html>
