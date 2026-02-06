// notifications-admin.js
document.addEventListener('DOMContentLoaded', function() {
    const notificationForm = document.querySelector('.form');
    const recentNotificationsTable = document.querySelector('table tbody');

    // Load recent notifications on page load
    loadRecentNotifications();

    // Handle form submission
    if (notificationForm) {
        notificationForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(notificationForm);
            const title = formData.get('notification_title');
            const body = formData.get('notification_message');
            const roleTarget = formData.get('notification_audience');

            // Validate
            if (!title || !body) {
                alert('Please fill in all fields');
                return;
            }

            // Disable submit button
            const submitBtn = notificationForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';

            try {
                const response = await fetch('send-notification.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        title: title,
                        body: body,
                        role_target: roleTarget
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Show success message
                    showMessage('✓ ' + data.message, 'success');
                    
                    // Reset form
                    notificationForm.reset();
                    
                    // Reload recent notifications
                    loadRecentNotifications();
                } else {
                    showMessage('✕ ' + data.message, 'error');
                }
            } catch (error) {
                showMessage('✕ Error sending notification: ' + error.message, 'error');
            } finally {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    }

    // Function to load recent notifications
    async function loadRecentNotifications() {
        try {
            const response = await fetch('get-notifications.php');
            const data = await response.json();

            if (data.success && data.notifications) {
                displayNotifications(data.notifications);
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    }

    // Function to display notifications in table
    function displayNotifications(notifications) {
        if (!recentNotificationsTable) return;

        if (notifications.length === 0) {
            recentNotificationsTable.innerHTML = `
                <tr>
                    <td colspan="3" style="text-align: center; color: var(--text-secondary);">
                        No notifications sent yet
                    </td>
                </tr>
            `;
            return;
        }

        recentNotificationsTable.innerHTML = notifications.map(notif => `
            <tr>
                <td>${escapeHtml(notif.title)}</td>
                <td>${escapeHtml(notif.audience)}</td>
                <td>${escapeHtml(notif.date)}</td>
            </tr>
        `).join('');
    }

    // Function to show messages
    function showMessage(message, type) {
        // Remove existing alerts
        const existingAlerts = document.querySelectorAll('.alert');
        existingAlerts.forEach(alert => alert.remove());

        // Create new alert
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.textContent = message;

        const container = document.createElement('div');
        container.className = 'container';
        container.appendChild(alertDiv);

        // Insert at the top of main
        const main = document.querySelector('main');
        main.insertBefore(container, main.firstChild);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            container.remove();
        }, 5000);
    }

    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});