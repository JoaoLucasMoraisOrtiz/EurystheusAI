<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<body class="font-sans antialiased">
    <div class="chat-container">
        <div class="chat-header">
            <h1>Assistente Dinâmico de Prompt</h1>
            <p class="chat-subtitle">Toda a conversa é conduzida pela IA para mapear seu problema em detalhes</p>
        </div>
        
        <div id="chatBox" class="chat-box">
            @if(isset($chatHistory) && is_array($chatHistory) && count($chatHistory) > 0)
                @foreach($chatHistory as $msg)
                    <div class="chat-message {{ $msg['role'] === 'user' ? 'user-message' : 'assistant-message' }}">
                        <div class="message-header">
                            <div class="message-avatar {{ $msg['role'] === 'user' ? 'user-avatar' : 'assistant-avatar' }}">
                                {{ $msg['role'] === 'user' ? 'U' : 'AI' }}
                            </div>
                            <span class="message-sender {{ $msg['role'] === 'user' ? 'user-sender' : 'assistant-sender' }}">
                                {{ $msg['role'] === 'user' ? 'Você' : 'Assistente' }}
                            </span>
                        </div>
                        <div class="message-content">
                            {{ $msg['content'] }}
                        </div>
                    </div>
                @endforeach
            @else
                <div class="welcome-message">
                    <p>👋 Olá! Sou seu assistente especializado em criação de prompts.</p>
                    <p>Descreva o que você precisa e eu vou te fazer algumas perguntas para criar o prompt ideal!</p>
                </div>
            @endif
            
            {{-- Typing indicator --}}
            <div id="typingIndicator" class="typing-indicator">
                <div class="typing-dots">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                </div>
            </div>
        </div>

        @if(isset($dossier) && $dossier)
            <div class="dossier-box">
                <div class="dossier-title">🎯 Resumo Final do Seu Prompt</div>
                <ul class="dossier-list">
                    @foreach($dossier as $k=>$v)
                        <li class="dossier-item">
                            <strong>{{ ucfirst($k) }}:</strong> {{ $v }}
                        </li>
                    @endforeach
                </ul>
                <form method="POST" action="{{ route('prompt.chat.reset') }}">
                    @csrf
                    <button type="submit" class="reset-button">🔄 Nova Conversa</button>
                </form>
            </div>
        @else
        <div class="input-container">
            <form id="chatForm" class="input-group">
                @csrf
                <textarea name="message" id="messageInput" class="input-field"
                    placeholder="Responda à pergunta da IA ou digite aqui..." autocomplete="off"
                    required rows="1"></textarea>
                <button type="submit" class="submit-button" id="submitButton">
                    <span id="buttonText">Enviar</span>
                    <span id="buttonIcon">📤</span>
                </button>
            </form>
        </div>
        @endif
    </div>

