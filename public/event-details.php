<<<<<<< Updated upstream
<?php session_start(); 
?>

=======
>>>>>>> Stashed changes
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Details</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <?php include 'navbar.php'; ?>
<<<<<<< Updated upstream
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

    $commentSql = " SELECT c.content, c.created_at, u.name FROM comments c JOIN user u ON c.user_id = u.user_id WHERE c.event_id = ?
                    ORDER BY c.created_at DESC";

    $commentStmt = $pdo->prepare($commentSql);
    $commentStmt->execute([$event_id]);
    $comments = $commentStmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <main>
        <div class="container">
            <h1 id="detail-title"><?= htmlspecialchars($events['title']) ?></h1>
=======

    <main>
        <div class="container">
            <h1 id="detail-title">CES 2026</h1>
>>>>>>> Stashed changes

            <section class="details-section">
                <h3>Event Information</h3>

<<<<<<< Updated upstream
                <!-- Event Manager Action Buttons -->
                <div class="button-container mb-20">
                    <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'event_manager')): ?>
                        <a href="event-edit.php?id=<?= $events['event_id'] ?>" class="btn">Edit Event</a>
                        <form action="event-delete-action.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this event?');">
                            <input type="hidden" name="event_id" value="<?= htmlspecialchars($events['event_id']) ?>">
                            <button type="submit" class="btn btn-danger">Delete Event</button>
                        </form>
                        <?php endif; ?>
                    <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'event_manager' || $_SESSION['role'] === 'admin')): ?> <!-- admin can also view attendees -->
                        <form action="event-attendees.php?id=<?= $events['event_id'] ?>" method="POST">
                            <input type="hidden" name="event_id" value="<?= htmlspecialchars($events['event_id']) ?>">
                            <button type="submit" class="btn btn-secondary">View Attendees</button>
                        </form>
                    <?php endif; ?>
                </div>
                        
                <p><strong>Date:</strong> <span id="detail-date"><?= htmlspecialchars($events['event_date']) ?></span></p>
                <p><strong>Time:</strong> <span id="detail-time"><?= htmlspecialchars($events['start_time']) ?> to <?= htmlspecialchars($events['end_time']) ?></span></p>
                <p><strong>Location:</strong> <span id="detail-location"><?= htmlspecialchars($events['location']) ?></span></p>
                <p><strong>Type:</strong> <span id="detail-type"><?= htmlspecialchars($events['type']) ?></span></p>
                <p><strong>Capacity:</strong> <span id="detail-capacity"><?= htmlspecialchars($events['capacity']) ?> pax</span></p>
                <p><strong>About:</strong> <?= htmlspecialchars($events['description']) ?></p><br>

                <!-- Join Form (disappears for eventmng and admin. alumni exclusive or not based on event) -->
                <?php if (!($_SESSION['role'] === 'admin') && !($_SESSION['role'] === 'event_manager')): ?>
                    <?php if (($_SESSION['role'] === 'alumni') || !$isAlumniExclusive): ?>
                    <form action="event-regist.php" method="POST">
                        <input type="hidden" name="event_id" value="<?= htmlspecialchars($events['event_id']) ?>">
                        
                        <!-- success message display -->
                        <?php if (!empty($_SESSION['success'])): ?>
                            <div class="alert alert-success">
                                <?= htmlspecialchars($_SESSION['success']) ?>
                            </div>
                            <?php unset($_SESSION['success']); ?>
                        <?php endif; ?>
                        
                        <!-- error message display -->
                        <?php if (!empty($_SESSION['error'])): ?>
                            <div class="alert alert-error">
                                <?= htmlspecialchars($_SESSION['error']) ?>
                            </div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>

                        <button type="submit" class="btn">Join This Event</button>
                    </form>
                    <?php endif; ?>
=======
                <!-- Admin/ Event Manager Action Buttons -->
                <div class="button-container mb-20">
                    <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'eventmanager')): ?>
                        <a href="event-edit.php" class="btn">Edit Event</a>
                        <button class="btn btn-danger" type="submit">Delete Event</button>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'eventmanager' || $_SESSION['role'] === 'admin')): ?> <!-- admin can only view attendees -->
                        <a href="event-attendees.php" class="btn btn-secondary">View Attendees</a> 
                    <?php endif; ?>
                </div>

                <p><strong>Date:</strong> <span id="detail-date">January 25, 2026</span></p>
                <p><strong>Time:</strong> <span id="detail-time">9:00 AM to 6:00 PM</span></p>
                <p><strong>Location:</strong> <span id="detail-location">Grand Hall</span></p>
                <p><strong>Capacity:</strong> <span id="detail-capacity">100 pax</span></p>
                <p><strong>About:</strong> This symposium is about Coding and Programming.</p>
                <p><strong>Visibility:</strong> Alumni</p>

                <!-- Join Form (alumni/ students only) -->
                 <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'alumni' || $_SESSION['role'] === 'student')): ?>
                    <form action="#" method="POST" class="mt-20">
                        <input type="hidden" name="event_id" value="1">
                        <button type="submit" class="btn">Join This Event</button>
                    </form>
>>>>>>> Stashed changes
                <?php endif; ?>
            </section>

            <section class="details-section">
<<<<<<< Updated upstream
                <p class="creation-details">Created by: <?= htmlspecialchars($events['created_by']) ?></p>
                <p class="creation-details">Created at: <?= htmlspecialchars($events['created_at']) ?></p>
                <p class="approval-details">Approved by: <?= htmlspecialchars($events['approved_by']) ?></p>
                <p class="approval-details">Approved at: <?= htmlspecialchars($events['approved_at']) ?></p>
=======
                <p class="creation-details">Created by: Event Manager</p>
                <p class="creation-details">Created at: February 20, 2025</p>
                <p class="approval-details">Approved by: Event Manager</p>
                <p class="approval-details">Approved at: March 27, 2025</p>
>>>>>>> Stashed changes
            </section>

            <section class="comments-section">
                <h3>Comments</h3>
                <div id="comments-list">
<<<<<<< Updated upstream
                    <?php if (empty($comments)): ?>
                        <p>No comments yet.</p>
                    <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment">
                                <strong><?= htmlspecialchars($comment['name']) ?>:</strong>
                                <?= htmlspecialchars($comment['content']) ?>
                                <div class="comment-date">
                                    <?= htmlspecialchars($comment['created_at']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                    <!-- Add Comment Form -->
                    <?php if ($_SESSION['role'] === 'alumni' || $_SESSION['role'] === 'student'): ?>
                        <form action="comment-submit.php?id=<?= $event_id ?>" method="POST" class="comment-form">
                            <textarea name="content" placeholder="Write a comment..." required></textarea>
                            <div class="button-container">
                                <button type="submit" class="btn">Post Comment</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <p>Only Alumni and Students can post comments.</p>
                    <?php endif; ?>
                </div>
=======
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
>>>>>>> Stashed changes
            </section>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 Alumni Engagement System</p>
    </footer>

</body>
</html>
