<?php
// File: chat.php (Diperbarui dengan perbaikan kata sambutan)
session_start();
require_once "db.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.php");
    exit;
}

ob_start(); // Mulai menampung output
?>

<div class="session-sidebar-content">
    <button class="new-chat-btn" id="new-chat-btn"><i class="fas fa-plus"></i> Percakapan Baru</button>
    <hr class="session-divider">
    <h4 class="session-title">Riwayat</h4>
    <ul class="session-list" id="session-list">
        <!-- Daftar sesi akan dimuat oleh JavaScript -->
    </ul>
</div>

<?php
$sidebar_extra_content = ob_get_clean(); // Ambil output dan simpan ke variabel

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Chat dengan AI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/chat.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/showdown/2.1.0/showdown.min.js"></script>
</head>
<body>
    <?php include 'sidebar.php'; ?>    
    <?php require_once 'theme_loader.php'; ?>
    <div class="main-content">
        <header>
            <div class="header-title">
                <i class="fas fa-bars" id="menu-toggle"></i>
                <h2>AI Assistant</h2>
            </div>
             <div class="header-actions">
                <button class="btn" id="header-new-chat-btn"><i class="fas fa-plus"></i> <span>Chat Baru</span></button>
                <div class="user-wrapper" id="user-menu-toggle">
                    <i class="fas fa-user-circle"></i>
                    <div>
                        <h4><?php echo htmlspecialchars($_SESSION["username"]); ?></h4>
                        <small>User</small>
                    </div>
                </div>
                 <div class="dropdown-menu" id="user-dropdown">
                    <a href="settings.php" class="dropdown-item">Profil & Pengaturan</a>
                    <a href="logout.php" class="dropdown-item">Keluar</a>
                </div>
            </div>
        </header>
        <main>
            <div class="chat-container">
                <div class="chat-messages" id="chat-messages">
                     <!-- Konten dinamis, termasuk placeholder, akan dimuat di sini oleh JS -->
                </div>
                <div class="chat-input-area">
                    <div class="input-wrapper">
                        <div class="edit-controls" id="edit-controls">
                            <span>Mengedit pesan...</span>
                            <button id="cancel-edit-btn" class="btn-sm">Batal</button>
                        </div>
                        <form id="chat-form" class="chat-input-form">
                            <div class="main-input-row">
                                <textarea id="prompt-input" placeholder="Ketik pesan Anda di sini..." rows="1"></textarea>
                            </div>
                            <div class="input-actions">
                                <button type="button" class="icon-btn" title="Upload File (segera hadir)" disabled><i class="fas fa-paperclip"></i></button>
                                <div class="context-selector-wrapper">
                                    <select id="context-selector" class="form-control">
                                        <option value="">Tanya umum (tanpa konteks)</option>
                                        <?php 
                                            $materials = $mysqli->query("SELECT cc.id, cc.title, c.title as course_title FROM course_contents cc JOIN courses c ON cc.course_id = c.id WHERE cc.content_type = 'materi' ORDER BY c.title, cc.title");
                                            while($mat = $materials->fetch_assoc()): 
                                        ?>
                                            <option value="<?php echo $mat['id']; ?>">
                                                Jelaskan: <?php echo htmlspecialchars($mat['title'] . ' (' . $mat['course_title'] . ')'); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <button type="submit" id="send-btn" class="btn" title="Kirim" disabled><i class="fas fa-arrow-up"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="js/dashboard.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ... (Definisi variabel DOM)
            const chatForm = document.getElementById('chat-form');
            const promptInput = document.getElementById('prompt-input');
            const chatMessages = document.getElementById('chat-messages');
            const sendBtn = document.getElementById('send-btn');
            const sessionList = document.getElementById('session-list');
            const newChatBtn = document.getElementById('new-chat-btn');
            const headerNewChatBtn = document.getElementById('header-new-chat-btn');
            const contextSelector = document.getElementById('context-selector');
            
            const editControls = document.getElementById('edit-controls');
            const cancelEditBtn = document.getElementById('cancel-edit-btn');
            let isEditing = false;
            let editingMessageId = null;

            let currentSessionId = null;
            const converter = new showdown.Converter({ tables: true, strikethrough: true });

            function adjustTextareaHeight() {
                promptInput.style.height = 'auto';
                promptInput.style.height = (promptInput.scrollHeight) + 'px';
            }
            promptInput.addEventListener('input', adjustTextareaHeight);

            function loadSessions() {
                fetch('AI/session_handler.php?action=fetch_sessions')
                    .then(res => res.json())
                    .then(sessions => {
                        sessionList.innerHTML = '';
                        sessions.forEach(session => {
                            const li = document.createElement('li');
                            li.dataset.sessionId = session.session_id;

                            const titleSpan = document.createElement('span');
                            titleSpan.className = 'session-title-text';
                            titleSpan.textContent = session.title;
                            titleSpan.addEventListener('click', () => loadChat(session.session_id));
                            
                            const deleteBtn = document.createElement('button');
                            deleteBtn.className = 'delete-session-btn';
                            deleteBtn.innerHTML = '<i class="fas fa-trash-alt"></i>';
                            deleteBtn.title = 'Hapus riwayat';
                            deleteBtn.addEventListener('click', () => deleteSession(session.session_id, li));

                            li.appendChild(titleSpan);
                            li.appendChild(deleteBtn);

                            if (session.session_id === currentSessionId) {
                                li.classList.add('active');
                            }
                            sessionList.appendChild(li);
                        });
                    });
            }

            function loadChat(sessionId) {
                currentSessionId = sessionId;
                chatMessages.innerHTML = '';
                showTypingIndicator();

                document.querySelectorAll('.session-list li').forEach(li => {
                    li.classList.toggle('active', li.dataset.sessionId === sessionId);
                });

                fetch(`AI/session_handler.php?action=load_chat&session_id=${sessionId}`)
                    .then(res => res.json())
                    .then(messages => {
                        removeTypingIndicator();
                        if (messages.length > 0) {
                            messages.forEach(msg => appendMessage(msg));
                        } else {
                            // Jika sesi ada tapi kosong, tampilkan placeholder
                            showWelcomePlaceholder();
                        }
                    });
            }
            
            // PERBAIKAN: Fungsi untuk menampilkan placeholder
            function showWelcomePlaceholder() {
                 chatMessages.innerHTML = `
                    <div class="welcome-placeholder" id="welcome-placeholder">
                        <i class="fas fa-robot"></i>
                        <h3>Selamat Datang!</h3>
                        <p>Mulai percakapan baru atau pilih riwayat di samping.</p>
                    </div>`;
            }

            function startNewChat() {
                currentSessionId = null;
                showWelcomePlaceholder(); // Panggil fungsi untuk menampilkan placeholder
                document.querySelectorAll('.session-list li').forEach(li => li.classList.remove('active'));
                cancelEditMode();
            }

            newChatBtn.addEventListener('click', startNewChat);
            headerNewChatBtn.addEventListener('click', startNewChat);
            
            function deleteSession(sessionId, listItem) {
                if (confirm('Apakah Anda yakin ingin menghapus riwayat ini?')) {
                    fetch('AI/session_handler.php?action=delete_session', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ session_id: sessionId })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            listItem.remove();
                            if (currentSessionId === sessionId) {
                                startNewChat();
                            }
                        } else {
                            alert('Gagal menghapus riwayat.');
                        }
                    });
                }
            }

            function sendMessage() {
                const prompt = promptInput.value.trim();
                const contextId = contextSelector.value;
                if (!prompt) return;
                
                // PERBAIKAN: Hapus placeholder jika ada
                const welcomePlaceholder = document.getElementById('welcome-placeholder');
                if (welcomePlaceholder) {
                    welcomePlaceholder.remove();
                }

                let tempMessageId = `temp_${Date.now()}`;
                
                if (!isEditing) {
                     appendMessage({
                        id: tempMessageId,
                        role: 'user', 
                        message: prompt, 
                        created_at: null 
                    });
                } else {
                    document.querySelector(`.message-wrapper[data-id="${editingMessageId}"]`).style.display = 'none';
                    let modelMessageId = parseInt(editingMessageId) + 1;
                    let modelMessage = document.querySelector(`.message-wrapper[data-id="${modelMessageId}"]`);
                    if(modelMessage) modelMessage.style.display = 'none';
                }

                promptInput.value = '';
                adjustTextareaHeight();
                sendBtn.disabled = true;
                showTypingIndicator();
                
                const originalEditingId = editingMessageId;

                fetch('AI/chat_handler.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        prompt, 
                        session_id: currentSessionId, 
                        context_id: contextId,
                        is_editing: isEditing,
                        message_id: editingMessageId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        removeTypingIndicator();
                        appendMessage({ role: 'model', message: `Error: ${data.error}` });
                    } else {
                        if (isEditing) {
                             let oldUserMessage = document.querySelector(`.message-wrapper[data-id="${originalEditingId}"]`);
                             if(oldUserMessage) oldUserMessage.remove();
                             let oldModelMessage = document.querySelector(`.message-wrapper[data-id="${parseInt(originalEditingId) + 1}"]`);
                             if(oldModelMessage) oldModelMessage.remove();
                        } else {
                            let tempMsg = document.getElementById(tempMessageId);
                            if(tempMsg) tempMsg.remove();
                        }

                        appendMessage(data.user_message);
                        typeAIResponse(data.ai_response);
                        
                        if (currentSessionId === null || data.new_title) {
                            currentSessionId = data.session_id;
                            loadSessions();
                        }
                    }
                }).finally(() => {
                    cancelEditMode();
                });
            }

            chatForm.addEventListener('submit', (e) => { e.preventDefault(); sendMessage(); });
            promptInput.addEventListener('input', () => { sendBtn.disabled = promptInput.value.trim().length === 0; });
            promptInput.addEventListener('keydown', (e) => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); if (!sendBtn.disabled) sendMessage(); }});

            function appendMessage(msgData) {
                const isUserMessage = msgData.role === 'user';
                const messageWrapper = document.createElement('div');
                messageWrapper.className = `message-wrapper ${msgData.role}`;
                messageWrapper.dataset.id = msgData.id;

                if (msgData.role === 'user' && msgData.created_at === null) {
                    messageWrapper.id = msgData.id;
                }
                
                const messageTime = document.createElement('div');
                messageTime.className = 'message-time';
                messageTime.textContent = formatRelativeTime(msgData.created_at);

                const messageDiv = document.createElement('div');
                messageDiv.className = `message ${msgData.role}`;
                
                if (isUserMessage) {
                    const messageMain = document.createElement('div');
                    messageMain.className = 'message-main-user';
                    
                    const actionsDiv = document.createElement('div');
                    actionsDiv.className = 'message-actions';
                    
                    const editBtn = document.createElement('button');
                    editBtn.className = 'message-action-btn';
                    editBtn.innerHTML = '<i class="fas fa-pencil-alt"></i>';
                    editBtn.title = 'Edit pesan';
                    editBtn.onclick = () => enterEditMode(msgData.id, msgData.message);
                    
                    actionsDiv.appendChild(editBtn);
                    
                    messageDiv.textContent = msgData.message;
                    messageMain.appendChild(actionsDiv);
                    messageMain.appendChild(messageDiv);
                    messageWrapper.appendChild(messageMain);
                } else {
                    const contentDiv = document.createElement('div');
                    contentDiv.className = 'message-content-wrapper';
                    contentDiv.innerHTML = converter.makeHtml(msgData.message);
                    
                    const actionsDiv = document.createElement('div');
                    actionsDiv.className = 'message-actions';
                    
                    const copyBtn = document.createElement('button');
                    copyBtn.className = 'message-action-btn';
                    copyBtn.innerHTML = '<i class="fas fa-copy"></i>';
                    copyBtn.title = 'Salin teks';
                    copyBtn.onclick = () => copyToClipboard(msgData.message);
                    
                    actionsDiv.appendChild(copyBtn);
                    messageDiv.appendChild(actionsDiv);
                    messageDiv.appendChild(contentDiv);
                    messageWrapper.appendChild(messageDiv);
                }
                
                messageWrapper.appendChild(messageTime);
                chatMessages.appendChild(messageWrapper);
                
                const isScrolledToBottom = chatMessages.scrollHeight - chatMessages.clientHeight <= chatMessages.scrollTop + 1;
                if(isScrolledToBottom) {
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            }
            
            function enterEditMode(messageId, text) {
                isEditing = true;
                editingMessageId = messageId;
                promptInput.value = text;
                promptInput.focus();
                adjustTextareaHeight();
                editControls.style.display = 'flex';

                const userMessage = document.querySelector(`.message-wrapper[data-id="${messageId}"]`);
                if(userMessage) userMessage.style.display = 'none';

                const modelMessageId = parseInt(messageId) + 1;
                const modelMessage = document.querySelector(`.message-wrapper[data-id="${modelMessageId}"]`);
                if(modelMessage) modelMessage.style.display = 'none';
            }

            function cancelEditMode() {
                if (isEditing) {
                    const userMessage = document.querySelector(`.message-wrapper[data-id="${editingMessageId}"]`);
                    if(userMessage) userMessage.style.display = 'flex';

                    const modelMessageId = parseInt(editingMessageId) + 1;
                    const modelMessage = document.querySelector(`.message-wrapper[data-id="${modelMessageId}"]`);
                    if(modelMessage) modelMessage.style.display = 'flex';
                }
                isEditing = false;
                editingMessageId = null;
                promptInput.value = '';
                adjustTextareaHeight();
                editControls.style.display = 'none';
            }
            cancelEditBtn.addEventListener('click', cancelEditMode);
            
            function showTypingIndicator() {
                if (document.getElementById('typing-indicator')) return;
                const typingDiv = document.createElement('div');
                typingDiv.id = 'typing-indicator';
                typingDiv.className = 'message-wrapper model';
                typingDiv.innerHTML = `<div class="message model typing"><span></span><span></span><span></span></div>`;
                chatMessages.appendChild(typingDiv);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            function removeTypingIndicator() {
                const typingIndicator = document.getElementById('typing-indicator');
                if (typingIndicator) {
                    typingIndicator.remove();
                }
            }
            
            function typeAIResponse(response) {
                removeTypingIndicator();

                const aiMessageData = response;
                const messageWrapper = document.createElement('div');
                messageWrapper.className = 'message-wrapper model';
                messageWrapper.dataset.id = aiMessageData.id;

                const messageTime = document.createElement('div');
                messageTime.className = 'message-time';
                
                const messageDiv = document.createElement('div');
                messageDiv.className = 'message model';
                
                const contentDiv = document.createElement('div');
                contentDiv.className = 'message-content-wrapper';

                const actionsDiv = document.createElement('div');
                actionsDiv.className = 'message-actions';
                const copyBtn = document.createElement('button');
                copyBtn.className = 'message-action-btn';
                copyBtn.innerHTML = '<i class="fas fa-copy"></i>';
                copyBtn.title = 'Salin teks';
                copyBtn.onclick = () => copyToClipboard(aiMessageData.message);
                actionsDiv.appendChild(copyBtn);
                
                messageDiv.appendChild(actionsDiv);
                messageDiv.appendChild(contentDiv);
                messageWrapper.appendChild(messageDiv);
                messageWrapper.appendChild(messageTime);
                chatMessages.appendChild(messageWrapper);

                let i = 0;
                let isScrolledToBottom = true;
                
                chatMessages.onscroll = () => {
                    isScrolledToBottom = chatMessages.scrollHeight - chatMessages.clientHeight <= chatMessages.scrollTop + 1;
                };

                function typingEffect() {
                    if (i < aiMessageData.message.length) {
                        contentDiv.innerHTML = converter.makeHtml(aiMessageData.message.substring(0, i + 1));
                        i++;
                        if (isScrolledToBottom) {
                           chatMessages.scrollTop = chatMessages.scrollHeight;
                        }
                        setTimeout(typingEffect, 10);
                    } else {
                        messageTime.textContent = formatRelativeTime(aiMessageData.created_at);
                        chatMessages.onscroll = null; 
                    }
                }
                typingEffect();
            }
            
            function copyToClipboard(text) {
                navigator.clipboard.writeText(text).then(() => {
                });
            }

            function formatRelativeTime(dateString) {
                if (!dateString) return "Baru saja";
                
                const past = new Date(dateString.replace(' ', 'T')); 
                if (isNaN(past.getTime())) {
                    return "Invalid Date";
                }
                const now = new Date();

                const diffInSeconds = Math.floor((now - past) / 1000);
                if (diffInSeconds < 0) return "Baru saja";

                const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                const pastDay = new Date(past.getFullYear(), past.getMonth(), past.getDate());
                const dayDiff = Math.floor((today - pastDay) / (1000 * 60 * 60 * 24));

                if (dayDiff === 0) {
                    return past.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                }
                if (dayDiff === 1) {
                    return "Kemarin";
                }
                if (dayDiff < 7) {
                    return `${dayDiff} hari yang lalu`;
                }
                if (dayDiff < 30) {
                    return `${Math.floor(dayDiff / 7)} minggu yang lalu`;
                }
                if (dayDiff < 365) {
                    return `${Math.floor(dayDiff / 30)} bulan yang lalu`;
                }
                return `${Math.floor(dayDiff / 365)} tahun yang lalu`;
            }

            loadSessions();
            startNewChat();
        });
    </script>
</body>
</html>

