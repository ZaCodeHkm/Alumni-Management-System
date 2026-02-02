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

    <main>
        <div class="container">
            <h1 id="detail-title">CES 2026</h1>

            <section class="details-section">
                <h3>Event Information</h3>

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
                <?php endif; ?>
            </section>

            <section class="details-section">
                <p class="creation-details">Created by: Event Manager</p>
                <p class="creation-details">Created at: February 20, 2025</p>
                <p class="approval-details">Approved by: Event Manager</p>
                <p class="approval-details">Approved at: March 27, 2025</p>
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
