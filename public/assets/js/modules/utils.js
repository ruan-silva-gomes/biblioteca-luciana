/**
 * Utilitários Compartilhados (Relógio, Toasts)
 */

function updateClock() {
    const clock = document.getElementById('clock');
    if (clock) {
        clock.innerText = new Date().toLocaleTimeString('pt-BR');
    }
}

function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerText = message;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 500);
    }, 3000);
}
