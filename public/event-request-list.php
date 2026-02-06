<<<<<<< Updated upstream
<?php session_start(); ?>

=======
>>>>>>> Stashed changes
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Requests</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

<<<<<<< Updated upstream
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

    $sql = "SELECT event_id, title, description, event_date, location, start_time, end_time
            FROM event
            WHERE status = 'pending'
            ORDER BY event_date ASC";

    $stmt = $pdo->query(query: $sql);

    $events = $stmt->fetchAll(mode: PDO::FETCH_ASSOC);
    ?>

    <main>
        <h1 class="page-title">Event Requests</h1>

        <section class="list-container" id="event-request-list">

            <?php if (count($events) === 0): ?>
                <p>No requests available.</p>
            <?php endif; ?>

            <?php foreach ($events as $event): ?>
                <article class="list-item">
                    <div class="flex-between">
                        <h2><?= htmlspecialchars($event['title']) ?></h2>
                        <a href="event-details.php?id=<?= $event['event_id'] ?>" class="card-link">
                            View Details
                        </a>
                    </div>
                    <p><strong>Title:</strong> <?= htmlspecialchars($event['title']) ?></p>
                    <p><strong>Date:</strong> <?= htmlspecialchars($event['event_date']) ?></p>
                    <p><strong>Time:</strong> <?= htmlspecialchars($event['start_time']) ?> to <?= htmlspecialchars($event['end_time']) ?></p>
                    <p><strong>Location:</strong> <?= htmlspecialchars($event['location']) ?></p>
                    <p><strong>Description:</strong> <?= htmlspecialchars($event['description']) ?></p>
                    
                    <!-- Buttons -->
                    <form action="event-request-action.php" method="POST" class="button-container">
                        
                        <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">

                        <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">

                        <button type="submit" name="action" value="approve" class="btn">Approve</button>

                        <button type="submit" name="action" value="reject" class="btn btn-danger">Reject</button>
                        </form>
                        
                </article>
            <?php endforeach; ?>
        </section>
            
=======
    <main>
        <h1 class="page-title">Event Requests</h1>

        <!-- Placeholder information. Final version will take data from event database -->
        <section class="list-container">
            
            <article class="list-item">
                <div class="flex-between">
                    <h2>Event Request</h2>
                    <a href="event-details-admin.html" class="card-link">View Event Details</a>
                </div>
                
                <p><strong>Title:</strong> <span class="eventName">MMU Alumni Guest Talk</span></p> <!-- alumni exclusivity based on 'is_alumni_exclusive' value in DB -->
                <p><strong>Date:</strong> <span class="eventDate">26th January 2026</span></p>
                <p><strong>Time:</strong> <span class="eventTime">09:30 AM</span></p>
                <p><strong>Location:</strong> <span class="eventLocation">Physical @ DTC</span></p>
                <p><strong>Description:</strong> <span class="eventDescription">Guest talk by alumni on career development and industry insights.</span></p>
                
                <div class="button-container">
                    <button class="btn" type="submit">Approve</button>
                    <button class="btn btn-danger" type="submit">Reject</button>
                </div>
            </article>

            <article class="list-item">
                <div class="flex-between">
                    <h2>Event Request</h2>
                    <a href="event-details.php" class="card-link">View Event Details</a>
                </div>
                
                <p><strong>Title:</strong> <span class="eventName">CES 2026</span></p>
                <p><strong>Date:</strong> <span class="eventDate">25th January 2026</span></p>
                <p><strong>Time:</strong> <span class="eventTime">09:30 AM</span></p>
                <p><strong>Location:</strong> <span class="eventLocation">Physical @ Las Vegas Convention Centre</span></p>
                <p><strong>Description:</strong> <span class="eventDescription">CES is a global technology event showcasing the latest innovations in consumer electronics.</span></p>
                
                <div class="button-container">
                    <button class="btn" type="submit">Approve</button>
                    <button class="btn btn-danger" type="submit">Reject</button>
                </div>
            </article>

        </section>
>>>>>>> Stashed changes
    </main>

    <footer>
        <p>&copy; 2026 Alumni Engagement System</p>
    </footer>

</body>
</html>
