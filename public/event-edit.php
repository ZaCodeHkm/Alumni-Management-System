<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <!--sql query to fetch event details-->
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

    $sql = "SELECT event_id, title, description, event_date, location, start_time, end_time, capacity, created_by, approved_by, approved_at, created_at, is_alumni_exclusive 
            FROM event WHERE event_id = ?";

    $stmt = $pdo->prepare(query: $sql);
    $stmt->execute(params: [$event_id]);
    $events = $stmt->fetch(mode: PDO::FETCH_ASSOC);
    $isAlumniExclusive = (bool)$events['is_alumni_exclusive'];
    ?>

    <main>
        <h1 class="page-title">Edit Event</h1>

        <div class="container">
            <form action="event-edit-submit.php" method="POST" class="form">
                <div class="flex-between mb-20">
                    <h2>Event Details</h2>
                </div>

                <!-- SEND EVENT ID -->
                <input type="hidden" name="event_id" value="<?= $events['event_id'] ?>">

                <label>Event Title</label>
                <input name="event_title" type="text" placeholder="Enter event title..." value="<?= htmlspecialchars($events['title']) ?>"required>

                <label>Description</label>
                <textarea name="description" placeholder="Enter event description..." required><?= htmlspecialchars($events['description']) ?></textarea>

                <label>Date</label>
                <input name="event_date" type="date" class="input-half" value="<?= htmlspecialchars($events['event_date']) ?>" required>

                <label>Start Time</label>
                <input name="start_time" type="time" class="input-half" value="<?= htmlspecialchars($events['start_time']) ?>" required>

                <label>End Time</label>
                <input name="end_time" type="time" class="input-half" value="<?= htmlspecialchars($events['end_time']) ?>" required>

                <label>Venue</label>
                <input name="location" type="text" placeholder="Enter location details..." value="<?= htmlspecialchars($events['location']) ?>" required>

                <label>Type</label>
                <select name="event_type" required>
                    <option value="Physical" <?= $events['type'] === 'Physical' ? 'selected' : '' ?>>Physical</option>
                    <option value="Online" <?= $events['type'] === 'Online' ? 'selected' : '' ?>>Online</option>
                </select>

                <label>Visibility</label>
                <select name="is_alumni_exclusive" required>
                    <option value="0" <?= !$isAlumniExclusive ? 'selected' : '' ?>>All</option>
                    <option value="1" <?= $isAlumniExclusive ? 'selected' : '' ?>>Alumni</option>
                </select>

                <label>Capacity</label>
                <input name="capacity" type="number" placeholder="Enter event max capacity..." value="<?= htmlspecialchars($events['capacity']) ?>" required>

                <div class="button-container">
                    <a href="events.php" class="btn btn-secondary">Cancel</a>
                    <button class="btn" type="submit">Save Changes</button>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 Alumni Engagement System</p>
    </footer>

</body>
</html>
