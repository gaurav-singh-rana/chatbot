<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Sales Support Chatbot</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
.chat-toggle {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #e60000;
    color: #fff;
    border-radius: 50%;
    width: 60px;
    height: 60px;
    border: none;
    font-size: 14px;
    font-weight: 600;
    z-index: 999;
}

.chat-container {
    position: fixed;
    bottom: 90px;
    right: 20px;
    width: 360px;
    max-width: 95%;
    display: none;
    z-index: 999;
}

.chat-card {
    border-radius: 15px;
    overflow: hidden;
}

.chat-header {
    background: #e60000;
    color: #fff;
    padding: 12px;
    font-weight: 600;
}

.chat-body {
    height: 350px;
    overflow-y: auto;
    background: #f9f9f9;
    padding: 10px;
}

.msg {
    padding: 8px 12px;
    border-radius: 15px;
    margin-bottom: 8px;
    font-size: 14px;
    max-width: 85%;
}

.msg-user {
    background: #e60000;
    color: #fff;
    margin-left: auto;
    border-bottom-right-radius: 4px;
}

.msg-bot {
    background: #fff;
    border: 1px solid #ddd;
    border-bottom-left-radius: 4px;
}

.chat-footer {
    border-top: 1px solid #ddd;
    padding: 8px;
}

.message-source {
    font-size: 10px;
    color: #888;
    margin-top: 4px;
}
</style>

</head>

<body>

<button class="chat-toggle" onclick="toggleChat()">Chat</button>

<div class="chat-container" id="chatBox">
    <div class="card shadow chat-card">
        <div class="chat-header d-flex justify-content-between">
            Sales Support Assistant
            <span style="cursor:pointer;" onclick="toggleChat()">X</span>
        </div>

        <div id="chat" class="chat-body">
            <div class="msg msg-bot">
                Ask about plan details, sales pitch, objections, or process steps.
            </div>
        </div>

        <div class="text-center p-2">
            <button class="btn btn-outline-danger btn-sm m-1" onclick="quickMsg('What are the benefits of a postpaid plan?')">Plan</button>
            <button class="btn btn-outline-danger btn-sm m-1" onclick="quickMsg('Give me a short sales pitch for Wi-Fi')">Sales Pitch</button>
            <button class="btn btn-outline-danger btn-sm m-1" onclick="quickMsg('Customer says the plan is expensive')">Objection</button>
            <button class="btn btn-outline-danger btn-sm m-1" onclick="quickMsg('What is the SIM activation process?')">Process</button>
        </div>

        <div class="chat-footer d-flex">
            <input id="msg" class="form-control me-2" placeholder="Ask your sales question...">
            <button class="btn btn-danger" onclick="send()">Send</button>
        </div>
    </div>
</div>

<script>
function toggleChat() {
    const box = document.getElementById('chatBox');
    box.style.display = box.style.display === 'block' ? 'none' : 'block';
}

function appendMessage(text, type, source = null) {
    const chat = document.getElementById('chat');
    const div = document.createElement('div');

    div.className = 'msg ' + (type === 'user' ? 'msg-user' : 'msg-bot');
    div.textContent = text;

    if (source) {
        const sourceLabel = document.createElement('div');
        sourceLabel.className = 'message-source';
        sourceLabel.textContent = source;
        div.appendChild(sourceLabel);
    }

    chat.appendChild(div);
    chat.scrollTop = chat.scrollHeight;
}

function quickMsg(text) {
    document.getElementById('msg').value = text;
    send();
}

function send() {
    const input = document.getElementById('msg');
    const msg = input.value.trim();

    if (!msg) {
        return;
    }

    appendMessage(msg, 'user');
    input.value = '';

    const typing = document.createElement('div');
    typing.className = 'msg msg-bot';
    typing.id = 'typing';
    typing.textContent = '...';
    document.getElementById('chat').appendChild(typing);

    fetch('/api/chat', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ message: msg })
    })
        .then(res => res.json())
        .then(data => {
            document.getElementById('typing').remove();
            appendMessage(data.reply, 'bot', data.source);
        })
        .catch(() => {
            document.getElementById('typing').remove();
            appendMessage('Server error. Please try again.', 'bot');
        });
}

document.getElementById('msg').addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        send();
    }
});
</script>

</body>
</html>
