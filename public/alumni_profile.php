<?php
session_start();
require_once 'auth.php';
require_once __DIR__ . '/../database/db.php';

require_login();

$alumniId = $_GET['id'] ?? 0;
$userId = $_SESSION['user_id'];

// Fetch alumni details
$stmt = $pdo->prepare("SELECT user_id, name, email, role FROM user WHERE user_id = ? AND role = 'alumni' AND is_active = TRUE");
$stmt->execute([$alumniId]);
$alumni = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$alumni) {
    $_SESSION['error'] = "Alumni profile not found.";
    header("Location: mentorship.php");
    exit;
}

// Fetch profile information
$stmt = $pdo->prepare("SELECT bio, education, contact_info, publications_summary FROM profile WHERE user_id = ?");
$stmt->execute([$alumniId]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

// If no profile exists, initialize empty array to prevent errors
if (!$profile) {
    $profile = [
        'bio' => '',
        'education' => '',
        'contact_info' => '',
        'publications_summary' => ''
    ];
}

// Fetch career history
$stmt = $pdo->prepare("SELECT career_id, job_title, company_name, start_date, end_date, description 
                       FROM career_history 
                       WHERE user_id = ? 
                       ORDER BY start_date DESC");
$stmt->execute([$alumniId]);
$careerHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if user already has a request with this mentor
$stmt = $pdo->prepare("SELECT mentorship_id, status FROM mentorship_request 
                       WHERE mentor_id = ? AND mentee_id = ? 
                       ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$alumniId, $userId]);
$existingRequest = $stmt->fetch(PDO::FETCH_ASSOC);

include 'navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($alumni['name']) ?> - Profile</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@400;600;700&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>

    <div class="profile-hero">
        <div class="hero-background"></div>
        <div class="container" style="background: transparent; border: none; box-shadow: none;">
            <a href="mentorship.php" class="btn-back-hero">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Back to Mentors
            </a>
                <div class="profile-hero-info">
                    <h1 class="profile-name"><?= htmlspecialchars($alumni['name']) ?></h1>
                    <?php if (!empty($careerHistory)): ?>
                        <p class="profile-current-role">
                            <?= htmlspecialchars($careerHistory[0]['job_title']) ?> at <?= htmlspecialchars($careerHistory[0]['company_name']) ?>
                        </p>
                    <?php else: ?>
                        <p class="profile-current-role" style="opacity: 0.7;">Alumni</p>
                    <?php endif; ?>
                    <div class="profile-contact-quick">
                        <a href="mailto:<?= htmlspecialchars($alumni['email']) ?>" class="contact-link">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                <path d="M3 8L10.89 13.26C11.25 13.48 11.75 13.48 12.11 13.26L20 8M5 19H19C20.1046 19 21 18.1046 21 17V7C21 5.89543 20.1046 5 19 5H5C3.89543 5 3 5.89543 3 7V17C3 18.1046 3.89543 19 5 19Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <?= htmlspecialchars($alumni['email']) ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <main>
        <div class="container">
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <circle cx="10" cy="10" r="9" stroke="currentColor" stroke-width="2"/>
                        <path d="M6 10L9 13L14 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <?= htmlspecialchars($_SESSION['success']) ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <circle cx="10" cy="10" r="9" stroke="currentColor" stroke-width="2"/>
                        <path d="M10 6V11M10 14V14.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <?= htmlspecialchars($_SESSION['error']) ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="profile-grid">
                <!-- Left Column -->
                <div class="profile-main">
                    
                    
                    <?php if (!empty($profile['bio'])): ?>
                    <section class="profile-section">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                            <div style="width: 4px; height: 32px; background: linear-gradient(180deg, #f59e0b 0%, #d97706 100%); border-radius: 2px;"></div>
                            <h2 class="section-title" style="margin: 0;">About</h2>
                        </div>
                        <div class="section-content">
                            <p class="bio-text" style="line-height: 1.8; font-size: 15px;"><?= nl2br(htmlspecialchars($profile['bio'])) ?></p>
                        </div>
                    </section>
                    <?php else: ?>
                    <section class="profile-section">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                            <div style="width: 4px; height: 32px; background: linear-gradient(180deg, #f59e0b 0%, #d97706 100%); border-radius: 2px;"></div>
                            <h2 class="section-title" style="margin: 0;">About</h2>
                        </div>
                        <div class="section-content">
                            <p class="bio-text" style="opacity: 0.5; font-style: italic; padding: 30px; text-align: center; background: rgba(255,255,255,0.02); border-radius: 8px;">This alumni hasn't added a bio yet.</p>
                        </div>
                    </section>
                    <?php endif; ?>

                    <?php if (!empty($careerHistory)): ?>
                    <section class="profile-section">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                            <div style="width: 4px; height: 32px; background: linear-gradient(180deg, #10b981 0%, #059669 100%); border-radius: 2px;"></div>
                            <h2 class="section-title" style="margin: 0;">Career Journey</h2>
                        </div>
                        <div class="section-content">
                            <div class="career-timeline">
                                <?php foreach ($careerHistory as $index => $career): ?>
                                <div class="career-item <?= $index === 0 ? 'current' : '' ?>">
                                    <div class="career-marker">
                                        <div class="career-dot"></div>
                                        <?php if ($index < count($careerHistory) - 1): ?>
                                        <div class="career-line"></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="career-content">
                                        <div class="career-header">
                                            <h3 class="career-title"><?= htmlspecialchars($career['job_title']) ?></h3>
                                            <?php if ($index === 0 && empty($career['end_date'])): ?>
                                            <span class="badge badge-current">Current</span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="career-company"><?= htmlspecialchars($career['company_name']) ?></p>
                                        <p class="career-dates">
                                            <?= date('M Y', strtotime($career['start_date'])) ?> - 
                                            <?= $career['end_date'] ? date('M Y', strtotime($career['end_date'])) : 'Present' ?>
                                        </p>
                                        <?php if (!empty($career['description'])): ?>
                                        <p class="career-description"><?= nl2br(htmlspecialchars($career['description'])) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
                    <?php else: ?>
                    <section class="profile-section">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                            <div style="width: 4px; height: 32px; background: linear-gradient(180deg, #10b981 0%, #059669 100%); border-radius: 2px;"></div>
                            <h2 class="section-title" style="margin: 0;">Career Journey</h2>
                        </div>
                        <div class="section-content">
                            <p style="opacity: 0.5; font-style: italic; padding: 30px; text-align: center; background: rgba(255,255,255,0.02); border-radius: 8px;">No career history added yet.</p>
                        </div>
                    </section>
                    <?php endif; ?>

                    <?php if (!empty($profile['publications_summary'])): ?>
                    <section class="profile-section">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                            <div style="width: 4px; height: 32px; background: linear-gradient(180deg, #8b5cf6 0%, #7c3aed 100%); border-radius: 2px;"></div>
                            <h2 class="section-title" style="margin: 0;">Publications & Research</h2>
                        </div>
                        <div class="section-content">
                            <div class="publications-placeholder">
                                <p class="bio-text" style="line-height: 1.8; font-size: 15px;"><?= nl2br(htmlspecialchars($profile['publications_summary'])) ?></p>
                            </div>
                        </div>
                    </section>
                    <?php endif; ?>

                </div>

                <!-- Right Column - Mentorship Request -->
                <div class="profile-sidebar">
                    <div class="mentorship-card">
                        <h2 class="mentorship-title">Request Mentorship</h2>
                        
                        <?php if ($existingRequest): ?>
                            <?php if ($existingRequest['status'] === 'pending'): ?>
                                <div class="request-status status-pending">
                                    <div class="status-icon">⏳</div>
                                    <div class="status-content">
                                        <strong>Request Pending</strong>
                                        <p>Your mentorship request is awaiting response. We'll notify you when there's an update.</p>
                                    </div>
                                </div>
                            <?php elseif ($existingRequest['status'] === 'approved'): ?>
                                <div class="request-status status-approved">
                                    <div class="status-icon">✓</div>
                                    <div class="status-content">
                                        <strong>Request Approved!</strong>
                                        <p>Your mentorship has been approved. Contact <?= htmlspecialchars($alumni['name']) ?> to begin.</p>
                                    </div>
                                </div>
                                <a href="mailto:<?= htmlspecialchars($alumni['email']) ?>" class="btn btn-primary btn-full">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                        <path d="M3 8L10.89 13.26C11.25 13.48 11.75 13.48 12.11 13.26L20 8M5 19H19C20.1046 19 21 18.1046 21 17V7C21 5.89543 20.1046 5 19 5H5C3.89543 5 3 5.89543 3 7V17C3 18.1046 3.89543 19 5 19Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                    Send Email
                                </a>
                            <?php elseif ($existingRequest['status'] === 'rejected'): ?>
                                <div class="request-status status-rejected">
                                    <div class="status-icon">✕</div>
                                    <div class="status-content">
                                        <strong>Previous Request Declined</strong>
                                        <p>Your previous request wasn't approved, but you can try again with a new message.</p>
                                    </div>
                                </div>
                                <form method="POST" action="mentorship_request.php" class="mentorship-form">
                                    <input type="hidden" name="mentor_id" value="<?= $alumni['user_id'] ?>">
                                    <label class="form-label">Your Message</label>
                                    <textarea name="message" class="form-textarea" placeholder="Share what you hope to learn and why you'd like to connect with <?= htmlspecialchars($alumni['name']) ?>..." rows="6" required></textarea>
                                    <button type="submit" class="btn btn-primary btn-full">Send New Request</button>
                                </form>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="mentorship-description">Connect with <?= htmlspecialchars($alumni['name']) ?> to gain insights from their professional journey and expertise.</p>
                            <form method="POST" action="mentorship_request.php" class="mentorship-form">
                                <input type="hidden" name="mentor_id" value="<?= $alumni['user_id'] ?>">
                                <label class="form-label">Your Message</label>
                                <textarea name="message" class="form-textarea" placeholder="Introduce yourself and describe what you hope to learn from this mentorship..." rows="6" required></textarea>
                                <button type="submit" class="btn btn-primary btn-full">Send Request</button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($profile['education']) || !empty($profile['contact_info'])): ?>
                    <div class="info-card">
                        <?php if (!empty($profile['education'])): ?>
                        <div class="info-item">
                            <h3 class="info-title">Education</h3>
                            <p class="info-content"><?= nl2br(htmlspecialchars($profile['education'])) ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($profile['contact_info'])): ?>
                        <div class="info-item">
                            <h3 class="info-title">Additional Contact</h3>
                            <p class="info-content"><?= nl2br(htmlspecialchars($profile['contact_info'])) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
