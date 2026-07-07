<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpazaSa — Chat</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .chat-page { min-height: 100vh; background: var(--cream); padding: 20px; }
        .chat-container { max-width: 700px; margin: 0 auto; background: var(--white); border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow); }
        .chat-header { background: var(--teal); color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .chat-header .back { color: white; text-decoration: none; font-weight: 700; }
        .chat-messages { height: 400px; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; }
        .chat-msg { padding: 10px 14px; border-radius: 12px; max-width: 75%; margin-bottom: 10px; font-size: 14px; }
        .chat-msg.sent { background: var(--teal); color: white; align-self: flex-end; }
        .chat-msg.received { background: #f1f1f1; align-self: flex-start; }
        .chat-msg .time { font-size: 10px; opacity: 0.7; display: block; margin-top: 3px; }
        .chat-input { padding: 12px 16px; border-top: 1px solid #ddd; display: flex; gap: 10px; background: var(--white); }
        .chat-input input { flex: 1; padding: 10px 16px; border: 1.5px solid #ddd; border-radius: var(--radius-pill); font-size: 14px; outline: none; }
        .chat-input input:focus { border-color: var(--teal); }
        .chat-input button { padding: 10px 24px; background: var(--teal); color: white; border: none; border-radius: var(--radius-pill); font-weight: 700; cursor: pointer; }
        .chat-input button:hover { background: var(--teal-dark); }
        .empty-message { text-align: center; padding: 40px; color: #888; }
    </style>
</head>
<body>

<div class="chat-page">
    <div class="chat-container">
        <div class="chat-header">
            <span><strong><?= htmlspecialchars($otherUser['full_name'] ?? $otherUser['username']) ?></strong>
            <?php if ($product): ?>
                <span style="font-size:12px;opacity:0.8;display:block;">About: <?= htmlspecialchars($product['title']) ?></span>
            <?php endif; ?>
            </span>
            <a href="index.php?action=messages" class="back">✕</a>
        </div>

        <div class="chat-messages" id="chatMessages">
            <?php if (empty($messages)): ?>
                <div class="empty-message">No messages yet. Say hello!</div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="chat-msg <?= $msg['sender_id'] == $_SESSION['user_id'] ? 'sent' : 'received' ?>">
                        <?= htmlspecialchars($msg['message_text']) ?>
                        <span class="time"><?= date('H:i', strtotime($msg['created_at'])) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="chat-input">
            <input type="text" id="chatInput" placeholder="Type a message..." onkeydown="if(event.key==='Enter') sendChat()">
            <button id="sendBtn" onclick="sendChat()">Send</button>
        </div>
    </div>
</div>

<script>
const OTHER_USER_ID = <?= json_encode($otherUserId) ?>;
const PRODUCT_ID = <?= json_encode($productId) ?>;
const SESSION_USER_ID = <?= json_encode($_SESSION['user_id'] ?? 0) ?>;
const SESSION = {
    userId: <?= json_encode($_SESSION['user_id'] ?? 0) ?>,
    userName: <?= json_encode($_SESSION['full_name'] ?? null) ?>,
    isLoggedIn: <?= json_encode(isset($_SESSION['user_id'])) ?>
};

console.log('Chat loaded: OTHER_USER_ID=', OTHER_USER_ID, 'PRODUCT_ID=', PRODUCT_ID);

function showToast(msg) {
    let t = document.getElementById('toast');
    if (!t) {
        t = document.createElement('div');
        t.id = 'toast';
        t.className = 'toast';
        document.body.appendChild(t);
    }
    t.textContent = msg;
    t.classList.add('show');
    clearTimeout(t._timeout);
    t._timeout = setTimeout(() => t.classList.remove('show'), 2800);
}
function scrollToBottom() {
    const container = document.getElementById('chatMessages');
    container.scrollTop = container.scrollHeight;
}
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
function appendMessage(message, isOwn) {
    const container = document.getElementById('chatMessages');
    const emptyMsg = container.querySelector('.empty-message');
    if (emptyMsg) emptyMsg.remove();
    const div = document.createElement('div');
    div.className = 'chat-msg ' + (isOwn ? 'sent' : 'received');
    const time = new Date(message.created_at).toLocaleTimeString();
    div.innerHTML = `${escapeHtml(message.message_text)}<span class="time">${time}</span>`;
    container.appendChild(div);
    scrollToBottom();
}
function sendChat() {
    const input = document.getElementById('chatInput');
    if (!input) {
        console.error('chatInput not found');
        return;
    }
    const message = input.value.trim();
    if (!message) {
        showToast('Please type a message.');
        return;
    }
    input.value = '';
    const sendBtn = document.getElementById('sendBtn');
    sendBtn.disabled = true;
    sendBtn.textContent = 'Sending...';

    const formData = new FormData();
    formData.append('receiver_id', OTHER_USER_ID);
    formData.append('message', message);
    if (PRODUCT_ID) formData.append('product_id', PRODUCT_ID);

    const url = 'index.php?action=send-message';
    console.log('Sending to:', url);
    console.log('Data:', { receiver_id: OTHER_USER_ID, message, product_id: PRODUCT_ID });

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        console.log('Send response:', data);
        if (data.success) {
            const container = document.getElementById('chatMessages');
            const emptyMsg = container.querySelector('.empty-message');
            if (emptyMsg) emptyMsg.remove();
            const div = document.createElement('div');
            div.className = 'chat-msg sent';
            const now = new Date();
            div.innerHTML = `${escapeHtml(message)}<span class="time">${now.toLocaleTimeString()}</span>`;
            container.appendChild(div);
            scrollToBottom();
            showToast('Message sent!');
        } else {
            showToast(data.error || 'Failed to send');
            input.value = message;
        }
    })
    .catch(err => {
        console.error('Send error:', err);
        showToast('Network error: ' + err.message);
        input.value = message;
    })
    .finally(() => {
        sendBtn.disabled = false;
        sendBtn.textContent = 'Send';
    });
}

let lastMessageId = <?= !empty($messages) ? end($messages)['message_id'] : 0 ?>;

function pollNewMessages() {
    const url = `index.php?action=get-messages&user=${OTHER_USER_ID}&last_id=${lastMessageId}`;
    fetch(url)
    .then(res => res.json())
    .then(data => {
        if (data.success && data.messages && data.messages.length > 0) {
            data.messages.forEach(msg => {
                const isOwn = msg.sender_id == SESSION_USER_ID;
                appendMessage(msg, isOwn);
                lastMessageId = msg.message_id;
            });
        }
    })
    .catch(err => {

        console.warn('Poll error:', err);
    });
}

setInterval(pollNewMessages, 3000);
setTimeout(pollNewMessages, 500);
scrollToBottom();
console.log('Chat initialized.');
</script>

<script src="assets/js/app.js"></script>
</body>
</html>
