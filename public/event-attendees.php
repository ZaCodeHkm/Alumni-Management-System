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

    <!--sql query to fetch event attendee info-->
    <?php
        $host = "localhost";
        $db = "sofeng";
        $user = "root";
        $pass = "";
        $event_id = $_GET['id'];
        $event_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if (!$event_id) {
                die("Invalid event ID");
            }

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


    $sql = "SELECT ea.attendance_id,ea.user_id, ea.status, ea.registered_at, u.name, u.email, u.role FROM event_attendance ea JOIN user u ON ea.user_id = u.user_id
    WHERE ea.event_id = ? ORDER BY ea.registered_at ASC";
    $sql2 = "SELECT event_id, title, description, event_date, location, start_time, end_time, capacity, created_by, approved_by, approved_at, created_at FROM event 
    WHERE event_id = ?";

    $stmt = $pdo->prepare(query: $sql);
    $stmt->execute(params: [$event_id]);
    $event_attendees = $stmt->fetchAll(mode: PDO::FETCH_ASSOC);

    $stmt2 = $pdo->prepare(query: $sql2);
    $stmt2->execute(params: [$event_id]);
    $events = $stmt2->fetch(mode: PDO::FETCH_ASSOC);
    ?>

    <main>
        <h1 class="page-title">Event Attendees</h1>

        <div class="container">
            <div class="flex-between">
                <h2><?= htmlspecialchars($events['title']) ?></h2>
                <a href="event-details.php?id=<?= $event_id ?>" class="btn btn-secondary">Back to Event</a>
            </div>
        </div>

        <section class="list-container" id="attendee-list">

            <?php if (empty($event_attendees)): ?>
                <p>No attendees.</p>
            <?php endif; ?>

            <?php foreach ($event_attendees as $attendee): ?>
                <article class="list-item">
                    <h2><?= htmlspecialchars(ucfirst($attendee['role'])) ?></h2>

                    <p>
                        <strong>Name:</strong>
                        <span class="attendeeName">
                            <?= htmlspecialchars($attendee['name']) ?>
                        </span>
                    </p>

                    <p>
                        <strong>ID:</strong>
                        <span class="attendeeID">
                            <?= htmlspecialchars($attendee['user_id']) ?>
                        </span>
                    </p>

                    <p>
                        <strong>Email:</strong>
                        <span>
                            <?= htmlspecialchars($attendee['email']) ?>
                        </span>
                    </p>
                </article>
            <?php endforeach; ?>
            </section>

    </main>

    <footer>
        <p>&copy; 2026 Alumni Engagement System</p>
    </footer>

</body>
</html>
