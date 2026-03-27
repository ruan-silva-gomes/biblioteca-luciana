/**
 * Módulo de Chat Inteligente (LívIA)
 * 
 * Este arquivo gerencia a interface de conversação, enviando as perguntas 
 * do usuário para o backend e renderizando as respostas da IA que tem 
 * acesso aos dados da biblioteca.
 */

/**
 * Controla a visibilidade da janela de chat (Toggle).
 */
function toggleChat() {
    const chatWindow = document.getElementById('ai-chat-window');
    chatWindow.classList.toggle('hidden');

    // Foca automaticamente no campo de texto para facilitar o uso
    if (!chatWindow.classList.contains('hidden')) {
        const input = document.getElementById('chat-input');
        if (input) input.focus();
    }
}

/**
 * Atalho de teclado: Envia a mensagem ao pressionar "Enter".
 */
function handleChatKey(event) {
    if (event.key === 'Enter') {
        sendMessage();
    }
}

/**
 * Função principal de envio: Captura o texto, chama a API e gerencia estados de espera.
 */
async function sendMessage() {
    const input = document.getElementById('chat-input');
    const message = input.value.trim();

    // Validação básica: não envia mensagens vazias
    if (!message) return;

    // 1. Renderiza a mensagem do usuário no balão azul
    appendMessage(message, 'user');
    input.value = ''; // Limpa o campo imediatamente para feedback de ação

    // 2. Coloca o indicador visual de que a assistente está processando
    const typingId = appendTypingIndicator();

    try {
        // Envia a pergunta para a API central (api.php)
        const response = await fetch('api.php?action=chat_ai', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: message })
        });

        const result = await response.json();

        // Remove o indicador de "digitando..."
        const typingEl = document.getElementById(typingId);
        if (typingEl) typingEl.remove();

        if (result.success) {
            let aiText = result.response;

            // 3. Sistema de "Agentic Execution" - Extrai comandos entre [[[ e ]]]
            const commandRegex = /\[\[\[(.*?)\]\]\]/g;
            let match;
            let commandsToExecute = [];

            while ((match = commandRegex.exec(aiText)) !== null) {
                commandsToExecute.push(match[1].trim());
            }

            // Remove as tags de comando da resposta visual para não sujar o chat
            aiText = aiText.replace(commandRegex, '').trim();

            // 4. Renderiza a resposta final de texto da LívIA
            appendMessage(aiText, 'ai');

            // 5. Executa os comandos extraídos na interface do usuário
            commandsToExecute.forEach(cmd => {
                setTimeout(() => {
                    try {
                        console.log("LívIA executando comando:", cmd);
                        eval(cmd);
                    } catch (e) {
                        console.error('Erro ao executar comando da IA:', e);
                    }
                }, 800); // Leve delay para dar tempo do usuário ler que a ação foi disparada
            });

        } else {
            // Caso a IA falhe (ex: erro de chave API ou limite excedido)
            appendMessage(result.message || 'Desculpe, tive um problema ao processar sua dúvida.', 'ai');
        }
    } catch (err) {
        // Fallback para erros de rede (offline)
        const typingEl = document.getElementById(typingId);
        if (typingEl) typingEl.remove();

        appendMessage('Estou com dificuldades de conexão externa. Tente novamente mais tarde.', 'ai');
        console.error('Chat Error:', err);
    }
}

/**
 * Cria dinamicamente um elemento de mensagem no DOM.
 * 
 * @param {string} text O texto da mensagem.
 * @param {string} side 'user' (direita) ou 'ai' (esquerda).
 */
function appendMessage(text, side) {
    const container = document.getElementById('chat-messages');
    if (!container) return;

    const msgDiv = document.createElement('div');
    msgDiv.className = `message ${side}`;
    msgDiv.innerText = text;

    container.appendChild(msgDiv);

    // Garante que o chat role para baixo sempre que houver mensagem nova
    container.scrollTop = container.scrollHeight;
}

/**
 * Exibe um componente visual temporário de "LívIA está digitando...".
 * @returns {string} O ID único para que possamos remover esse elemento específico depois.
 */
function appendTypingIndicator() {
    const container = document.getElementById('chat-messages');
    const id = 'typing-' + Date.now();
    const div = document.createElement('div');

    div.id = id;
    div.className = 'typing-indicator';
    div.innerText = 'Lívia está analisando...';

    container.appendChild(div);
    container.scrollTop = container.scrollHeight;

    return id;
}
