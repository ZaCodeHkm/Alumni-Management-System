<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <main>
        <div class="container">
            <h1>Dashboard</h1>
        </div>

        <div class="container">
            <section class="dashboard-stats">
                <div class="stat-card">
                    <h3 id="total-users">1,240</h3>
                    <p>Total User Accounts</p>
                </div>

                <div class="stat-card">
                    <h3 id="pending-approvals">12</h3>
                    <p>Pending Event Approvals</p>
                </div>

                <div class="stat-card">
                    <h3 id="total-comments">458</h3>
                    <p>Total Engagement (Comments)</p>
                </div>

                <div class="stat-card">
                    <h3 id="live-events">24</h3>
                    <p>Active Live Events</p>
                </div>
            </section>
        </div>

    </main>

    <footer>
        <p>&copy; 2026 Alumni Engagement System</p>
    </footer>

</body>
</html>