<script>
    // Funções globais para os botões
    function viewSavedPrompt() {
        // Redireciona para o dashboard onde o prompt estará disponível
        window.location.href = "{{ route('dashboard') }}";
    }

    // Updated to be a general text copier, can be used by new dynamic button
    window.copyTextToClipboard = function(text) {
        if (!text) {
            alert('❌ Nenhum texto para copiar.');
            return;
        }
        if (!navigator.clipboard) { // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed'; // Avoid scrolling to bottom
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
                alert('✅ Texto copiado para a área de transferência!');
            } catch (err) {
                alert('❌ Erro ao copiar. Tente manualmente.');
            }
            document.body.removeChild(textArea);
            return;
        }
        navigator.clipboard.writeText(text).then(() => {
            alert('✅ Texto copiado para a área de transferência!');
        }).catch(err => {
            alert('❌ Erro ao copiar. Tente manualmente.');
        });
    }

    // Original function, might still be used if backend doesn't pass primary_prompt directly
    function copyPromptToClipboard() {
        fetch("{{ route('prompt.saved.json') }}")
            .then(response => response.json())
            .then(data => {
                if (data.success && data.prompt) {
                    copyTextToClipboard(data.prompt);
                } else {
                    alert('❌ Erro ao copiar prompt: ' + (data.message || 'Prompt não encontrado'));
                }
            })
            .catch(error => {
                console.error('Erro ao copiar prompt:', error);
                alert('❌ Erro ao copiar prompt. Tente novamente.');
            });
    }

    function startNewChat() {
        window.location.href = "{{ route('prompt.chat') }}";
    }

    document.addEventListener('DOMContentLoaded', function() {
        const chatForm = document.getElementById('chatForm');
        const messageInput = document.getElementById('messageInput');
        const submitButton = document.getElementById('submitButton');
        const buttonText = document.getElementById('buttonText');
        const buttonIcon = document.getElementById('buttonIcon');
        const typingIndicator = document.getElementById('typingIndicator');
        const chatBox = document.getElementById('chatBox');
        const inputContainer = document.querySelector('.input-container');

        const csrfTokenEl = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenEl ? csrfTokenEl.getAttribute('content') : null;

        if(chatBox) {
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        if(messageInput) {
            messageInput.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 120) + 'px'; // Max height 120px
            });

            messageInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    if (chatForm && submitButton && !submitButton.disabled) {
                        chatForm.dispatchEvent(new Event('submit', { bubbles: true }));
                    }
                }
            });
        }

        if(chatForm) {
            chatForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const msg = messageInput.value.trim();
                if(!msg || (submitButton && submitButton.disabled)) return;

                setLoadingState(true);
                // User message will be added when history is re-rendered from backend response.
                // addSingleMessageToChat('user', msg); // Optional: for immediate local feedback

                messageInput.value = '';
                messageInput.style.height = 'auto';
                
                showTypingIndicator();

                fetch("{{ route('prompt.chat.message') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ message: msg })
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(errData => {
                            throw { status: response.status, data: errData };
                        }).catch(() => {
                            return response.text().then(text => { throw { status: response.status, text: text }; });
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    hideTypingIndicator();

                    if (data.error) {
                        addSingleMessageToChat('assistant', data.error);
                        if (data.history && Array.isArray(data.history)) {
                            // Decide if error messages should clear history or append.
                            // For now, let's assume an error means we might want to see the context.
                            renderChatHistory(data.history, true); // Render history up to the error.
                            addSingleMessageToChat('assistant', data.error); // Add error message after history.
                        }
                    } else if (data.done) {
                        if (data.history && Array.isArray(data.history)) {
                            renderChatHistory(data.history, true);
                        }
                        if (data.dossier) {
                            displayDossier(data.dossier);
                        }
                        disableInputAndShowCompletionActions(data.primary_prompt, data.prompt_id); 
                    } else if (data.processing) {
                        if (data.history && Array.isArray(data.history)) {
                            renderChatHistory(data.history, true);
                        }
                        // The 'message' in processing state is often "I'm working on it"
                        // which might already be part of the history if controller adds it.
                        // If not, and data.message exists, add it.
                        else if (data.message) { 
                            addSingleMessageToChat('assistant', data.message);
                        }
                    } else { // Regular conversation turn
                        if (data.history && Array.isArray(data.history)) {
                            renderChatHistory(data.history, true);
                        } else if (data.message) { // Fallback if history isn't sent but a single message is
                            addSingleMessageToChat('assistant', data.message);
                        }
                    }
                })
                .catch((error) => { 
                    console.error('Erro ao enviar mensagem:', error);
                    hideTypingIndicator();
                    let errorMsg = '❌ Desculpe, ocorreu um erro de comunicação. Tente novamente.';
                    if (error && error.data && error.data.message) {
                        errorMsg = error.data.message;
                    } else if (error && error.text) {
                        errorMsg = `Erro ${error.status || ''}: ${error.text.substring(0,100)}`;
                    } else if (typeof error === 'string') {
                        errorMsg = error;
                    }
                    addSingleMessageToChat('assistant', errorMsg);
                })
                .finally(() => {
                    setLoadingState(false);
                    if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
                });
            });
        }

        function setLoadingState(loading) {
            if (!submitButton || !buttonText || !buttonIcon) return;
            submitButton.disabled = loading;
            if (loading) {
                buttonText.textContent = 'Enviando...';
                buttonIcon.textContent = '⏳';
            } else {
                buttonText.textContent = 'Enviar';
                buttonIcon.textContent = '📤';
            }
        }

        function showTypingIndicator() {
            if (typingIndicator) {
                typingIndicator.style.display = 'flex'; // Changed to flex for dot alignment
                if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
            }
        }

        function hideTypingIndicator() {
            if (typingIndicator) {
                typingIndicator.style.display = 'none';
            }
        }

        function escapeHtml(text) {
            if (typeof text !== 'string') {
                if (text === null || typeof text === 'undefined') return '';
                try {
                    text = String(text);
                } catch (e) {
                    return '';
                }
            }
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function createMessageElement(role, content) {
            const messageDiv = document.createElement('div');
            // Ensure role is one of the expected values, default to 'assistant' if not.
            const validRole = (role === 'user' || role === 'assistant') ? role : 'assistant';
            messageDiv.classList.add('chat-message', validRole === 'user' ? 'user-message' : 'assistant-message');
            
            const avatar = validRole === 'user' ? 'U' : 'AI';
            const sender = validRole === 'user' ? 'Você' : 'Assistente';
            const avatarClass = validRole === 'user' ? 'user-avatar' : 'assistant-avatar';
            const senderClass = validRole === 'user' ? 'user-sender' : 'assistant-sender';
            
            messageDiv.innerHTML = `
                <div class="message-header">
                    <div class="message-avatar ${avatarClass}">${avatar}</div>
                    <span class="message-sender ${senderClass}">${sender}</span>
                </div>
                <div class="message-content">${escapeHtml(content)}</div>
            `;
            return messageDiv;
        }

        function addSingleMessageToChat(role, content) {
            if (!chatBox || !typingIndicator) return;
            const welcomeMessage = chatBox.querySelector('.welcome-message');
            if (welcomeMessage) welcomeMessage.remove();

            const messageElement = createMessageElement(role, content);
            chatBox.insertBefore(messageElement, typingIndicator);
            if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
        }

        function renderChatHistory(historyArray, clearCurrent = true) {
            if (!chatBox || !typingIndicator) return;
            
            const welcomeMessage = chatBox.querySelector('.welcome-message');
            if (welcomeMessage) welcomeMessage.remove();

            if (clearCurrent) {
                const elementsToRemove = [];
                chatBox.childNodes.forEach(child => {
                    if (child.nodeType === 1 && (child.classList.contains('chat-message') || child.classList.contains('prompt-options') || child.classList.contains('dossier-box'))) {
                         elementsToRemove.push(child);
                    }
                });
                elementsToRemove.forEach(el => chatBox.removeChild(el));
            }

            if (historyArray && Array.isArray(historyArray)) {
                historyArray.forEach(msg => {
                    // CRITICAL FIX: Check for msg, msg.role, and msg.content
                    if (msg && typeof msg === 'object' && msg.hasOwnProperty('role') && msg.hasOwnProperty('content')) {
                        const messageElement = createMessageElement(msg.role, msg.content);
                        chatBox.insertBefore(messageElement, typingIndicator);
                    } else {
                        console.warn('Skipping malformed message object in history:', msg);
                    }
                });
            }
            if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
        }
        
        function displayDossier(dossierData) {
            if (!chatBox || !dossierData || typeof dossierData !== 'object') return;

            const existingDossier = chatBox.querySelector('.dossier-box');
            if (existingDossier) existingDossier.remove();

            const dossierDiv = document.createElement('div');
            dossierDiv.className = 'dossier-box';
            let listItems = '';
            for (const key in dossierData) {
                if (dossierData.hasOwnProperty(key)) {
                    let value = dossierData[key];
                    // Using <pre> for values to preserve formatting, especially for multi-line strings or JSON.
                    // Escape HTML for key and value to prevent XSS.
                    listItems += `<li class="dossier-item"><strong>${escapeHtml(key.charAt(0).toUpperCase() + key.slice(1))}:</strong> <div class="dossier-value-content"><pre>${escapeHtml(value)}</pre></div></li>`;
                }
            }

            dossierDiv.innerHTML = `
                <div class="dossier-title">🎯 Dossiê Final da Análise</div>
                <ul class="dossier-list">${listItems}</ul>
            `;
            chatBox.insertBefore(dossierDiv, typingIndicator);
            if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
        }

        function disableInputAndShowCompletionActions(primaryPromptText, promptId) {
            if (inputContainer) {
                inputContainer.style.display = 'none';
            }
            if (messageInput) {
                messageInput.disabled = true;
                messageInput.placeholder = "Conversa finalizada. Use as opções abaixo.";
            }
            if (submitButton) {
                submitButton.disabled = true;
            }

            const oldOptions = chatBox.querySelector('.prompt-options');
            if (oldOptions) oldOptions.remove();

            const optionsDiv = document.createElement('div');
            optionsDiv.className = 'prompt-options';
            
            // Prepare prompt text for JS function call, ensure proper escaping for string literal
            const escapedPromptText = primaryPromptText ? escapeHtml(primaryPromptText).replace(/'/g, "\\'") : '';

            let copyButtonHtml = `<button onclick="copyPromptToClipboard()" class="action-button copy-button">📄 Copiar Prompt Principal (do Painel)</button>`;
            if (primaryPromptText) {
                copyButtonHtml = `<button onclick="copyTextToClipboard('${escapedPromptText}')" class="action-button copy-button">📄 Copiar Prompt Principal</button>`;
            }

            optionsDiv.innerHTML = `
                <div class="completion-box">
                    <h3 class="completion-title">🎉 Análise Concluída!</h3>
                    <p class="completion-subtitle">
                        Seu dossiê está acima. O prompt principal (e outros, se aplicável) foram gerados.
                    </p>
                    <div class="completion-actions">
                        <button onclick="viewSavedPrompt()" class="action-button view-button">📋 Ver Detalhes no Painel</button>
                        ${copyButtonHtml}
                        <button onclick="startNewChat()" class="action-button newchat-button">🔄 Nova Conversa</button>
                    </div>
                </div>
            `;
            
            chatBox.insertBefore(optionsDiv, typingIndicator);
            if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
        }

        // Initial setup
        if (messageInput && !messageInput.disabled) {
            messageInput.focus();
        }
        // The initial chat history is rendered by Blade's foreach.
        // If $chatHistory is empty or null, the welcome message is shown.
        // This new JS logic will take over for dynamic updates.
    });
