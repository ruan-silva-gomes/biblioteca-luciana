/**
 * Lógica Central do Front-end - SISTEMA BIBLIOTECA
 * Este arquivo funciona como o "cérebro" da interface, gerenciando a navegação SPA,
 * a atualização do relógio, a renderização de gráficos e as chamadas AJAX para a API PHP.
 */

// Instância global do gráfico (Chart.js) para permitir destruição e recriação sem bugs de memória
let flowChartInstance = null;
// Tipo de gráfico padrão (pode ser alternado entre 'line' e 'bar' pelo usuário)
let currentChartType = 'line';
// Período de estatísticas padrão (today, week, month)
let currentPeriod = 'today';

/**
 * Ponto de entrada do JavaScript.
 * Executa assim que o HTML base termina de ser carregado.
 */
document.addEventListener('DOMContentLoaded', () => {
    console.log('SISTEMA_BIBLIOTECA: Inicializando interface...');

    // Inicia o relógio digital e configura o intervalo de atualização para cada 1 segundo
    updateClock();
    setInterval(updateClock, 1000);

    // Sincroniza os dados iniciais do banco para o cache local do navegador
    loadStudents();
    loadHistory();

    // Identifica qual seção deve estar ativa no carregamento inicial
    const activeSection = document.querySelector('.nav-links li.active');
    if (activeSection) {
        const onclick = activeSection.getAttribute('onclick');
        const match = onclick ? onclick.match(/'([^']+)'/) : null;
        if (match) showSection(match[1]);
    }
});

/**
 * Atualiza o elemento de texto do relógio com a hora local formatada.
 */
function updateClock() {
    const clock = document.getElementById('clock');
    if (clock) {
        clock.innerText = new Date().toLocaleTimeString('pt-BR');
    }
}

/**
 * Gerencia o sistema de navegação Single Page Application (SPA).
 * Alterna a visibilidade das seções e controla o ciclo de vida da câmera.
 * 
 * @param {string} sectionId O identificador da seção (ex: 'vision', 'dashboard').
 */
async function showSection(sectionId) {
    // 1. Esconde todas as tags <section> e remove o destaque visual do menu lateral
    document.querySelectorAll('section').forEach(s => s.classList.add('hidden'));
    document.querySelectorAll('.nav-links li').forEach(l => l.classList.remove('active'));

    // 2. Torna visível apenas a seção que o usuário clicou
    document.getElementById(`sec-${sectionId}`).classList.remove('hidden');

    // 3. Aplica a classe 'active' no item do menu correspondente para feedback visual
    const activeLi = Array.from(document.querySelectorAll('.nav-links li')).find(li =>
        li.getAttribute('onclick') && li.getAttribute('onclick').includes(`'${sectionId}'`)
    );
    if (activeLi) activeLi.classList.add('active');

    // 4. Atualiza dinamicamente o título no cabeçalho da página
    const titles = {
        'vision': 'Scanner de Acesso',
        'dashboard': 'Painel de Fluxo',
        'students': 'Gestão de Usuários',
        'history': 'Histórico de Entradas'
    };
    document.getElementById('page-title').innerText = titles[sectionId] || 'Início';

    // 5. Aciona lógicas específicas para cada aba ao entrar nela
    if (sectionId === 'dashboard') {
        setTimeout(initCharts, 50); // Delay leve para garantir que o Canvas está pronto
    } else if (sectionId === 'history') {
        loadHistory();
    } else if (sectionId === 'classes') {
        loadClasses();
    }

    // 6. Controle INTELIGENTE da Câmera: apenas desliga ao sair da aba
    if (sectionId !== 'vision') {
        // Desliga o hardware ao sair da aba para economizar bateria e CPU
        if (typeof window.stopVideo === 'function') {
            await window.stopVideo();
        }
    }
}

/**
 * Busca dados estatísticos da API e renderiza os gráficos usando Chart.js.
 */
