<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>AI Chatbot</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
/* Floating Button */
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
    font-size: 24px;
    z-index: 999;
}

/* Chat Container */
.chat-container {
    position: fixed;
    bottom: 90px;
    right: 20px;
    width: 350px;
    max-width: 95%;
    display: none;
    z-index: 999;
}

/* Card */
.chat-card {
    border-radius: 15px;
    overflow: hidden;
}

/* Header */
.chat-header {
    background: #e60000;
    color: #fff;
    padding: 12px;
    font-weight: 600;
}

/* Body */
.chat-body {
    height: 350px;
    overflow-y: auto;
    background: #f9f9f9;
    padding: 10px;
}

/* Message */
.msg {
    padding: 8px 12px;
    border-radius: 15px;
    margin-bottom: 8px;
    font-size: 14px;
    max-width: 80%;
}

/* User */
.msg-user {
    background: #e60000;
    color: #fff;
    margin-left: auto;
    border-bottom-right-radius: 4px;
}

/* Bot */
.msg-bot {
    background: #fff;
    border: 1px solid #ddd;
    border-bottom-left-radius: 4px;
}

/* Input */
.chat-footer {
    border-top: 1px solid #ddd;
    padding: 8px;
}
</style>

</head>

<body>

<!-- Floating Button -->
<button class="chat-toggle" onclick="toggleChat()">💬</button>

<!-- Chatbox -->
<div class="chat-container" id="chatBox">
    <div class="card shadow chat-card">

        <!-- Header -->
        <div class="chat-header d-flex justify-content-between">
            Chat Assistant
            <span style="cursor:pointer;" onclick="toggleChat()">✖</span>
        </div>

        <!-- Body -->
        <div id="chat" class="chat-body">
            <div class="msg msg-bot">
                Hi 👋 Ask about plans, objections or activation.
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="text-center p-2">
            <button class="btn btn-outline-danger btn-sm m-1" onclick="quickMsg('what is postpaid plan')">Plan</button>
            <button class="btn btn-outline-danger btn-sm m-1" onclick="quickMsg('it is expensive')">Objection</button>
            <button class="btn btn-outline-danger btn-sm m-1" onclick="quickMsg('sim activation process')">Process</button>
        </div>

        <!-- Footer -->
        <div class="chat-footer d-flex">
            <input id="msg" class="form-control me-2" placeholder="Type...">
            <button class="btn btn-danger" onclick="send()">➤</button>
        </div>

    </div>
</div>

<script>

function toggleChat() {
    let box = document.getElementById('chatBox');
    box.style.display = box.style.display === 'none' ? 'block' : 'none';
}

function appendMessage(text, type, source = null) {
    let chat = document.getElementById('chat');

    let div = document.createElement('div');
    div.className = 'msg ' + (type === 'user' ? 'msg-user' : 'msg-bot');

    div.innerHTML = text + (source ? `<div style="font-size:10px;color:#888;">${source}</div>` : '');

    chat.appendChild(div);
    chat.scrollTop = chat.scrollHeight;
}

function quickMsg(text){
    document.getElementById('msg').value = text;
    send();
}

function send() {
    let input = document.getElementById('msg');
    let msg = input.value.trim();
    if (!msg) return;

    appendMessage(msg, 'user');
    input.value = '';

    let typing = document.createElement('div');
    typing.className = 'msg msg-bot';
    typing.id = 'typing';
    typing.innerHTML = "•••";
    document.getElementById('chat').appendChild(typing);

    fetch('/api/chat', {
        method:'POST',
        headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({message: msg})
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('typing').remove();
        appendMessage(data.reply, 'bot', data.source);
    })
    .catch(() => {
        document.getElementById('typing').remove();
        appendMessage('Server error', 'bot');
    });
}

document.getElementById('msg').addEventListener("keypress", function(e){
    if(e.key === "Enter") send();
});

</script>

</body>
</html>