</script>
<style>
/* ... existing styles ... */

.chat-container {
    /* ... */
    max-width: 800px; /* Increased max-width */
    /* ... */
}

.chat-box {
    /* ... */
    padding: 15px; /* Slightly more padding */
}

.chat-message {
    /* ... */
    margin-bottom: 15px; /* More spacing */
}
.message-header {
    display: flex;
    align-items: center;
    margin-bottom: 5px;
}
.message-avatar {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 10px;
    color: white;
}
.user-avatar { background-color: var(--accent-color); }
.assistant-avatar { background-color: var(--primary-color); }

.message-sender {
    font-weight: 600;
    font-size: 0.9em;
}
.user-sender { color: var(--accent-color); }
.assistant-sender { color: var(--primary-color); }

.message-content {
    padding: 10px 15px;
    border-radius: 0 15px 15px 15px;
    line-height: 1.6;
    word-wrap: break-word;
    background-color: var(--message-bg); /* Ensure this is defined */
    color: var(--text-primary); /* Ensure this is defined */
}
.user-message .message-content {
    background-color: var(--accent-light); /* Ensure this is defined */
    border-radius: 15px 0 15px 15px;
}
.assistant-message .message-content {
    background-color: var(--message-bg-assistant); /* Ensure this is defined */
}


