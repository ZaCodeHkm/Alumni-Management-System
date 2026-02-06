<?php session_start(); ?>

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

    <!--sql query-->
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

    $sql = "SELECT event_id, title, description, event_date, location, start_time, end_time, is_alumni_exclusive
            FROM event
            ORDER BY event_date ASC";

    if (in_array($_SESSION['role'], ['alumni', 'admin', 'event_manager'])) {
    $sql .= "1=1";  // Show all events
    } else {
        $sql .= "(is_alumni_exclusive = 0 OR is_alumni_exclusive IS NULL)";  // Show only public events
    }

    $stmt = $pdo->query(query: $sql);

    $events = $stmt->fetchAll(mode: PDO::FETCH_ASSOC);
    ?>

    <main>
        <?php echo "!FOR TESTING! User role: " . htmlspecialchars($_SESSION['role']); ?>
        <h1 class="page-title">Upcoming Events</h1>

        <!-- Event Management -->
        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'event_manager')): ?>  <!--event managers only-->
            <div class="container">
                <div class="flex-between">
                    <h2>Management Actions</h2>
                    <div>
                        <a href="event-create.php" class="btn">Create Event</a>
                        <a href="event-request-list.php" class="btn btn-secondary">View Requests</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Alumni event creation request -->
        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'alumni')): ?>
            <div class="container">
                <div class="flex-between">
                    <p>Want to organize an event?</p>
                    <a href="event-request.php" class="btn">Request Event Creation</a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Event Template -->
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
