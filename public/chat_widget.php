<!-- Floating Chat Button -->
<button id="chatBtn" style="
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: var(--accent);
    color: white;
    border: none;
    font-size: 28px;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(0,0,0,0.2);
    z-index: 1000;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
">💬</button>

<!-- Chat Window -->
<div id="mainLayout" style="
    display: none;
    position: fixed;
    bottom: 90px;
    right: 20px;
    width: 90%;
    max-width: 420px;
    height: 80vh;
    max-height: 600px;
    background: var(--bg-container);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: 0 12px 40px rgba(0,0,0,0.15);
    z-index: 999;
    flex-direction: column;
    overflow: hidden;
">
    <!-- Chat Header -->
    <div style="
        background: var(--accent);
        padding: 16px 20px;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    ">
        <h3 style="margin: 0; color: white; font-size: 18px; font-weight: 600;">Messages</h3>
        <button id="newChatBtn" style="
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
        ">+ New Chat</button>
    </div>

    <!-- Chat Container -->
    <div style="display: flex; flex: 1; overflow: hidden;">
        <!-- Sidebar - Conversations List -->
        <div id="conversationList" style="
            width: 35%;
            background: var(--bg-input);
            border-right: 1px solid var(--border);
            overflow-y: auto;
        ">
            <!-- Conversations will be loaded here -->
        </div>

        <!-- Chat Area -->
        <div style="
            flex: 1;
            display: flex;
            flex-direction: column;
            background: var(--bg-container);
        ">
            <!-- Messages Container -->
            <div id="chat-box" style="
                flex: 1;
                padding: 20px 16px;
                overflow-y: auto;
                display: flex;
                flex-direction: column;
                gap: 8px;
            ">
                <p style="text-align: center; color: var(--text-secondary); padding: 40px 20px; font-size: 14px;">
                    👋 Select a conversation to start messaging
                </p>
            </div>

            <!-- Input Area -->
            <div id="inputArea" style="
                padding: 16px;
                background: var(--bg-container);
                border-top: 1px solid var(--border);
                display: none;
            ">
                <div style="display: flex; gap: 10px; align-items: flex-end;">
                    <input 
                        type="text" 
                        id="user-input" 
                        placeholder="Type a message..."
                        style="
                            flex: 1;
                            padding: 12px 16px;
                            border: 1px solid var(--border);
                            border-radius: 24px;
                            font-size: 14px;
                            outline: none;
                            transition: border-color 0.2s;
                        "
                        onkeypress="if(event.key==='Enter') sendMessage()"
                        onfocus="this.style.borderColor='var(--accent)'"
                        onblur="this.style.borderColor='var(--border)'"
                    >
                    <button onclick="sendMessage()" style="
                        background: var(--accent);
                        color: white;
                        border: none;
                        padding: 12px 24px;
                        border-radius: 24px;
                        font-size: 14px;
                        font-weight: 600;
                        cursor: pointer;
                        transition: all 0.2s;
                    ">Send</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Chat Modal -->
<div id="newChatModal" style="
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    z-index: 1001;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(4px);
">
    <div class="container" style="
        width: 90%;
        max-width: 500px;
        max-height: 80vh;
        overflow-y: auto;
        background: var(--bg-container);
        border-radius: 16px;
        padding: 24px;
    ">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h2 style="margin: 0; color: var(--text-primary); font-size: 20px;">Start New Conversation</h2>
            <button onclick="closeNewChatModal()" style="
                background: var(--bg-input);
                color: var(--text-primary);
                border: 1px solid var(--border);
                padding: 8px 16px;
                border-radius: 8px;
                cursor: pointer;
                font-size: 14px;
            ">Close</button>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500; color: var(--text-primary);">Search Users</label>
            <input 
                type="text" 
                id="userSearch" 
                placeholder="Search by name..."
                oninput="searchUsers()"
                style="
                    width: 100%;
                    padding: 12px 16px;
                    border: 1px solid var(--border);
                    border-radius: 8px;
                    font-size: 14px;
                "
            >
        </div>

        <div id="userList">
            <!-- Users will be loaded here -->
        </div>
    </div>
</div>

<style>
/* Scrollbar Styling */
#chat-box::-webkit-scrollbar,
#conversationList::-webkit-scrollbar {
    width: 6px;
}

#chat-box::-webkit-scrollbar-track,
#conversationList::-webkit-scrollbar-track {
    background: transparent;
}

#chat-box::-webkit-scrollbar-thumb,
#conversationList::-webkit-scrollbar-thumb {
    background: var(--border);
    border-radius: 3px;
}

#chat-box::-webkit-scrollbar-thumb:hover,
#conversationList::-webkit-scrollbar-thumb:hover {
    background: var(--text-secondary);
}

/* Chat Messages - Improved Layout */
.chat-message {
    padding: 10px 14px;
    border-radius: 16px;
    margin-bottom: 4px;
    max-width: 75%;
    word-wrap: break-word;
    font-size: 14px;
    line-height: 1.5;
    position: relative;
    clear: both;
}

/* Sent Messages - Right Aligned */
.chat-message.sent {
    background: var(--accent);
    color: white;
    margin-left: auto;
    float: right;
    clear: both;
    border-bottom-right-radius: 4px;
}

/* Received Messages - Left Aligned */
.chat-message.received {
    background: var(--bg-input);
    color: var(--text-primary);
    margin-right: auto;
    float: left;
    clear: both;
    border-bottom-left-radius: 4px;
}

.chat-message.received strong {
    font-size: 11px;
    color: var(--accent);
    display: block;
    margin-bottom: 4px;
    font-weight: 600;
}

/* Conversation List Items */
.conversation-item {
    padding: 14px 12px;
    border-bottom: 1px solid var(--border);
    cursor: pointer;
    transition: all 0.2s;
}

.conversation-item:hover {
    background: var(--bg-hover);
}

.conversation-item.active {
    background: var(--bg-container);
    border-left: 3px solid var(--accent);
}

.conversation-name {
    font-weight: 600;
    color: var(--text-primary);
    font-size: 14px;
    margin-bottom: 4px;
}

.conversation-preview {
    font-size: 12px;
    color: var(--text-secondary);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* User List Items */
.user-item {
    padding: 14px;
    border: 1px solid var(--border);
    border-radius: 10px;
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--bg-input);
    transition: all 0.2s;
}

.user-item:hover {
    background: var(--bg-hover);
    border-color: var(--accent);
    transform: translateY(-1px);
}

.user-item button {
    background: var(--accent);
    color: white;
    border: none;
    padding: 8px 20px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.user-item button:hover {
    opacity: 0.9;
    transform: scale(1.05);
}

/* Button Hover Effects */
#chatBtn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(0,0,0,0.3);
}

#newChatBtn:hover {
    background: rgba(255,255,255,0.3);
}

button[onclick="sendMessage()"]:hover {
    opacity: 0.9;
    transform: scale(1.02);
}

/* Responsive Design */
@media (max-width: 500px) {
    #mainLayout {
        width: 95%;
        max-width: none;
        right: 2.5%;
        bottom: 80px;
        height: 85vh;
    }
    
    #chatBtn {
        bottom: 15px;
        right: 15px;
        width: 56px;
        height: 56px;
    }
    
    #conversationList {
        width: 0;
        overflow: hidden;
    }
    
    .conversation-item.active ~ #conversationList {
        width: 35%;
    }
}
</style>

<script src="chat.js"></script>