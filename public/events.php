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

        <section class="list-container" id="event-list">
            
            <!-- Placeholder Event 1 -->
            <article class="list-item">
                <div class="flex-between">
                    <h2 id="event-title-1">ACES 2026</h2>
                    <a href="event-details.html" class="card-link">View Details</a>
                </div>
                <p><strong>Date:</strong> <span id="event-date-1">January 25, 2026</span></p>
                <p><strong>Location:</strong> <span id="event-loc-1">Grand Hall</span></p>
                <p><strong>Description:</strong> A gathering of tech enthusiasts.</p>
            </article>

            <!-- Placeholder Event 2 -->
            <article class="list-item">
                <div class="flex-between">
                    <h2 id="event-title-2">Career Fair 2026</h2>
                    <a href="event-details.html" class="card-link">View Details</a>
                </div>
                <p><strong>Date:</strong> February 10, 2026</p>
                <p><strong>Location:</strong> University Courtyard</p>
                <p><strong>Description:</strong> Connect with top employers.</p>
            </article>

        </section>
    </main>

    <footer>
        <p>&copy; 2026 Alumni Engagement System</p>
    </footer>

</body>
</html>
