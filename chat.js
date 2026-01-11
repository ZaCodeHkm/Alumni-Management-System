let dragging = false;
let currentUser = "yoshenan";

const chats = {
    yoshenan: [{ text: "Hello! How can I help you today?", type: "received" }],
    chris: [{ text: "Hey there! Are we meeting for the alumni event?", type: "received" }],
    emily: [{ text: "I've sent over the documents you requested.", type: "received" }]
};

function updateSidebarNotification(user, text, isTyping = false) {
    const contact = document.querySelector(`.contact[data-user="${user}"]`);
    if (contact) {
        const notifySpan = contact.querySelector('.notification');
        notifySpan.textContent = text;
        if (isTyping) {
            notifySpan.classList.add('typing');
        } else {
            notifySpan.classList.remove('typing');
            if (text === "Seen") notifySpan.classList.add('seen');
        }
    }
}

function sendMessage(type) {
    const userInput = document.getElementById('user-input');
    if (!userInput) return;

    const messageText = userInput.value.trim();
    if (messageText === '') return;

    addMessageToUI(messageText, 'sent');
    chats[currentUser].push({ text: messageText, type: 'sent' });
    userInput.value = '';
    
    updateSidebarNotification(currentUser, "Sent");

    setTimeout(() => {
        showTypingIndicator();
        updateSidebarNotification(currentUser, "Typing...", true);

        setTimeout(() => {
            hideTypingIndicator();
            const replies = ["That sounds great!", "I'll get back to you soon.", "Let's discuss this later."];
            const randomReply = replies[Math.floor(Math.random() * replies.length)];
            
            addMessageToUI(randomReply, 'received');
            chats[currentUser].push({ text: randomReply, type: 'received' });
            updateSidebarNotification(currentUser, "New message");

            setTimeout(() => {
                updateSidebarNotification(currentUser, "Seen");
            }, 3000);
        }, 2000);
    }, 1000);
}

function showTypingIndicator() {
    if (document.getElementById('typing-id')) return;
    const chatBox = document.getElementById('chat-box');
    const typingDiv = document.createElement('div');
    typingDiv.id = 'typing-id';
    typingDiv.textContent = `${currentUser} is typing...`;
    typingDiv.classList.add('typing-indicator');
    chatBox.appendChild(typingDiv);
    chatBox.scrollTop = chatBox.scrollHeight;
}

function hideTypingIndicator() {
    const indicator = document.getElementById('typing-id');
    if (indicator) indicator.remove();
}

function addMessageToUI(text, type) {
    const chatBox = document.getElementById('chat-box');
    const messageDiv = document.createElement('div');
    messageDiv.textContent = text;
    messageDiv.classList.add('chat-message', type);
    chatBox.appendChild(messageDiv);
    chatBox.scrollTop = chatBox.scrollHeight;
}

function selectContact(element) {
    document.querySelectorAll('.contact').forEach(c => c.classList.remove('active'));
    element.classList.add('active');
    currentUser = element.dataset.user;
    updateSidebarNotification(currentUser, "Online");
    loadMessages();
}

function loadMessages() {
    const chatBox = document.getElementById('chat-box');
    chatBox.innerHTML = "";
    if (chats[currentUser]) {
        chats[currentUser].forEach(msg => {
            const div = document.createElement('div');
            div.textContent = msg.text;
            div.classList.add('chat-message', msg.type);
            chatBox.appendChild(div);
        });
    }
    chatBox.scrollTop = chatBox.scrollHeight;
}

const btn = document.getElementById("chatBtn");
const mainLayout = document.getElementById("mainLayout");

btn.addEventListener("click", () => {
    const isHidden = mainLayout.style.display === "none" || mainLayout.style.display === "";
    mainLayout.style.display = isHidden ? "flex" : "none";
});

btn.onmousedown = () => dragging = true;
document.onmouseup = () => dragging = false;
document.onmousemove = (e) => {
    if (dragging) {
        btn.style.left = e.clientX + "px";
        btn.style.top = e.clientY + "px";
    }
};

loadMessages();