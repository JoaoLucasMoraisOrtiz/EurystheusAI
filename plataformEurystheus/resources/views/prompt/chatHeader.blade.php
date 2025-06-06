<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Assistente de Criação de Prompt - EurystheusAI</title>
@vite(['resources/css/app.css', 'resources/js/app.js'])
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --primary-bg: #0f172a;
        --secondary-bg: #1e293b;
        --chat-bg: #334155;
        --message-bg: #475569;
        --user-color: #3b82f6;
        --assistant-color: #10b981;
        --text-primary: #f1f5f9;
        --text-secondary: #cbd5e1;
        --text-muted: #94a3b8;
        --border-color: #475569;
        --accent-color: #6366f1;
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --error-color: #ef4444;
        --shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        --shadow-sm: 0 2px 10px rgba(0, 0, 0, 0.2);
    }

    * {
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        background: linear-gradient(135deg, var(--primary-bg) 0%, var(--secondary-bg) 100%);
        color: var(--text-primary);
        margin: 0;
        padding: 0;
        min-height: 100vh;
        overflow-x: hidden;
    }

    .chat-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 2rem 1.5rem;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .chat-header {
        text-align: center;
        margin-bottom: 2rem;
        animation: fadeInDown 0.6s ease-out;
    }

    .chat-header h1 {
        font-size: 2rem;
        font-weight: 700;
        margin: 0 0 0.5rem 0;
        background: linear-gradient(135deg, var(--user-color), var(--assistant-color));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .chat-subtitle {
        color: var(--text-secondary);
        font-size: 1rem;
        font-weight: 400;
        margin: 0;
    }

    .chat-box {
        background: var(--secondary-bg);
        border-radius: 1rem;
        box-shadow: var(--shadow);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        min-height: 400px;
        max-height: 60vh;
        overflow-y: auto;
        flex: 1;
        border: 1px solid var(--border-color);
        animation: fadeInUp 0.6s ease-out 0.2s both;
    }

    .chat-box::-webkit-scrollbar {
        width: 6px;
    }

    .chat-box::-webkit-scrollbar-track {
        background: var(--chat-bg);
        border-radius: 3px;
    }

    .chat-box::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 3px;
    }

    .chat-box::-webkit-scrollbar-thumb:hover {
        background: var(--text-muted);
    }

    .chat-message {
        margin-bottom: 1.5rem;
        animation: messageSlideIn 0.4s ease-out;
    }

    .message-header {
        display: flex;
        align-items: center;
        margin-bottom: 0.5rem;
        gap: 0.5rem;
    }

    .message-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .user-avatar {
        background: linear-gradient(135deg, var(--user-color), #1d4ed8);
        color: white;
    }

    .assistant-avatar {
        background: linear-gradient(135deg, var(--assistant-color), #059669);
        color: white;
    }

    .message-sender {
        font-weight: 600;
        font-size: 0.875rem;
    }

    .user-sender {
        color: var(--user-color);
    }

    .assistant-sender {
        color: var(--assistant-color);
    }

    .message-content {
        background: var(--message-bg);
        padding: 1rem;
        border-radius: 0.75rem;
        margin-left: 2.5rem;
        line-height: 1.6;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .user-message .message-content {
        background: linear-gradient(135deg, var(--user-color), #1d4ed8);
        color: white;
        margin-left: 0;
        margin-right: 2.5rem;
    }

    .assistant-message .message-content {
        background: var(--chat-bg);
    }

    /* Enhanced Welcome Message */
    .welcome-message {
        text-align: center;
        color: var(--text-secondary);
        padding: 2rem;
        animation: fadeInUp 0.8s ease-out;
    }

    .welcome-icon {
        font-size: 4rem;
        margin-bottom: 1.5rem;
        animation: bounce 2s infinite;
    }

    .welcome-message h3 {
        color: var(--text-primary);
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, var(--user-color), var(--assistant-color));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .welcome-features {
        display: grid;
        gap: 1rem;
        margin: 2rem 0;
        text-align: left;
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
    }

    .feature-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: var(--chat-bg);
        border-radius: 0.75rem;
        border: 1px solid var(--border-color);
        transition: all 0.3s ease;
    }

    .feature-item:hover {
        background: var(--message-bg);
        transform: translateX(5px);
    }

    .feature-icon {
        font-size: 1.5rem;
        min-width: 2rem;
        text-align: center;
    }

    .start-prompt {
        background: linear-gradient(135deg, var(--accent-color), #4f46e5);
        color: white;
        padding: 1.5rem;
        border-radius: 0.75rem;
        font-weight: 600;
        margin-top: 2rem;
        animation: pulse 2s infinite;
    }

    /* Smart Suggestions */
    .smart-suggestions {
        background: var(--secondary-bg);
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
        padding: 1rem;
        margin-bottom: 1rem;
        animation: slideInUp 0.5s ease-out;
    }

    .suggestions-title {
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.75rem;
        font-size: 0.9rem;
    }

    .suggestions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 0.5rem;
    }

    .suggestion-btn {
        background: var(--chat-bg);
        color: var(--text-secondary);
        border: 1px solid var(--border-color);
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: left;
    }

    .suggestion-btn:hover {
        background: var(--accent-color);
        color: white;
        border-color: var(--accent-color);
        transform: translateY(-1px);
    }

    /* Enhanced Input Container */
    .input-container {
        background: var(--secondary-bg);
        border-radius: 1rem;
        padding: 1rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-color);
        animation: fadeInUp 0.6s ease-out 0.4s both;
    }

    .input-group {
        display: flex;
        gap: 0.75rem;
        align-items: flex-end;
    }

    .input-wrapper {
        position: relative;
        flex: 1;
    }

    .input-field {
        width: 100%;
        border: 2px solid var(--border-color);
        border-radius: 0.75rem;
        padding: 1rem;
        padding-right: 5rem;
        background: var(--chat-bg);
        color: var(--text-primary);
        font-size: 1rem;
        resize: vertical;
        min-height: 50px;
        max-height: 120px;
        transition: all 0.2s ease;
        font-family: inherit;
    }

    .input-field:focus {
        outline: none;
        border-color: var(--accent-color);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .input-field::placeholder {
        color: var(--text-muted);
    }

    .input-tools {
        position: absolute;
        bottom: 0.75rem;
        right: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .char-counter {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .suggestions-toggle {
        background: var(--accent-color);
        color: white;
        border: none;
        border-radius: 0.375rem;
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .suggestions-toggle:hover {
        background: #4f46e5;
        transform: scale(1.05);
    }

    .submit-button {
        background: linear-gradient(135deg, var(--accent-color), #4f46e5);
        color: white;
        border: none;
        border-radius: 0.75rem;
        padding: 1rem 1.5rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        min-width: 100px;
        justify-content: center;
    }

    .submit-button:hover:not(:disabled) {
        background: linear-gradient(135deg, #4f46e5, #3730a3);
        transform: translateY(-1px);
        box-shadow: var(--shadow-sm);
    }

    .submit-button:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    /* Context Indicator */
    .context-indicator {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.5rem;
        padding: 0.5rem 0.75rem;
        background: rgba(99, 102, 241, 0.1);
        border: 1px solid rgba(99, 102, 241, 0.3);
        border-radius: 0.5rem;
        font-size: 0.875rem;
        color: var(--accent-color);
    }

    .context-icon {
        font-size: 1rem;
    }

    /* Completion Message */
    .prompt-options {
        margin: 1rem 0;
        animation: fadeInUp 0.6s ease-out;
    }

    .completion-message {
        background: linear-gradient(135deg, var(--success-color), #059669);
        color: white;
        padding: 2rem;
        border-radius: 1rem;
        text-align: center;
        box-shadow: var(--shadow);
    }

    .completion-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        animation: bounce 1s infinite;
    }

    .completion-message h3 {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0 0 1rem 0;
    }

    .completion-message p {
        margin: 0 0 2rem 0;
        opacity: 0.9;
    }

    .options-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .option-btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 0.5rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        min-width: 150px;
    }

    .option-btn.primary {
        background: white;
        color: var(--success-color);
    }

    .option-btn.secondary {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .option-btn.tertiary {
        background: transparent;
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.5);
    }

    .option-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    /* Typing Indicator */
    .typing-indicator {
        display: none;
        margin-left: 2.5rem;
        padding: 1rem;
        background: var(--chat-bg);
        border-radius: 0.75rem;
        border: 1px solid rgba(255, 255, 255, 0.1);
        animation: fadeIn 0.3s ease-out;
    }

    .typing-dots {
        display: flex;
        gap: 4px;
    }

    .typing-dot {
        width: 8px;
        height: 8px;
        background: var(--text-muted);
        border-radius: 50%;
        animation: typingPulse 1.4s infinite;
    }

    .typing-dot:nth-child(2) {
        animation-delay: 0.2s;
    }

    .typing-dot:nth-child(3) {
        animation-delay: 0.4s;
    }

    /* ...existing code for dossier-box and other components... */
    .dossier-box {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        border: 2px solid var(--warning-color);
        border-radius: 1rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        color: #92400e;
        animation: fadeInUp 0.6s ease-out;
    }

    .dossier-title {
        font-weight: 700;
        font-size: 1.125rem;
        margin-bottom: 1rem;
        color: #78350f;
    }

    .dossier-list {
        list-style: none;
        padding: 0;
        margin: 0 0 1rem 0;
    }

    .dossier-item {
        padding: 0.5rem 0;
        border-bottom: 1px solid rgba(146, 64, 14, 0.2);
    }

    .dossier-item:last-child {
        border-bottom: none;
    }

    .reset-button {
        background: #6b7280;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .reset-button:hover {
        background: #4b5563;
        transform: translateY(-1px);
    }

    /* Progress Bar Styles */
    .progress-container {
        margin-top: 1.5rem;
        animation: fadeInUp 0.6s ease-out 0.3s both;
    }

    .progress-bar {
        background: var(--chat-bg);
        height: 8px;
        border-radius: 4px;
        margin-bottom: 1rem;
        overflow: hidden;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .progress-fill {
        background: linear-gradient(90deg, var(--user-color), var(--assistant-color));
        height: 100%;
        width: 20%;
        border-radius: 4px;
        transition: width 0.5s ease;
        position: relative;
    }

    .progress-fill::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        animation: shimmer 2s infinite;
    }

    .progress-steps {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
    }

    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
        opacity: 0.5;
        transition: all 0.3s ease;
    }

    .step.active {
        opacity: 1;
        transform: scale(1.05);
    }

    .step.completed {
        opacity: 0.8;
    }

    .step-number {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--chat-bg);
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
        border: 2px solid var(--border-color);
        transition: all 0.3s ease;
    }

    .step.active .step-number {
        background: var(--accent-color);
        color: white;
        border-color: var(--accent-color);
        box-shadow: 0 0 20px rgba(99, 102, 241, 0.4);
    }

    .step.completed .step-number {
        background: var(--success-color);
        color: white;
        border-color: var(--success-color);
    }

    .step.completed .step-number::after {
        content: '✓';
        font-size: 0.75rem;
    }

    .step-label {
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--text-muted);
        text-align: center;
        transition: color 0.3s ease;
    }

    .step.active .step-label {
        color: var(--text-primary);
        font-weight: 600;
    }

    .step.completed .step-label {
        color: var(--success-color);
    }

    /* Step Indicator */
    .step-indicator {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.5rem;
        padding: 0.75rem 1rem;
        background: rgba(99, 102, 241, 0.1);
        border: 1px solid rgba(99, 102, 241, 0.3);
        border-radius: 0.5rem;
        font-size: 0.875rem;
        color: var(--accent-color);
        animation: slideInUp 0.3s ease-out;
    }

    .step-icon {
        font-size: 1rem;
    }

    /* Animations */
    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
        40% { transform: translateY(-10px); }
        60% { transform: translateY(-5px); }
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.02); }
    }

    @keyframes messageSlideIn {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes typingPulse {
        0%, 80%, 100% {
            transform: scale(0.8);
            opacity: 0.5;
        }
        40% {
            transform: scale(1);
            opacity: 1;
        }
    }

    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .chat-container {
            padding: 1rem;
        }

        .chat-header h1 {
            font-size: 1.5rem;
        }

        .message-content {
            margin-left: 2rem;
        }

        .user-message .message-content {
            margin-left: 0;
            margin-right: 2rem;
        }

        .input-group {
            flex-direction: column;
            gap: 0.5rem;
        }

        .submit-button {
            width: 100%;
        }

        .suggestions-grid {
            grid-template-columns: 1fr;
        }

        .options-buttons {
            flex-direction: column;
        }

        .option-btn {
            width: 100%;
        }
    }
</style>