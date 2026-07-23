/**
 * GroupW Messenger - Final Complete UI Logic
 */

const BASE_URL = 'http://localhost:8000/api';
let pollingInterval = null;
let lastMessagesState = ""; // Prevents the 5-second blinking

// --- DOM ELEMENTS ---
const elements = {
    authView: document.getElementById('auth-view'),
    chatView: document.getElementById('chat-view'),
    
    authForm: document.getElementById('auth-form'),
    authTitle: document.getElementById('auth-title'),
    authSubtitle: document.getElementById('auth-subtitle'), // Fixed subtitle reference
    authBtnText: document.getElementById('auth-btn-text'),
    authSpinner: document.getElementById('auth-spinner'),
    toggleAuth: document.getElementById('toggle-auth'),
    authError: document.getElementById('auth-error'),
    
    usernameInput: document.getElementById('username'),
    emailGroup: document.getElementById('email-group'), // Fixed email field mapping
    emailInput: document.getElementById('email'),       // Fixed email field mapping
    passwordInput: document.getElementById('password'),
    
    messageContainer: document.getElementById('message-container'),
    chatForm: document.getElementById('chat-form'),
    messageInput: document.getElementById('message-input'),
    sendBtnText: document.getElementById('send-btn-text'),
    sendSpinner: document.getElementById('send-spinner'),
    
    logoutBtn: document.getElementById('logout-btn'),
    logoutSpinner: document.getElementById('logout-spinner')
};

// --- API HELPER ---
async function apiFetch(endpoint, data = null, method = 'POST') {
    const config = { method, headers: { 'Content-Type': 'application/x-www-form-urlencoded' } };
    if (data) config.body = new URLSearchParams(data).toString();
    const response = await fetch(`${BASE_URL}${endpoint}`, config);
    return await response.json();
}

// --- INITIALIZATION ---
document.addEventListener('DOMContentLoaded', () => {
    if (localStorage.getItem('api_token')) showChatView();
});

// --- AUTH LOGIC ---
let isLoginMode = true;

elements.toggleAuth.addEventListener('click', (e) => {
    e.preventDefault();
    isLoginMode = !isLoginMode;
    
    // Update Text
    elements.authTitle.innerText = isLoginMode ? "Welcome Back" : "Create Account";
    elements.authSubtitle.innerText = isLoginMode ? "Sign in to GroupW to continue" : "Register a new account";
    elements.authBtnText.innerText = isLoginMode ? "Login" : "Register";
    elements.toggleAuth.innerText = isLoginMode ? "Create an account" : "Already have an account? Login";
    elements.authError.classList.add('d-none');
    
    // Toggle Email Field Visibility & Requirement
    if (isLoginMode) {
        elements.emailGroup.classList.add('d-none');
        elements.emailInput.required = false;
    } else {
        elements.emailGroup.classList.remove('d-none');
        elements.emailInput.required = true;
    }
});

elements.authForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const endpoint = isLoginMode ? '/LoginApi.php' : '/RegisterApi.php';
    
    elements.authBtnText.classList.add('d-none');
    elements.authSpinner.classList.remove('d-none');
    elements.authError.classList.add('d-none');

    try {
        // Build Payload
        const payload = {
            name: elements.usernameInput.value.trim(),
            password: elements.passwordInput.value
        };
        
        if (!isLoginMode) {
            payload.email = elements.emailInput.value.trim();
        }

        const result = await apiFetch(endpoint, payload);
        
        if (result.status === 'success') {
            localStorage.setItem('api_token', result.api_token);
            localStorage.setItem('user_id', result.user_id);
            elements.passwordInput.value = '';
            elements.emailInput.value = '';
            showChatView();
        } else {
            throw new Error(result.message || "Authentication failed");
        }
    } catch (err) {
        elements.authError.innerText = err.message;
        elements.authError.classList.remove('d-none');
    } finally {
        elements.authBtnText.classList.remove('d-none');
        elements.authSpinner.classList.add('d-none');
    }
});

// --- LOGOUT LOGIC ---
elements.logoutBtn.addEventListener('click', () => {
    elements.logoutSpinner.classList.remove('d-none');
    setTimeout(() => {
        localStorage.removeItem('api_token');
        localStorage.removeItem('user_id');
        if (pollingInterval) clearInterval(pollingInterval);
        lastMessagesState = "";
        elements.messageContainer.innerHTML = '';
        elements.chatView.classList.add('d-none');
        elements.authView.classList.remove('d-none');
        elements.logoutSpinner.classList.add('d-none');
    }, 400);
});

