/**
 * Histórico: Listagem de acessos recentes
 */

async function loadHistory() {
    try {
        const response = await fetch('api.php?action=list_history');
        const history = await response.json();

        const tbody = document.getElementById('history-table-body');
        if (tbody) {
            tbody.innerHTML = '';
            if (Array.isArray(history) && history.length > 0) {
                history.forEach(log => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td style="text-align: center; color: #888;">#${log.id}</td>
                        <td style="font-weight: 600;">${log.nome}</td>
                        <td style="text-align: center; color: #555;">${log.turma || 'N/A'}</td>
                        <td style="text-align: center; font-size: 0.9rem;">
                            ${new Date(log.horario_entrada).toLocaleString('pt-BR')}
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 2rem; color: #999;">Nenhum registro encontrado.</td></tr>';
            }
        }
    } catch (err) {
        console.error("Erro ao carregar histórico:", err);
    }
}
