/**
 * Alumni Chat System Logic
 * Fixed: Functions must be global to work with onclick attributes
 */

// Global variables
let currentConversationId = null;
let messageCheckInterval = null;

// Initialize on Load
document.addEventListener('DOMContentLoaded', function() {
    loadConversations();
    
    // Auto-refresh messages every 5 seconds
    messageCheckInterval = setInterval(() => {
        if (currentConversationId) {
            loadMessages(currentConversationId, true);
        }
    }, 5000);
});

// Toggle chat window
document.addEventListener('click', function(e) {
    if (e.target && e.target.id === 'chatBtn') {
        const mainLayout = document.getElementById("mainLayout");
        const isHidden = mainLayout.style.display === "none" || mainLayout.style.display === "";
        mainLayout.style.display = isHidden ? "flex" : "none";
        if (isHidden) loadConversations();
    }
    
    // Handle New Chat Button
    if (e.target && e.target.id === 'newChatBtn') {
        document.getElementById('newChatModal').style.display = 'flex';
        searchUsers();
        setTimeout(() => document.getElementById('userSearch').focus(), 100);
    }
    
    // Close modal on outside click
    if (e.target && e.target.id === 'newChatModal') {
        closeNewChatModal();
    }
});

// ESC key closes modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('newChatModal');
        if (modal && modal.style.display === 'flex') {
            closeNewChatModal();
        }
    }
});

// Load conversations list
function loadConversations() {
    fetch('chat_api.php?action=get_conversations')
        .then(response => {
            if (!response.ok) throw new Error('Server error');
            return response.json();
        })
        .then(data => {
            const list = document.getElementById('conversationList');
            if (data.success && data.conversations.length > 0) {
                list.innerHTML = data.conversations.map(conv => `
                    <div class="conversation-item" data-id="${conv.conversation_id}" onclick="selectConversation('${conv.conversation_id}')">
                        <div class="conversation-name">${escapeHtml(conv.conversation_name)}</div>
                        <div class="conversation-preview">${escapeHtml(conv.last_message || 'No messages yet')}</div>
                    </div>
                `).join('');
            } else {
                list.innerHTML = '<p style="padding: 20px; text-align: center; color: #666; font-size: 13px;">No conversations yet.<br>Click "New Chat" to start!</p>';
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            document.getElementById('conversationList').innerHTML = '<p style="padding: 20px; text-align: center; color: red; font-size: 12px;">Error loading conversations</p>';
        });
}

// Select a conversation to chat
function selectConversation(conversationId) {
    currentConversationId = conversationId;
    
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.classList.remove('active');
    });
    const selected = document.querySelector(`[data-id="${conversationId}"]`);
    if (selected) selected.classList.add('active');
    
    document.getElementById('inputArea').style.display = 'block';
    loadMessages(conversationId);
}

// Fetch messages for a specific chat
function loadMessages(conversationId, silent = false) {
    fetch(`chat_api.php?action=get_messages&conversation_id=${conversationId}`)
        .then(response => {
            if (!response.ok) throw new Error('Server returned error');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const chatBox = document.getElementById('chat-box');
                const wasAtBottom = chatBox.scrollHeight - chatBox.scrollTop <= chatBox.clientHeight + 50;
                
                let htmlContent = '';
                if (data.messages && data.messages.length > 0) {
                    data.messages.forEach(msg => {
                        const isMine = msg.is_mine === true || msg.is_mine === 1;
                        htmlContent += `
                            <div class="chat-message ${isMine ? 'sent' : 'received'}">
                                ${!isMine ? `<strong>${escapeHtml(msg.sender_name)}</strong><br>` : ''}
                                ${escapeHtml(msg.message_text)}
                            </div>
                        `;
                    });
                    htmlContent += '<div style="clear:both"></div>';
                } else {
                    htmlContent = '<p style="text-align: center; color: #666; padding: 20px;">No messages yet</p>';
                }
                
                chatBox.innerHTML = htmlContent;
                
                if (!silent || wasAtBottom) {
                    setTimeout(() => chatBox.scrollTop = chatBox.scrollHeight, 50);
                }
            }
        })
        .catch(error => console.error('Message Load Error:', error));
}

// Send a new message
function sendMessage() {
    const input = document.getElementById('user-input');
    const message = input.value.trim();
    if (!message || !currentConversationId) return;
    
    input.disabled = true;
    fetch('chat_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=send_message&conversation_id=${currentConversationId}&message=${encodeURIComponent(message)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            loadMessages(currentConversationId);
            loadConversations();
        } else {
            alert('Failed to send: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Send error:', error);
        alert('Failed to send message');
    })
    .finally(() => {
        input.disabled = false;
        input.focus();
    });
}

// User Search Logic
function searchUsers() {
    const query = document.getElementById('userSearch').value;
    fetch(`chat_api.php?action=search_users&query=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => {
            const userList = document.getElementById('userList');
            if (data.success && data.users.length > 0) {
                userList.innerHTML = data.users.map(user => `
                    <div class="user-item">
                        <div>
                            <div style="font-weight: 600; margin-bottom: 4px;">${escapeHtml(user.name)}</div>
                            <div style="font-size: 12px; color: #666;">${escapeHtml(user.email)}</div>
                        </div>
                        <button onclick="startConversation('${user.user_id}', '${escapeHtml(user.name).replace(/'/g, "&#39;")}')">Message</button>
                    </div>
                `).join('');
            } else {
                userList.innerHTML = `<p style="text-align: center; color: #666; padding: 20px;">${query ? 'No users found' : 'Start typing to search'}</p>`;
            }
        })
        .catch(error => console.error('Search error:', error));
}

// Start a brand new conversation
function startConversation(userId, userName) {
    fetch('chat_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=create_conversation&user_id=${userId}&conversation_name=${encodeURIComponent('Chat with ' + userName)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeNewChatModal();
            loadConversations();
            setTimeout(() => selectConversation(data.conversation_id), 300);
        } else {
            alert('Failed to create conversation: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Create conversation error:', error);
        alert('Failed to create conversation');
    });
}

// Close new chat modal
function closeNewChatModal() {
    document.getElementById('newChatModal').style.display = 'none';
    document.getElementById('userSearch').value = '';
}

// Utility: Escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}