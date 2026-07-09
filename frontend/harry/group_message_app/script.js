/**
 * GroupW Messenger - Final Corrected Version
 */

const BASE_URL = 'http://localhost:8000/api';
let pollingInterval = null;

// --- DOM ELEMENTS ---
const elements = {
    authView: document.getElementById('auth-view'),
    chatView: document.getElementById('chat-view'),
    authForm: document.getElementById('auth-form'),
    authTitle: document.getElementById('auth-title'),
    authBtn: document.getElementById('auth-btn'),
    toggleAuth: document.getElementById('toggle-auth'),
    authError: document.getElementById('auth-error'),
    messageContainer: document.getElementById('message-container'),
    chatForm: document.getElementById('chat-form'),
    messageInput: document.getElementById('message-input')
};

// --- API HELPER ---
async function apiFetch(endpoint, data = null, method = 'POST') {
    const config = {
        method,
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    };
    if (data) config.body = new URLSearchParams(data).toString();

    const response = await fetch(`${BASE_URL}${endpoint}`, config);
    return await response.json();
}

// --- INITIALIZATION ---
document.addEventListener('DOMContentLoaded', () => {
    // If the user already has a token, jump straight to chat
    if (localStorage.getItem('api_token')) {
        showChatView();
    }
});

// --- AUTH LOGIC ---
let isLoginMode = true;

elements.toggleAuth.addEventListener('click', (e) => {
    e.preventDefault();
    isLoginMode = !isLoginMode;
    elements.authTitle.innerText = isLoginMode ? "Login" : "Register";
    elements.authBtn.innerText = isLoginMode ? "Login" : "Register";
});

elements.authForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const endpoint = isLoginMode ? '/LoginApi.php' : '/RegisterApi.php';

    try {
        const result = await apiFetch(endpoint, {
            name: document.getElementById('username').value,
            password: document.getElementById('password').value
        });

        if (result.status === 'success') {
            // Persist the token and the user_id
            localStorage.setItem('api_token', result.api_token);
            localStorage.setItem('user_id', result.user_id);

            showChatView();
        } else {
            throw new Error(result.message || "Authentication failed");
        }
    } catch (err) {
        elements.authError.innerText = err.message;
        elements.authError.classList.remove('d-none');
    }
});

// --- CHAT LOGIC ---
function renderMessages(messages) {
    const currentUserId = parseInt(localStorage.getItem('user_id'));

    elements.messageContainer.innerHTML = messages.map(msg => {
        // Strictly compare using user_id
        const isMe = parseInt(msg.user_id) === currentUserId;
        const time = new Date(msg.timestamp * 1000).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        return `
            <div class="msg-wrapper ${isMe ? 'me' : 'them'}">
                <div class="avatar shadow-sm">${isMe ? 'Y' : 'U'}</div>
                <div class="msg-content">
                    <div class="msg-meta">${isMe ? 'You' : 'User #' + msg.user_id} • ${time}</div>
                    <div class="msg-bubble">${msg.message}</div>
                </div>
            </div>
        `;
    }).join('');
}

async function fetchMessages(forceScroll = false) {
    try {
        const response = await fetch(`${BASE_URL}/GetMessagesApi.php`);
        const result = await response.json();
        if (result.status === 'success') {
            renderMessages(result.data);
            if (forceScroll) {
                elements.messageContainer.scrollTop = elements.messageContainer.scrollHeight;
            }
        }
    } catch (err) {
        console.error("Fetch failed:", err);
    }
}

elements.chatForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const message = elements.messageInput.value.trim();
    if (!message) return;

    try {
        await apiFetch('/SendMessageApi.php', {
            api_token: localStorage.getItem('api_token'),
            message: message
        });

        elements.messageInput.value = '';
        fetchMessages(true);
    } catch (err) {
        alert("Failed to send message.");
    }
});

// --- UI HELPERS ---
function showChatView() {
    elements.authView.classList.add('d-none');
    elements.chatView.classList.remove('d-none');

    fetchMessages(true);

    if (pollingInterval) clearInterval(pollingInterval);
    pollingInterval = setInterval(() => fetchMessages(false), 5000);
}