async function initCharts(period = null) {
    const activePeriod = period || currentPeriod;
    const canvas = document.getElementById('flowChart');
    if (!canvas) return;

    // Importante: Destrói o objeto do gráfico anterior para não haver sobreposição visual
    if (flowChartInstance) flowChartInstance.destroy();

    try {
        // Consulta o backend sobre o volume de acessos e fluxo por hora, passando o período
        const response = await fetch(`api.php?action=dashboard_stats&period=${activePeriod}`);
        const data = await response.json();

        // Atualiza o contador numérico central do dashboard
        const totalAcessos = document.getElementById('total-acessos');
        if (totalAcessos) {
            totalAcessos.innerText = data.total_acessos || 0;
        }

        // Define a configuração visual do gráfico (Cores Institucionais SENAI)
        const chartConfig = {
            type: currentChartType,
            data: {
                labels: ['08h', '09h', '10h', '11h', '12h', '13h', '14h', '15h', '16h', '17h'],
                datasets: [{
                    label: 'Fluxo de Pessoas',
                    data: data.flow_data || [0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                    borderColor: '#D2232A', // Vermelho SENAI
                    backgroundColor: currentChartType === 'line' ? 'rgba(210, 35, 42, 0.1)' : '#D2232A',
                    fill: currentChartType === 'line',
                    tension: 0.4,
                    pointRadius: 6,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#D2232A',
                    pointBorderWidth: 2,
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                }
            }
        };

        // Renderiza o novo gráfico no canvas HTML5
        flowChartInstance = new Chart(canvas.getContext('2d'), chartConfig);

        // Atualiza o título do período no dashboard
        const titles = { 'today': 'Hoje', 'week': 'Semana', 'month': 'Mês' };
        const periodTitle = document.getElementById('stat-period-title');
        if (periodTitle) periodTitle.innerText = `Acessos ${titles[activePeriod]}`;

    } catch (err) {
        console.error("Erro ao carregar dados do dashboard:", err);
    }
}

/**
 * Exibe balões de notificação (Toasts) no canto da tela.
 * @param {string} message Texto da mensagem.
 * @param {string} type 'success' (verde) ou 'error' (vermelho).
 */
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerText = message;

    container.appendChild(toast);

    // Efeito de fade-out e remoção automática após 3 segundos
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 500);
    }, 3000);
}

// --- Módulos de Gestão de Dados (CRUD e API) ---

/**
 * Abre a janela de cadastro injetando a biometria capturada pela IA.
 */
function openRegistration(descriptor = null) {
    // Tenta pegar a face do parâmetro ou do cache global mantido pelo modulo facial
    const desc = descriptor || window.lastFaceDescriptor;

    if (!desc) {
        showToast("Aguarde a câmera detectar um rosto para iniciar o cadastro.", "error");
        window.location.href = "index.php?section=vision";
        return;
    }

    // Serializa o array de números da face para salvar como string no banco
    document.getElementById('reg-descriptor').value = JSON.stringify(Array.from(desc));
    document.getElementById('face-capture-status').innerText = "[Biometria Facial Capturada ✓]";
    document.getElementById('face-capture-status').style.color = "var(--success)";

    // Exibe a modal
    document.getElementById('modal-register').classList.add('active');
}

/**
 * Fecha a janela de cadastro.
 */
function closeRegistration() {
    document.getElementById('modal-register').classList.remove('active');
}

/**
 * Envia o formulário de cadastro para o backend PHP via POST/JSON.
 */