// --- CHAT DISPLAY LOGIC ---
function renderMessages(messages) {
    const currentUserId = parseInt(localStorage.getItem('user_id'));

    const messagesHtml = messages.map(msg => {
        const isMe = parseInt(msg.user_id) === currentUserId;
        
        let timeString = "Sending..."; 
        if (msg.timestamp && !msg.isTemp) {
            let dateObj = /^\d+$/.test(msg.timestamp) ? new Date(parseInt(msg.timestamp) * 1000) : new Date(msg.timestamp);
            if (!isNaN(dateObj.getTime())) timeString = dateObj.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }
        
        const senderName = isMe ? "You" : (msg.name || "Unknown");
        
        // Render Action Buttons (React + Edit)
        const reactBtn = !msg.isTemp ? `<button class="edit-btn open-react-btn" data-id="${msg.message_id}">😀</button>` : '';
        const editBtn = (isMe && !msg.isTemp) ? `<button class="edit-btn" data-id="${msg.message_id}">Edit</button>` : '';
        const actionsHtml = `<div class="msg-actions">${reactBtn}${editBtn}</div>`;

        // Render Reactions
        let reactionsHtml = '';
        if (msg.reactions && msg.reactions.length > 0) {
            const counts = {};
            msg.reactions.forEach(r => {
                if (!counts[r.emoji]) counts[r.emoji] = { count: 0, iReacted: false, users: [] };
                counts[r.emoji].count++;
                counts[r.emoji].users.push(r.name);
                if (parseInt(r.user_id) === currentUserId) counts[r.emoji].iReacted = true;
            });

            const badges = Object.entries(counts).map(([emoji, data]) => {
                const activeClass = data.iReacted ? 'active-reaction' : '';
                return `<span class="reaction-badge ${activeClass} toggle-react-btn" data-id="${msg.message_id}" data-emoji="${emoji}" title="${data.users.join(', ')}">${emoji} ${data.count}</span>`;
            }).join('');
            reactionsHtml = `<div class="reaction-bar ${isMe ? 'me' : 'them'}">${badges}</div>`;
        }

        return `
            <div class="msg-wrapper ${isMe ? 'me' : 'them'} ${msg.isTemp ? 'temp-msg' : ''} slide-in" id="msg-wrapper-${msg.message_id}">
                <div class="msg-metadata">${senderName} • ${timeString}</div>
                <div class="msg-bubble" id="msg-bubble-${msg.message_id}">${msg.message}</div>
                ${reactionsHtml}
                ${actionsHtml}
            </div>
        `;
    }).join('');

    elements.messageContainer.innerHTML = messagesHtml;
}

async function fetchMessages(forceScroll = false) {
    try {
        const response = await fetch(`${BASE_URL}/GetMessagesApi.php`);
        const result = await response.json();
        
        if (result.status === 'success') {
            const currentState = JSON.stringify(result.data);
            if (currentState === lastMessagesState && !forceScroll) return; 
            lastMessagesState = currentState;

            const isAtBottom = elements.messageContainer.scrollHeight - elements.messageContainer.scrollTop <= elements.messageContainer.clientHeight + 50;
            
            renderMessages(result.data);
            if (forceScroll || isAtBottom) {
                elements.messageContainer.scrollTop = elements.messageContainer.scrollHeight;
            }
        }
    } catch (err) { console.error("Fetch failed:", err); }
}

// --- OPTIMISTIC SEND LOGIC ---
elements.chatForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const message = elements.messageInput.value.trim();
    if (!message) return;

    elements.messageInput.value = '';
    if (pollingInterval) clearInterval(pollingInterval);

    const tempHtml = `
        <div class="msg-wrapper me temp-msg slide-in">
            <div class="msg-metadata">You • Sending...</div>
            <div class="msg-bubble">${message}</div>
        </div>
    `;
    elements.messageContainer.insertAdjacentHTML('beforeend', tempHtml);
    elements.messageContainer.scrollTop = elements.messageContainer.scrollHeight;

    try {
        await apiFetch('/SendMessageApi.php', {
            api_token: localStorage.getItem('api_token'),
            message: message
        });
        await fetchMessages(true); 
    } catch (err) {
        alert("Failed to send message.");
        await fetchMessages(true);
    } finally {
        pollingInterval = setInterval(() => fetchMessages(false), 5000); 
    }
});

// --- REACTION LOGIC ---
// --- REACTION LOGIC ---
let targetReactMessageId = null;
const emojiPicker = document.getElementById('emoji-picker');