.typing-indicator {
    display: none; /* Hidden by default */
    align-items: center;
    padding: 10px 0;
    margin-left: 45px; /* Align with assistant messages */
}
.typing-dots {
    display: flex;
}
.typing-dot {
    width: 8px;
    height: 8px;
    margin: 0 2px;
    background-color: var(--text-secondary); /* Ensure this is defined */
    border-radius: 50%;
    animation: typing 1.4s infinite ease-in-out both;
}
.typing-dot:nth-child(1) { animation-delay: -0.32s; }
.typing-dot:nth-child(2) { animation-delay: -0.16s; }
@keyframes typing {
    0%, 80%, 100% { transform: scale(0); }
    40% { transform: scale(1.0); }
}

.input-container {
    padding: 10px 15px;
    border-top: 1px solid var(--border-color); /* Ensure this is defined */
    background-color: var(--bg-secondary); /* Ensure this is defined */
}
.input-group {
    display: flex;
    align-items: flex-end; /* Align items to bottom for textarea growth */
}
.input-field {
    flex-grow: 1;
    padding: 12px 15px;
    border: 1px solid var(--border-color);
    border-radius: 20px;
    margin-right: 10px;
    font-size: 1rem;
    resize: none;
    overflow-y: auto; /* scroll if content exceeds max-height */
    max-height: 120px; /* Same as JS max-height */
    line-height: 1.4;
}
.submit-button {
    padding: 10px 15px; /* Reduced padding slightly */
    border-radius: 20px; /* Match input field */
    min-height: 48px; /* Ensure button height matches input field initial */
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Dossier Box Styles */
.dossier-box {
    background: var(--message-bg-assistant, #f0f4f8); /* Fallback color */
    border-radius: 1rem;
    padding: 1.5rem;
    margin: 1rem 0;
    border: 1px solid var(--primary-color, #007bff); /* Fallback color */
    color: var(--text-primary);
}
.dossier-title {
    color: var(--primary-color, #007bff);
    margin: 0 0 1rem 0;
    font-size: 1.3rem;
    font-weight: 600;
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 0.5rem;
}
.dossier-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.dossier-item {
    margin-bottom: 0.8rem;
    padding-bottom: 0.8rem;
    border-bottom: 1px dashed var(--border-color-light, #e0e0e0); /* Fallback color */
}
.dossier-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}
.dossier-item strong {
    display: block;
    color: var(--text-secondary, #555); /* Fallback color */
    margin-bottom: 0.3rem;
    font-size: 0.95rem;
}
.dossier-value-content pre {
    white-space: pre-wrap; /* Allows wrapping of long lines */
    word-wrap: break-word; /* Ensures words break to prevent overflow */
    background-color: var(--bg-primary, #fff); /* Fallback color */
    padding: 0.5rem;
    border-radius: 0.3rem;
    border: 1px solid var(--border-color-light, #e0e0e0);
    font-size: 0.9rem;
    color: var(--text-primary);
    max-height: 200px; /* Limit height of individual dossier values */
    overflow-y: auto;   /* Add scroll if content overflows */
}

/* Completion Actions Box Styles */
.prompt-options .completion-box { /* Target within .prompt-options if that's the wrapper */
    background: var(--message-bg, #ffffff);
    border-radius: 1rem;
    padding: 1.5rem;
    margin: 1rem 0;
    border: 2px solid var(--success-color, #28a745); /* Fallback color */
    text-align: center;
}
.completion-title {
    color: var(--success-color, #28a745);
    margin: 0 0 0.5rem 0;
    font-size: 1.4rem;
    font-weight: 600;
}
.completion-subtitle {
    margin: 0 0 1.5rem 0;
    color: var(--text-secondary, #555);
    font-size: 1rem;
}
.completion-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    justify-content: center;
}
.action-button {
    color: white;
    border: none;
    padding: 0.8rem 1.5rem;
    border-radius: 0.5rem;
    cursor: pointer;
    font-weight: 500;
    flex-grow: 1;
    min-width: 200px;
    font-size: 0.95rem;
    transition: background-color 0.2s ease;
}
.action-button.view-button { background-color: var(--accent-color, #6f42c1); }
.action-button.copy-button { background-color: var(--success-color, #28a745); }
.action-button.newchat-button {
    background-color: var(--border-color, #ced4da);
    color: var(--text-primary, #212529);
}
.action-button:hover {
    opacity: 0.9;
}

/* Ensure CSS variables are defined, e.g., in a :root block or higher up */
:root {
    --primary-color: #007bff;
    --accent-color: #6f42c1;
    --success-color: #28a745;
    --text-primary: #f8f9fa;
    --text-secondary: #adb5bd;
    --bg-primary: #212529;
    --bg-secondary: #343a40;
    --message-bg: #495057;
    --message-bg-assistant: #3A4755;
    --accent-light: #4a3a5e;
    --border-color: #495057;
    --border-color-light: #343a40;
}

/* Dark mode example (add a class to body or html for dark mode) */

/* body.dark-mode {
    
} */

</style>
<!-- ... rest of the body ... -->
</html>
