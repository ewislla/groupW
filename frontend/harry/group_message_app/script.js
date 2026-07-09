/**
 * GroupW Messenger - Final Premium Logic
 */

const BASE_URL = 'http://localhost:8000/api';
let pollingInterval = null;

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
    fetchLoader: document.getElementById('fetch-loader'),
    
    logoutBtn: document.getElementById('logout-btn'),
    logoutSpinner: document.getElementById('logout-spinner')
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
    if (localStorage.getItem('api_token')) {
        showChatView();
    }
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
        elements.messageContainer.innerHTML = '';
        
        elements.chatView.classList.add('d-none');
        elements.authView.classList.remove('d-none');
        elements.logoutSpinner.classList.add('d-none');
    }, 400);
});

// --- CHAT LOGIC ---
function renderMessages(messages) {
    const currentUserId = parseInt(localStorage.getItem('user_id'));

    const messagesHtml = messages.map(msg => {
        const isMe = parseInt(msg.user_id) === currentUserId;
        
        // Smart Time Parser
        let timeString = "Old Message"; 
        
        if (msg.timestamp) {
            let dateObj;
            
            // Check if the database sent a pure number string (e.g., "1720524451")
            if (/^\d+$/.test(msg.timestamp)) {
                let ts = parseInt(msg.timestamp);
                if (ts > 0 && ts < 9999999999) ts *= 1000; // Convert valid seconds to milliseconds
                dateObj = new Date(ts);
            } else {
                // The database sent a standard date string (e.g., "2026-07-09 14:30:00")
                dateObj = new Date(msg.timestamp);
            }

            // Ensure the date is valid before trying to format it
            if (!isNaN(dateObj.getTime()) && dateObj.getTime() > 0) {
                timeString = dateObj.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }
        }
        
        const senderName = isMe ? "You" : (msg.name || "Unknown");

        return `
            <div class="msg-wrapper ${isMe ? 'me' : 'them'} slide-in">
                <div class="msg-metadata">${senderName} • ${timeString}</div>
                <div class="msg-bubble">${msg.message}</div>
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
            const isAtBottom = elements.messageContainer.scrollHeight - elements.messageContainer.scrollTop <= elements.messageContainer.clientHeight + 50;
            
            renderMessages(result.data);
            
            if (forceScroll || isAtBottom) {
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

    elements.sendBtnText.classList.add('d-none');
    elements.sendSpinner.classList.remove('d-none');
    elements.messageInput.disabled = true;

    try {
        await apiFetch('/SendMessageApi.php', {
            api_token: localStorage.getItem('api_token'),
            message: message
        });

        elements.messageInput.value = '';
        fetchMessages(true); 
    } catch (err) {
        alert("Failed to send message. Please check your connection.");
    } finally {
        elements.sendBtnText.classList.remove('d-none');
        elements.sendSpinner.classList.add('d-none');
        elements.messageInput.disabled = false;
        elements.messageInput.focus();
    }
});

// --- UI HELPERS ---
function showChatView() {
    elements.authView.classList.add('d-none');
    elements.chatView.classList.remove('d-none');
    
    elements.messageContainer.innerHTML = '<div class="w-100 h-100 d-flex justify-content-center align-items-center text-primary"><div class="spinner-border" role="status"></div></div>';

    fetchMessages(true);

    if (pollingInterval) clearInterval(pollingInterval);
    // Adjusted polling to 5 seconds to reduce server load
    pollingInterval = setInterval(() => fetchMessages(false), 5000); 
}