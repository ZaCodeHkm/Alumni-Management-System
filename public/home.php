<?php
session_start();
require_once 'auth.php';
require_once __DIR__ . '/../database/db.php';

require_login();

$userName = htmlspecialchars($_SESSION['name']);
$userRole = $_SESSION['role'] ?? 'student';
$userId = $_SESSION['user_id'];

// Stats
$stats = [
    'users' => $pdo->query("SELECT COUNT(*) FROM user WHERE is_active = 1")->fetchColumn(),
    'events' => $pdo->query("SELECT COUNT(*) FROM event WHERE status = 'approved' AND start_time > NOW()")->fetchColumn(),
    'jobs' => $pdo->query("SELECT COUNT(*) FROM jobposting WHERE availability = 1")->fetchColumn()
];

// Upcoming events
$stmt = $pdo->prepare("
    SELECT e.event_id, e.title, e.start_time, e.location,
           COUNT(ea.attendance_id) as attendees
    FROM event e
    LEFT JOIN event_attendance ea ON e.event_id = ea.event_id
    WHERE e.status = 'approved' AND e.start_time > NOW()
    GROUP BY e.event_id
    ORDER BY e.start_time ASC
    LIMIT 3
");
$stmt->execute();
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recent jobs
$stmt = $pdo->prepare("
    SELECT j.job_id, j.title, j.company_name, j.created_at
    FROM jobposting j
    WHERE j.availability = 1
    ORDER BY j.created_at DESC
    LIMIT 3
");
$stmt->execute();
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>MMU Alumni Portal</title>
    <style>
        /* Hero Section - Adobe Dark Theme */
        .hero { 
            background: linear-gradient(135deg, var(--bg-elevated) 0%, var(--bg-body) 100%); 
            padding: 100px 20px 60px; 
            text-align: center; 
            color: var(--text-primary);
            border-bottom: 1px solid var(--border);
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 4px;
            height: 100px;
            background: linear-gradient(to bottom, var(--accent), transparent);
        }
        .hero h1 { 
            font-size: 2.8rem; 
            font-weight: 700; 
            margin-bottom: 16px; 
            color: var(--accent);
            letter-spacing: -0.02em;
        }
        .hero p { 
            font-size: 1.15rem; 
            opacity: 0.9; 
            max-width: 600px; 
            margin: 0 auto 32px; 
            color: var(--text-secondary);
            line-height: 1.6;
        }
        .hero-actions { 
            display: flex; 
            gap: 12px; 
            justify-content: center; 
            flex-wrap: wrap; 
        }
        .btn-hero { 
            padding: 12px 28px; 
            border-radius: 20px; 
            font-weight: 600; 
            text-decoration: none; 
            transition: var(--transition);
            border: 1px solid;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-primary { 
            background: var(--accent); 
            color: var(--bg-body);
            border-color: var(--accent);
        }
        .btn-primary:hover { 
            background: var(--accent-hover);
            border-color: var(--accent-hover);
            transform: translateY(-2px); 
            box-shadow: 0 4px 12px var(--accent-soft);
        }
        .btn-outline { 
            background: transparent; 
            color: var(--text-primary); 
            border-color: var(--border);
        }
        .btn-outline:hover { 
            background: var(--bg-hover); 
            border-color: var(--text-secondary);
        }
        
        /* Stats Section */
        .stats { 
            background: var(--bg-surface); 
            margin: -30px auto 50px; 
            max-width: 850px; 
            border-radius: var(--radius); 
            box-shadow: var(--shadow); 
            display: grid; 
            grid-template-columns: repeat(3, 1fr); 
            padding: 32px; 
            gap: 32px;
            border: 1px solid var(--border);
        }
        .stat { 
            text-align: center;
            position: relative;
        }
        .stat:not(:last-child)::after {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 1px;
            height: 60%;
            background: var(--border);
        }
        .stat-number { 
            font-size: 2.2rem; 
            font-weight: 700; 
            color: var(--accent); 
            display: block; 
        }
        .stat-label { 
            color: var(--text-secondary); 
            font-size: 0.85rem; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
            margin-top: 6px; 
        }
        
        /* Container */
        .home-container { 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 0 20px 60px; 
        }
        
        /* Section Headers */
        .section-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 24px; 
        }
        .section-header h2 { 
            font-size: 1.6rem; 
            font-weight: 600;
            color: var(--accent);
            margin-bottom: 0;
        }
        .link-arrow { 
            color: var(--accent); 
            text-decoration: none; 
            font-weight: 500;
            transition: var(--transition);
        }
        .link-arrow:hover { 
            color: var(--accent-hover);
        }
        
        /* Grid Layout */
        .home-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); 
            gap: 24px; 
            margin-bottom: 50px; 
        }
        
        /* Cards */
        .home-card { 
            background: var(--bg-surface); 
            border: 1px solid var(--border); 
            border-radius: var(--radius); 
            padding: 20px; 
            transition: var(--transition);
        }
        .home-card:hover { 
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5); 
            border-color: var(--accent);
            transform: translateY(-2px);
        }
        .home-card h3 { 
            font-size: 1.15rem; 
            margin-bottom: 10px; 
            color: var(--text-primary); 
        }
        .card-meta { 
            color: var(--text-secondary); 
            font-size: 0.85rem; 
            margin-bottom: 6px; 
        }
        .company { 
            color: var(--accent); 
            font-weight: 600; 
            margin-bottom: 6px; 
            font-size: 0.95rem;
        }
        
        /* Features Section */
        .features { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); 
            gap: 24px; 
            margin: 50px 0; 
        }
        .feature { 
            text-align: center; 
            padding: 24px 16px;
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            transition: var(--transition);
        }
        .feature:hover {
            border-color: var(--accent);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }
        .feature-icon { 
            font-size: 2.5rem; 
            margin-bottom: 12px; 
        }
        .feature h3 { 
            font-size: 1.2rem; 
            margin-bottom: 10px;
            color: var(--text-primary);
        }
        .feature p { 
            color: var(--text-secondary); 
            line-height: 1.5;
            margin: 0;
        }
        
        /* Empty State */
        .empty { 
            text-align: center; 
            padding: 32px; 
            color: var(--text-secondary); 
            border: 2px dashed var(--border); 
            border-radius: var(--radius);
            background: var(--bg-elevated);
        }
        .empty p {
            margin: 0;
            color: var(--text-secondary);
        }
        
        /* Footer */
        .home-footer { 
            background: var(--bg-surface); 
            color: var(--text-secondary); 
            text-align: center; 
            padding: 24px 20px;
            border-top: 1px solid var(--border);
            margin-top: 60px;
        }
        .home-footer p {
            margin: 0;
            color: var(--text-secondary);
        }
        
        @media (max-width: 768px) { 
            .hero h1 { font-size: 2rem; } 
            .hero p { font-size: 1rem; }
            .stats { 
                grid-template-columns: 1fr;
                padding: 24px;
            }
            .stat:not(:last-child)::after {
                display: none;
            }
            .home-grid { grid-template-columns: 1fr; } 
            .features { grid-template-columns: 1fr; }
            .hero-actions {
                flex-direction: column;
                align-items: stretch;
            }
            .btn-hero {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="hero">
    <h1>MMU Alumni Portal</h1>
    <p>Connect with fellow alumni, find mentorship opportunities, and explore career paths. Your professional network starts here.</p>
    <div class="hero-actions">
        <a href="mentorship.php" class="btn-hero btn-primary">Find a Mentor</a>
        <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['alumni', 'event_manager', 'admin'])): ?>
            <a href="job_list.php" class="btn-hero btn-outline">Browse Jobs</a>
        <?php endif; ?>
    </div>
