<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <main>
        <h1 class="page-title">Create Event</h1>

        <div class="container">
            <form action="event-create-submit.php" method="POST" class="form">
                <h2>Event Creation Form</h2>
                
                <label>Event Name</label>
                <input type="text" name="event_title" placeholder="Enter event name..." required>

                <label>Date</label>
                <input type="date" name="event_date" class="input-half" required>

                <label>Start Time</label>
                <input type="time" name="start_time" class="input-half" required>

                <label>End Time</label>   
                <input type="time" name="end_time" class="input-half" required>

                <label>Capacity</label>
                <input type="number" name="capacity" class="input-half" required>

                <label>Location</label>
                <input type="text" name="location" placeholder="Enter location..." required>

                <label>Description</label>
                <textarea name="description" placeholder="Describe your event..." required></textarea>

                <label>Type</label>
                <select name="event_type" required>
                    <option value="physical">Physical</option>
                    <option value="online">Online</option>
                </select>

                <label>Visibility</label>
                <select name="is_alumni_exclusive" required>
                    <option value="0">All</option>
                    <option value="1">Alumni</option>
                </select>

                <div class="button-container">
                    <a href="events.php" class="btn btn-secondary">Cancel</a>
                    <button class="btn" type="submit">Create Event</button>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 Alumni Engagement System</p>
    </footer>

</body>
</html>
