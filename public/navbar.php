<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['role'] ?? '';
$name = $_SESSION['name'] ?? 'User';
$initial = strtoupper($name[0] ?? 'U');

// Determine base URLs based on role
$eventsPage = 'events.php';
$jobsPage = 'job-list.php';
$mentorshipPage = 'mentorship.php';

if ($role === 'student') {
    $eventsPage = 'events.php';
    $mentorshipPage = 'mentorship.php';
} elseif ($role === 'alumni') {
    $eventsPage = 'events-alumni.php';
    $jobsPage = 'job-list-alumni.php';
    $mentorshipPage = 'mentorship.php'; // Alumni see their own + incoming requests
} elseif ($role === 'admin') {
    $eventsPage = 'events-admin.php';
    $mentorshipPage = 'mentorship-admin.php'; // View-only
} elseif ($role === 'event_manager') {
    $eventsPage = 'events-admin.php';
}
?>

<nav class="navbar">
    <a href="<?php echo $eventsPage; ?>" class="navbar-brand">AES</a>
    <div class="navbar-links">
        
        <!-- Events - All roles -->
        <a href="<?php echo $eventsPage; ?>">Events</a>

        <!-- Search/Messages - Student, Alumni, Admin (NOT Event Manager) -->
        <?php if (in_array($role, ['student', 'alumni', 'admin'])): ?>
            <a href="search.php">Search</a>
        <?php endif; ?>

        <!-- Mentorship - Student, Alumni, Admin -->
        <?php if (in_array($role, ['student', 'alumni', 'admin'])): ?>
            <a href="<?php echo $mentorshipPage; ?>">Mentorship</a>
        <?php endif; ?>

        <!-- Jobs - Alumni (can post/apply), Admin (view) -->
        <?php if ($role === 'alumni'): ?>
            <a href="<?php echo $jobsPage; ?>">Jobs</a>
        <?php elseif ($role === 'admin'): ?>
            <a href="job-list.php">Jobs</a>
        <?php endif; ?>

        <!-- Dashboard - Admin & Event Manager -->
        <?php if (in_array($role, ['admin', 'event_manager'])): ?>
            <a href="dashboard.php">Dashboard</a>
        <?php endif; ?>

        <!-- Notifications - Admin & Event Manager -->
        <?php if ($role === 'admin'): ?>
            <a href="notifications-admin.html">Notifications</a>
        <?php elseif ($role === 'event_manager'): ?>
            <a href="notifications-eventmanager.html">Notifications</a>
        <?php endif; ?>

        <!-- Users - Admin only -->
        <?php if ($role === 'admin'): ?>
            <a href="user_check.php">Users</a>
        <?php endif; ?>

        <!-- Profile -->
        <a href="profile.php" class="navbar-profile"><?php echo $initial; ?></a>

    </div>
</nav>