// 1. Send the reaction to the backend with Optimistic UI (No Blinking)
async function sendReaction(msgId, emoji, clickedBtn = null) {
    emojiPicker.classList.add('d-none'); // Hide menu instantly
    
    // OPTIMISTIC UI: Instantly change the button's look before the database replies
    if (clickedBtn && clickedBtn.classList.contains('reaction-badge')) {
        const isActive = clickedBtn.classList.contains('active-reaction');
        let currentCount = parseInt(clickedBtn.innerText.replace(/[^0-9]/g, '')) || 0;
        
        if (isActive) {
            clickedBtn.classList.remove('active-reaction');
            clickedBtn.innerText = `${emoji} ${Math.max(0, currentCount - 1)}`;
        } else {
            clickedBtn.classList.add('active-reaction');
            clickedBtn.innerText = `${emoji} ${currentCount + 1}`;
        }
    }

    try {
        await apiFetch('/ReactMessageApi.php', {
            api_token: localStorage.getItem('api_token'),
            message_id: msgId,
            emoji: emoji
        });
        // We removed the forced refresh! The background polling will sync it naturally now.
    } catch (err) {
        console.error("Failed to react");
    }
}

// Handle all clicks inside the message container
elements.messageContainer.addEventListener('click', (e) => {
    // 1. Open the Emoji Picker Menu
    if (e.target.classList.contains('open-react-btn')) {
        targetReactMessageId = e.target.getAttribute('data-id');
        
        const rect = e.target.getBoundingClientRect();
        emojiPicker.style.top = `${rect.top - 45}px`;
        emojiPicker.style.left = `${Math.max(10, rect.left - 50)}px`;
        emojiPicker.classList.remove('d-none');
    }
    
    // 2. Quick-Toggle an existing reaction badge (Pass the button element to the function!)
    if (e.target.classList.contains('toggle-react-btn')) {
        const msgId = e.target.getAttribute('data-id');
        const emoji = e.target.getAttribute('data-emoji');
        sendReaction(msgId, emoji, e.target);
    }
    
    // 3. Open Edit Modal
    if (e.target.classList.contains('edit-btn') && !e.target.classList.contains('open-react-btn')) {
        const msgId = e.target.getAttribute('data-id');
        const bubble = document.getElementById(`msg-bubble-${msgId}`);
        
        document.getElementById('edit-msg-id').value = msgId;
        document.getElementById('edit-msg-input').value = bubble.innerText;
        document.getElementById('custom-edit-modal').classList.remove('d-none');
    }
});

// Handle selecting a NEW emoji from the menu
document.getElementById('emoji-picker').addEventListener('click', (e) => {
    if (e.target.classList.contains('emoji-option') && targetReactMessageId) {
        sendReaction(targetReactMessageId, e.target.innerText, null);
        
        // Force a quiet background fetch so the new badge renders smoothly
        setTimeout(() => fetchMessages(false), 500); 
    }
});

// Hide menu if clicking anywhere else
document.addEventListener('click', (e) => {
    if (!e.target.classList.contains('open-react-btn') && !e.target.closest('#emoji-picker')) {
        emojiPicker.classList.add('d-none');
    }
});

// --- EDIT MESSAGE DIALOG LOGIC ---
const closeModal = () => {
    document.getElementById('custom-edit-modal').classList.add('d-none');
    document.getElementById('edit-msg-input').value = '';
};
document.getElementById('close-modal-btn').addEventListener('click', closeModal);
document.getElementById('close-modal-overlay').addEventListener('click', closeModal);

document.getElementById('save-modal-btn').addEventListener('click', async (e) => {
    const msgId = document.getElementById('edit-msg-id').value;
    const newText = document.getElementById('edit-msg-input').value.trim();
    const btn = e.target;
    
    if (!newText) return;

    btn.innerText = "Saving...";
    btn.disabled = true;

    try {
        const res = await apiFetch('/EditMessageApi.php', {
            api_token: localStorage.getItem('api_token'),
            message_id: msgId,
            new_message: newText
        });

        if (res.status === 'success') {
            closeModal();
            lastMessagesState = ""; 
            fetchMessages(false);
        } else {
            alert(res.message);
        }
    } catch (err) {
        alert("Error updating message.");
    } finally {
        btn.innerText = "Save Changes";
        btn.disabled = false;
    }
});

// --- UI HELPERS ---
function showChatView() {
    elements.authView.classList.add('d-none');
    elements.chatView.classList.remove('d-none');
    elements.messageContainer.innerHTML = '<div class="w-100 h-100 d-flex justify-content-center align-items-center text-primary"><div class="spinner-border" role="status"></div></div>';
    fetchMessages(true);
    if (pollingInterval) clearInterval(pollingInterval);
    pollingInterval = setInterval(() => fetchMessages(false), 5000); 
}