async function saveRegistration() {
    const name = document.getElementById('reg-name').value;
    const turma = document.getElementById('reg-matricula').value;
    const descriptor = document.getElementById('reg-descriptor').value;

    // Validações de segurança no frontend
    if (!descriptor) {
        showToast("Biometria facial não detectada.", "error");
        return;
    }

    if (!name || !turma) {
        showToast("Preencha todos os campos do formulário.", "error");
        return;
    }

    const payload = { nome: name, turma: turma, face_descriptor: descriptor };

    try {
        const response = await fetch('api.php?action=register_student', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (result.success) {
            showToast(`Usuário ${name} registrado com sucesso!`, 'success');
            window.knownStudentsCache = null; // Invalida o cache para forçar recarregamento na IA
            loadStudents();   // Atualiza a tabela
            closeRegistration();
            document.getElementById('registration-form').reset();
        } else {
            showToast(result.message, 'error');
        }
    } catch (err) {
        showToast('Falha na comunicação com o servidor.', 'error');
    }
}

/**
 * Busca a lista de alunos e popula a tabela HTML.
 * Também atualiza o cache usado pela IA para reconhecer rostos.
 */
async function loadStudents() {
    try {
        const response = await fetch('api.php?action=list_students');
        const students = await response.json();

        if (Array.isArray(students)) {
            // Sincroniza o cache global para o facialRecognition.js usar
            window.knownStudentsCache = students;

            const tbody = document.getElementById('student-table-body');
            if (tbody) {
                tbody.innerHTML = '';
                students.forEach(user => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td style="text-align: center; font-weight: bold; color: #888;">${user.id}</td>
                        <td>${user.nome}</td>
                        <td style="text-align: center;">${user.turma || 'N/A'}</td>
                        <td style="text-align: center; font-weight: bold; color: var(--primary);">
                            ${user.last_entry ? new Date(user.last_entry).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }) : 'Ausente'}
                        </td>
                        <td>
                            <button class="btn-action btn-delete" onclick="deleteStudent(${user.id}, '${user.nome}')">Excluir</button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        }
    } catch (err) {
        console.error("Erro ao listar usuários:", err);
    }
}

/**
 * Busca os últimos eventos de acesso para o histórico geral.
 */
async function loadHistory() {
    try {
        const response = await fetch('api.php?action=list_history');
        const history = await response.json();

        const tbody = document.getElementById('history-table-body');
        if (!tbody) return;

        tbody.innerHTML = '';
        if (Array.isArray(history)) {
            history.forEach(log => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="text-align: center; color: #888;">#${log.id}</td>
                    <td style="text-align: center;">${log.nome}</td>
                    <td style="text-align: center; font-weight: bold;">${log.turma || 'N/A'}</td>
                    <td style="text-align: center; color: var(--primary);">
                        ${new Date(log.horario_entrada).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'medium' })}
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }
    } catch (err) {
        console.error("Erro ao listar histórico:", err);
    }
}

/**
 * Remove um cadastro via API.
 */
async function deleteStudent(id, name) {
    if (!confirm(`Deseja excluir permanentemente o cadastro de ${name}?`)) return;

    try {
        const response = await fetch(`api.php?action=delete_student&id=${id}`);
        const result = await response.json();

        if (result.success) {
            showToast("Removido com sucesso.", 'success');
            window.knownStudentsCache = null;
            loadStudents();
        } else {
            showToast(result.message, 'error');
        }
    } catch (err) {
        showToast("Erro na exclusão.", 'error');
    }
}

/**
 * Aciona o endpoint que gera o arquivo CSV/Excel para download.
 * Pode receber 'today', 'week', 'month' ou 'all'.
 */
/**
 * Aciona o endpoint que gera o arquivo CSV/Excel para download.
 * Pode receber 'today', 'week', 'month' ou 'all'.
 * @param {string} period 'today', 'week', 'month' ou 'all'.
 * @param {string|null} date Data específica no formato YYYY-MM-DD.
 */
function exportExcel(period = 'all', date = null) {
    let url = `api.php?action=export_excel&period=${period}`;
    if (date) url += `&date=${date}`;
    window.location.href = url;
}

/**
 * Reinicia o hardware da câmera. Útil em caso de bugs de travamento do navegador.
 */
