<nav class="navbar">
    <a href="home.php" class="navbar-brand">AES</a>
    
    <div class="navbar-links">
        <a href="events.php">Events</a>
        <a href="mentorship.php">Mentorship</a>
        
        <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['alumni', 'event_manager', 'admin'])): ?>
            <a href="job_list.php">Jobs</a>
        <?php endif; ?>
        
        <a href="notification.php">News</a>
        
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="user_check.php">Admin</a>
        <?php endif; ?>

        <a href="logout.php" class="logout-link" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
        
        <a href="profile.php" class="navbar-profile">
            <?php echo strtoupper($_SESSION['name'][0] ?? 'U'); ?>
        </a>
    </div>
</nav>
