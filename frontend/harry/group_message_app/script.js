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
    authBtnText: document.getElementById('auth-btn-text'),
    authSpinner: document.getElementById('auth-spinner'),
    toggleAuth: document.getElementById('toggle-auth'),
    authError: document.getElementById('auth-error'),
    usernameInput: document.getElementById('username'),
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
    elements.authTitle.innerText = isLoginMode ? "Welcome Back" : "Create Account";
    elements.authBtnText.innerText = isLoginMode ? "Login" : "Register";
    elements.toggleAuth.innerText = isLoginMode ? "Create an account" : "Already have an account? Login";
    elements.authError.classList.add('d-none');
});

elements.authForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const endpoint = isLoginMode ? '/LoginApi.php' : '/RegisterApi.php';
    
    elements.authBtnText.classList.add('d-none');
    elements.authSpinner.classList.remove('d-none');
    elements.authError.classList.add('d-none');

    try {
        const result = await apiFetch(endpoint, {
            name: elements.usernameInput.value.trim(),
            password: elements.passwordInput.value
        });
        if (result.status === 'success') {
            localStorage.setItem('api_token', result.api_token);
            localStorage.setItem('user_id', result.user_id);
            elements.passwordInput.value = '';
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
// --- CHAT DISPLAY LOGIC ---
function renderMessages(messages) {
    const currentUserId = parseInt(localStorage.getItem('user_id'));

    const messagesHtml = messages.map(msg => {
        const isMe = parseInt(msg.user_id) === currentUserId;
        
        let timeString = "Sending..."; 
        if (msg.timestamp && !msg.isTemp) {
            let dateObj;
            if (/^\d+$/.test(msg.timestamp)) {
                let ts = parseInt(msg.timestamp);
                if (ts > 0 && ts < 9999999999) ts *= 1000; 
                dateObj = new Date(ts);
            } else {
                dateObj = new Date(msg.timestamp);
            }
            if (!isNaN(dateObj.getTime()) && dateObj.getTime() > 0) {
                timeString = dateObj.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }
        }
        
        const senderName = isMe ? "You" : (msg.name || "Unknown");
        
        // STRICTLY USE msg.message_id for the edit button
        const editHtml = (isMe && !msg.isTemp) 
            ? `<div class="msg-actions"><button class="edit-btn" data-id="${msg.message_id}">Edit</button></div>` 
            : '';

        // STRICTLY USE msg.message_id for the wrapper and bubble IDs
        return `
            <div class="msg-wrapper ${isMe ? 'me' : 'them'} ${msg.isTemp ? 'temp-msg' : ''} slide-in" id="msg-wrapper-${msg.message_id}">
                <div class="msg-metadata">${senderName} • ${timeString}</div>
                <div class="msg-bubble" id="msg-bubble-${msg.message_id}">${msg.message}</div>
                ${editHtml}
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

// --- EDIT MESSAGE DIALOG LOGIC ---
elements.messageContainer.addEventListener('click', (e) => {
    if (e.target.classList.contains('edit-btn')) {
        const msgId = e.target.getAttribute('data-id');
        const bubble = document.getElementById(`msg-bubble-${msgId}`);
        
        document.getElementById('edit-msg-id').value = msgId;
        document.getElementById('edit-msg-input').value = bubble.innerText;
        document.getElementById('custom-edit-modal').classList.remove('d-none');
    }
});

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