async function retryCamera() {
    if (typeof window.stopVideo === 'function') {
        await window.stopVideo();
    }

    // Espera a liberação total do hardware pelo SO antes de religar
    setTimeout(async () => {
        if (typeof window.startVideo === 'function') {
            await window.startVideo();
        }
    }, 200);
}


/**
 * Muda o visual do gráfico (Linha vs Barra).
 */
function changeChartType(type) {
    currentChartType = type;

    // Gerencia o estado visual dos botões (destaque)
    document.querySelectorAll('.chart-controls button').forEach(btn => btn.classList.remove('active'));
    const activeBtn = document.getElementById(`btn-type-${type}`);
    if (activeBtn) activeBtn.classList.add('active');

    // Reconstrói o gráfico no Canvas
    initCharts();
}

/**
 * Muda o período das estatísticas (Hoje, Semana, Mês).
 */
function changePeriod(period) {
    currentPeriod = period;

    // Gerencia o estado visual dos botões de período
    document.querySelectorAll('.period-selector button').forEach(btn => btn.classList.remove('active'));
    const activeBtn = document.getElementById(`btn-period-${period}`);
    if (activeBtn) activeBtn.classList.add('active');

    // Atualiza o texto e a ação do botão de exportação no dashboard
    const exportBtn = document.getElementById('btn-export-dashboard');
    if (exportBtn) {
        const labels = { 
            'today': 'de Hoje', 
            'week': 'da Semana', 
            'month': 'do Mês' 
        };
        exportBtn.innerHTML = `<i class="fas fa-file-download"></i> Planilha ${labels[period]}`;
        exportBtn.setAttribute('onclick', `exportExcel('${period}')`);
    }

    // Atualiza os dados
    initCharts(period);
}

/**
 * Controle do menu lateral para dispositivos celulares.
 */
function toggleMobileMenu() {
    const sidebar = document.querySelector('nav');
    const overlay = document.getElementById('sidebar-overlay');

    if (sidebar && overlay) {
        sidebar.classList.toggle('mobile-active');
        overlay.classList.toggle('active');
    }
}

// Garante que o menu mobile feche ao clicar em qualquer item
document.querySelectorAll('.nav-links li').forEach(li => {
    li.addEventListener('click', () => {
        if (window.innerWidth <= 768) toggleMobileMenu();
    });
});

/* =========================================================================
   MÓDULO DE GESTÃO DE TURMAS
   Logica central para crud de turmas e atribuição de alunos
   ========================================================================= */

let currentManageClass = null;

/**
 * Lê todas as turmas do banco e as exibe no grid.
 */
