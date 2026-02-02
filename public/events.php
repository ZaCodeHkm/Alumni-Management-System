<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Events</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <nav>
        <?php include 'navbar.php'; ?>
    </nav>

    <?php
        $host = "localhost";
        $db = "sofeng";
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

    $sql = "SELECT event_id, title, description, event_date, location, start_time, end_time
            FROM event
            ORDER BY event_date ASC";

    $stmt = $pdo->query(query: $sql);

    $events = $stmt->fetchAll(mode: PDO::FETCH_ASSOC);
    ?>

    <main>
        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'eventmanager')): ?>
            <h1 class="page-title">Event Management</h1> <!--admins and event managers only-->
            <!-- Event Management -->
            <div class="container">
                <div class="flex-between">
                    <h2>Management Actions</h2>
                    <div>
                        <a href="event-create.html" class="btn">Create Event</a>
                        <a href="event-request-list.html" class="btn btn-secondary">View Requests</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <h1 class="page-title">Upcoming Events</h1>

        <!-- Alumni can request event creation -->
        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'alumni')): ?>
            <div class="container">
                <div class="flex-between">
                    <p>Want to organize an event?</p>
                    <a href="event-request.html" class="btn">Request Event Creation</a>
                </div>
            </div>
        <?php endif; ?>
            
            <!-- Placeholder Event 1 -->
        <section class="list-container" id="event-list">

            <?php if (count($events) === 0): ?>
                <p>No events available.</p>
            <?php endif; ?>

            <?php foreach ($events as $event): ?>
                <article class="list-item">
                    <div class="flex-between">
                        <h2><?= htmlspecialchars($event['title']) ?></h2>
                        <a href="event-details.php?id=<?= $event['event_id'] ?>" class="card-link">
                            View Details
                        </a>
                    </div>

                    <p><strong>Date:</strong> <?= htmlspecialchars($event['event_date']) ?></p>
                    <p><strong>Time:</strong> <?= htmlspecialchars($event['start_time']) ?> to <?= htmlspecialchars($event['end_time']) ?></p>
                    <p><strong>Location:</strong> <?= htmlspecialchars($event['location']) ?></p>
                    <p><strong>Description:</strong> <?= htmlspecialchars($event['description']) ?></p>
                </article>
            <?php endforeach; ?>

</section>

        </section>
    </main>

    <footer>
        <p>&copy; 2026 Alumni Engagement System</p>
    </footer>

</body>
</html>
