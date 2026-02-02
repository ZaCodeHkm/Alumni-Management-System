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
    </main>

    <footer>
        <p>&copy; 2026 Alumni Engagement System</p>
    </footer>

</body>
</html>
