<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Event</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <main>
        <h1 class="page-title">Request a New Event</h1>

        <div class="container">
<<<<<<< Updated upstream
            <form action="event-request-submit.php" method="POST" class="form">
                <h2>Event Request Form</h2>
                
                <label>Event Name</label>
                <input type="text" name="event_title" placeholder="Enter event name..." required>
=======
            <!-- NOTE: Retrieve data using 'name' attributes -->
            <form action="#" method="POST" class="form">
                <h2>Event Request Form</h2>
                
                <label>Event Name</label>
                <input type="text" name="event_name" placeholder="Enter event name..." required>
>>>>>>> Stashed changes

                <label>Date</label>
                <input type="date" name="event_date" class="input-half" required>

<<<<<<< Updated upstream
                <label>Start Time</label>
                <input type="time" name="start_time" class="input-half" required>

                <label>End Time</label>   
                <input type="time" name="end_time" class="input-half" required>

                <label>Capacity</label>
                <input type="number" name="capacity" class="input-half" required>
=======
                <label>Time</label>
                <input type="time" name="event_time" class="input-half" required>
>>>>>>> Stashed changes

                <label>Location</label>
                <input type="text" name="location" placeholder="Enter location..." required>

                <label>Description</label>
                <textarea name="description" placeholder="Describe your event..." required></textarea>

<<<<<<< Updated upstream
                <label>Type</label>
                <select name="event_type" required>
                    <option value="physical">Physical</option>
                    <option value="online">Online</option>
                </select>

                <label>Visibility</label>
                <select name="is_alumni_exclusive" required>
                    <option value="0" <?= !$isAlumniExclusive ? 'selected' : '' ?>>All</option>
                    <option value="1" <?= $isAlumniExclusive ? 'selected' : '' ?>>Alumni</option>
                </select>

=======
>>>>>>> Stashed changes
                <div class="button-container">
                    <button type="reset" class="btn btn-secondary">Clear</button>
                    <button type="submit" class="btn">Submit Request</button>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 Alumni Engagement System</p>
    </footer>

</body>
</html>