</div>

<div class="stats">
    <div class="stat">
        <span class="stat-number"><?= number_format($stats['users']) ?></span>
        <span class="stat-label">Active Members</span>
    </div>
    <div class="stat">
        <span class="stat-number"><?= number_format($stats['events']) ?></span>
        <span class="stat-label">Upcoming Events</span>
    </div>
    <div class="stat">
        <span class="stat-number"><?= number_format($stats['jobs']) ?></span>
        <span class="stat-label">Job Openings</span>
    </div>
</div>

<div class="home-container">
    
    <div class="features">
        <div class="feature">
            <div class="feature-icon">🎯</div>
            <h3>Mentorship</h3>
            <p>Get guidance from experienced alumni or share your expertise with students.</p>
        </div>
        <div class="feature">
            <div class="feature-icon">💼</div>
            <h3>Career Growth</h3>
            <p>Access exclusive job opportunities from companies seeking MMU talent.</p>
        </div>
        <div class="feature">
            <div class="feature-icon">🤝</div>
            <h3>Networking</h3>
            <p>Join events and connect with professionals across industries.</p>
        </div>
    </div>
    
    <div class="home-grid">
        <div>
            <div class="section-header">
                <h2>Upcoming Events</h2>
                <a href="events.php" class="link-arrow">View all →</a>
            </div>
            <?php if (!empty($events)): ?>
                <?php foreach ($events as $e): ?>
                    <div class="home-card">
                        <h3><?= htmlspecialchars($e['title']) ?></h3>
                        <div class="card-meta">📅 <?= date('M j, Y • g:i A', strtotime($e['start_time'])) ?></div>
                        <div class="card-meta">📍 <?= htmlspecialchars($e['location']) ?></div>
                        <?php if ($e['attendees'] > 0): ?>
                            <div class="card-meta">👥 <?= $e['attendees'] ?> registered</div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty"><p>No upcoming events</p></div>
            <?php endif; ?>
        </div>
        
        <div>
            <div class="section-header">
                <h2>Latest Jobs</h2>
                <a href="job_list.php" class="link-arrow">View all →</a>
            </div>
            <?php if (!empty($jobs)): ?>
                <?php foreach ($jobs as $j): ?>
                    <div class="home-card">
                        <h3><?= htmlspecialchars($j['title']) ?></h3>
                        <div class="company"><?= htmlspecialchars($j['company_name']) ?></div>
                        <div class="card-meta">Posted <?= date('M j, Y', strtotime($j['created_at'])) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty"><p>No job openings</p></div>
            <?php endif; ?>
        </div>
    </div>
    
</div>

<footer class="home-footer">
    <p>&copy; <?= date('Y') ?> MMU Alumni Portal. All rights reserved.</p>
</footer>
<?php include 'chat_widget.php'; ?>

</body>
</html>