<main>
        <div class="container">
            <h1>Alumni Search & Networking</h1>
            <p>Search for alumni and connect via messaging.</p>
            
            <form class="mt-20">
                <div class="search">
                    <span class="search-icon material-symbols-outlined">search</span>
                    <input class="search-input" type="search" placeholder="Search for alumni by name, field, or company...">
                </div>
            </form>
        </div>

       

        <!-- Chat Layout (initially hidden) -->
        <div class="main-chat-layout" id="mainLayout" style="display: none;">
            <div class="contacts" id="contact_list">
                <h3>Chats</h3>
                <div class="contact active" data-user="yoshenan" onclick="selectContact(this)">
                    <span class="avatar">YS</span>
                    <div class="contact-info">
                        <span class="name">Yoshenan</span>
                        <span class="notification">Online</span>
                    </div>
                </div>

                <div class="contact" data-user="chris" onclick="selectContact(this)">
                    <span class="avatar">CE</span>
                    <div class="contact-info">
                        <span class="name">Chris Evans</span>
                        <span class="notification">2 new messages</span>
                    </div>
                </div>

                <div class="contact" data-user="emily" onclick="selectContact(this)">
                    <span class="avatar">EC</span>
                    <div class="contact-info">
                        <span class="name">Emily Carter</span>
                        <span class="notification">Seen 5m ago</span>
                    </div>
                </div>
            </div>

            <div class="chat-wrapper" id="chatWrapper">
                <div id="chat-box"></div>
                <div class="input-section">
                    <div class="input-container">
                        <div class="input-row">
                            <input type="text" id="user-input" placeholder="Type your message">
                            <button class="send-btn" onclick="sendMessage('sent')">Send</button>
                        </div>
                        <hr style="border: 0.5px solid var(--border); width: 100%;">
                    </div>
                </div>
            </div>
        </div>
    </main>

    </main>

    <footer>
        <p>&copy; 2026 Alumni Engagement System</p>
    </footer>

</body>
</html>