async function loadClasses() {
    try {
        const response = await fetch('api.php?action=list_classes');
        const classes = await response.json();
        const grid = document.getElementById('classes-grid');

        if (!grid) return;
        grid.innerHTML = '';

        if (!classes || classes.length === 0) {
            grid.innerHTML = '<div style="padding: 2rem; text-align: center; color: #666; grid-column: 1 / -1;">Nenhuma turma cadastrada. Crie uma para começar.</div>';
            // Atualiza select do Modal de Cadastro também (se existir)
            updateClassSelects([]);
            return;
        }

        updateClassSelects(classes);

        classes.forEach(cls => {
            const card = document.createElement('div');
            card.className = 'card';
            card.style.cursor = 'pointer';
            card.style.transition = 'transform 0.2s, box-shadow 0.2s';

            // Efeito hover básico inline
            card.onmouseover = () => { card.style.transform = 'translateY(-3px)'; card.style.boxShadow = '0 6px 12px rgba(0,0,0,0.1)'; };
            card.onmouseout = () => { card.style.transform = ''; card.style.boxShadow = ''; };

            card.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <h4 style="margin: 0; font-size: 1.25rem; color: var(--text);">${cls.nome}</h4>
                        <span style="font-size: 0.85rem; color: #666;">Criada em ${new Date(cls.created_at).toLocaleDateString('pt-BR')}</span>
                    </div>
                </div>
                <button class="btn-secondary" style="width: 100%; margin-top: 1rem;" onclick="openManageClassModal('${cls.nome}', ${cls.id}, event)">
                    Gerenciar Alunos
                </button>
            `;
            grid.appendChild(card);
        });
    } catch (err) {
        console.error("Erro ao carregar turmas:", err);
        showToast("Falha ao carregar as turmas", "error");
    }
}

/**
 * Atualiza todos os <select> de turma do sistema.
 */
function updateClassSelects(classes) {
    const regSelect = document.getElementById('reg-matricula');
    if (regSelect) {
        regSelect.innerHTML = '<option value="" disabled selected>Selecione a Turma</option>';
        classes.forEach(cls => {
            const opt = document.createElement('option');
            opt.value = cls.nome;
            opt.innerText = cls.nome;
            regSelect.appendChild(opt);
        });
    }
}

// === MODAL DE NOVA TURMA ===
function openClassModal() {
    document.getElementById('modal-class').classList.add('active');
    document.getElementById('class-id').value = '';
    document.getElementById('class-name').value = '';
    setTimeout(() => document.getElementById('class-name').focus(), 100);
}

function closeClassModal() {
    document.getElementById('modal-class').classList.remove('active');
}

async function saveClass() {
    const name = document.getElementById('class-name').value.trim();
    if (!name) return showToast("Insira um nome", "error");

    try {
        const res = await fetch('api.php?action=create_class', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nome: name })
        });
        const data = await res.json();

        if (data.success) {
            showToast(data.message, "success");
            closeClassModal();
            loadClasses();
        } else {
            showToast(data.message, "error");
        }
    } catch (err) {
        showToast("Erro de rede ao salvar turma", "error");
    }
}

// === MODAL DE GERENCIAMENTO (ALUNOS DENTRO DA TURMA) ===
function openManageClassModal(className, classId, event) {
    if (event) event.stopPropagation(); // Evita acoplar em cliques múltiplos
    currentManageClass = { name: className, id: classId };

    document.getElementById('manage-class-title').innerText = `Gerenciar Turma: ${className}`;
    document.getElementById('modal-manage-class').classList.add('active');

    // Atualiza lista de alunos
    refreshStudentsInClassModal();
}

function closeManageClassModal() {
    document.getElementById('modal-manage-class').classList.remove('active');
    currentManageClass = null;
}

/**
 * Filtra no array local (knownStudentsCache) todos os alunos e os separa.
 */
function refreshStudentsInClassModal() {
    if (!window.knownStudentsCache || !currentManageClass) return;

    const allStudents = window.knownStudentsCache;
    const enrolled = allStudents.filter(s => s.turma === currentManageClass.name);
    // Para o select "Adicionar", mostramos pessoas que não estão nesta turma.
    const notEnrolled = allStudents.filter(s => s.turma !== currentManageClass.name);

    const tbody = document.getElementById('class-students-body');
    tbody.innerHTML = '';

    enrolled.forEach(s => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${s.nome}</td>
            <td style="text-align: right;">
                <button class="btn-action btn-delete" title="Remover aluno desta turma" onclick="removeStudentFromClass(${s.id}, '${s.nome}')">
                    <i class="fas fa-user-minus"></i> Desvincular
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    if (enrolled.length === 0) {
        tbody.innerHTML = '<tr><td colspan="2" style="text-align:center; color:#666;">Nenhum aluno nesta turma.</td></tr>';
    }

    // Popular o Select de "Adicionar"
    const select = document.getElementById('select-add-student');
    select.innerHTML = '<option value="" disabled selected>Selecione um aluno livre...</option>';
    notEnrolled.forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.id;
        opt.innerText = `${s.nome} (${s.turma || 'Sem Turma'})`;
        select.appendChild(opt);
    });
}

/**
 * Altera a turma de um aluno.
 */
async function addStudentToClass() {
    if (!currentManageClass) return;
    const select = document.getElementById('select-add-student');
    const studentId = select.value;

    if (!studentId) return showToast("Selecione um aluno", "error");

    await updateStudentClassAPI(studentId, currentManageClass.name);
}

async function removeStudentFromClass(studentId, studentName) {
    if (!confirm(`Tirar ${studentName} desta turma? Ele ficará 'Sem Turma'.`)) return;
    await updateStudentClassAPI(studentId, 'Sem Turma');
}

async function updateStudentClassAPI(studentId, newClassName) {
    try {
        const res = await fetch('api.php?action=update_student_class', {
            method: 'POST',
            body: JSON.stringify({ id: studentId, className: newClassName })
        });
        const data = await res.json();

        if (data.success) {
            showToast(data.message, "success");
            // Atualizar cache local
            const st = window.knownStudentsCache.find(s => s.id == studentId);
            if (st) st.turma = newClassName; // Modifica in-place

            refreshStudentsInClassModal(); // Recarrega view local
            loadStudents(); // Recarrega tabela mestra em background
        } else {
            showToast(data.message, "error");
        }
    } catch (err) {
        showToast("Erro na modificação do aluno", "error");
    }
}

async function deleteCurrentClass() {
    if (!currentManageClass) return;

    if (!confirm(`CERTEZA ABSOLUTA que deseja apagar a turma "${currentManageClass.name}"?\nTODOS OS ALUNOS e acessos destas turmas também serão EXCLUÍDOS permanentemente!`)) {
        return;
    }

    try {
        const res = await fetch(`api.php?action=delete_class&id=${currentManageClass.id}`);
        const data = await res.json();

        if (data.success) {
            showToast(data.message, "success");
            closeManageClassModal();
            // Invalida cache local pois muitos alunos mudaram
            window.knownStudentsCache = null;
            await loadStudents(); // Recarrega cache do servidor completo
            loadClasses();
        } else {
            showToast(data.message, "error");
        }
    } catch (err) {
        showToast("Erro ao apagar", "error");
    }
}

/* =========================================================================
   FUNCIONALIDADES DA ABA DE ALUNOS (Pesquisa e Exportação)
   ========================================================================= */

/**
 * Filtra alunos na tabela com base no texto digitado pelo usuário.
 */
function filterStudents() {
    const input = document.getElementById("search-student");
    const filter = input.value.toUpperCase();
    const tbody = document.getElementById("student-table-body");
    const trs = tbody.getElementsByTagName("tr");

    for (let i = 0; i < trs.length; i++) {
        let tdName = trs[i].getElementsByTagName("td")[1];
        let tdClass = trs[i].getElementsByTagName("td")[2];

        if (tdName || tdClass) {
            let txtName = tdName.textContent || tdName.innerText;
            let txtClass = tdClass.textContent || tdClass.innerText;

            if (txtName.toUpperCase().indexOf(filter) > -1 || txtClass.toUpperCase().indexOf(filter) > -1) {
                trs[i].style.display = "";
            } else {
                trs[i].style.display = "none";
            }
        }
    }
}

/**
 * Aciona o endpoint específico de exportação de dados brutos de alunos.
 */
function exportStudentsCSV() {
    window.location.href = 'api.php?action=export_students';
}

/**
 * Funções de Exportação Avançada (Modal)
 */
function openExportModal() {
    document.getElementById('modal-export-advanced').classList.add('active');
}

function closeExportModal() {
    document.getElementById('modal-export-advanced').classList.remove('active');
}

function runAdvancedExport() {
    const period = document.querySelector('input[name="export-period"]:checked').value;
    const date = document.getElementById('export-date').value;

    if (!date) {
        showToast("Por favor, selecione uma data de referência.", "error");
        return;
    }

    exportExcel(period, date);
    showToast("Gerando sua planilha...", "success");
    closeExportModal();
}
