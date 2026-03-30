<?php

/**
 * PÁGINA PRINCIPAL - SISTEMA DE BIBLIOTECA (VISÃO COMPUTACIONAL)
 * Este arquivo orquestra o Frontend, definindo a estrutura HTML5, 
 * importando os estilos SENAI e as bibliotecas de IA (face-api.js).
 */
require_once __DIR__ . '/../config/config.php';

// Controle de cache para evitar que o navegador use versões antigas dos scripts após atualizações
header("Cache-Control: no-cache, must-revalidate");
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Controle de Acesso</title>

    <!-- Fontes e Estilos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Dependências Externas (CDNs) -->
    <!-- Chart.js: Usado para renderizar os gráficos de fluxo no Dashboard -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Face-api.js: Motor de IA que roda detecção facial diretamente no navegador do usuário -->
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
</head>

<body>

    <!-- MENU LATERAL (Sidebar)
         Contém a logo institucional e os links de navegação por seções. 
    -->
    <nav>
        <div class="sidebar-header">
            <h1>SISTEMA</h1>
            <div class="senai-logo"><?php echo APP_NAME_SHORT; ?></div>
        </div>
        <ul class="nav-links">
            <li class="active" onclick="showSection('vision')">
                <i class="fas fa-camera"></i> Reconhecimento
            </li>
            <li onclick="showSection('dashboard')">
                <i class="fas fa-chart-line"></i> Dashboard
            </li>
            <li onclick="showSection('students')">
                <i class="fas fa-users"></i> Alunos
            </li>
            <li onclick="showSection('classes')">
                <i class="fas fa-layer-group"></i> Turmas
            </li>
            <li onclick="showSection('history')">
                <i class="fas fa-history"></i> Histórico
            </li>
            <li onclick="openExportModal()">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </li>
        </ul>
    </nav>

    <!-- ÁREA DE CONTEÚDO PRINCIPAL (Main) -->
    <main>
        <!-- CABEÇALHO DINÂMICO
             Exibe o título da seção atual e um relógio em tempo real.
        -->
        <header>
            <div style="display: flex; align-items: center; gap: 15px;">
                <button id="btn-menu-mobile" class="btn-menu-mobile" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars"></i>
                </button>
                <h2 id="page-title">Painel de Controle</h2>
            </div>
            <div id="clock">00:00:00</div>
        </header>

        <!-- Cortina cinza para o menu mobile (aparece quando o menu abre no celular) -->
        <div id="sidebar-overlay" onclick="toggleMobileMenu()"></div>

        <!-- SEÇÃO 1: SCANNER DE RECONHECIMENTO 
             Onde a IA monitora a webcam buscando usuários cadastrados.
        -->
        <section id="sec-vision">
            <div class="vision-container">
                <div class="card">
                    <div class="video-wrapper">
                        <video id="video" autoplay muted></video>
                        <canvas id="overlay"></canvas> <!-- Camada transparente para desenhos da IA -->
                        <div id="camera-placeholder">Aguardando Câmera...</div>
                    </div>
                </div>

                <div class="status-panel">
                    <div class="card">
                        <h3>Monitor de Biometria</h3>
                        <div id="vision-status" class="status-indicator">Iniciando...</div>
                        <div id="vision-msg" style="margin-top: 10px; font-size: 0.9rem; text-align: center;"></div>
                    </div>

                    <!-- Controles de hardware -->
                    <button class="btn-primary" onclick="openRegistration()" style="width: 100%; margin-bottom: 10px;">
                        <i class="fas fa-user-plus"></i> Novo Cadastro
                    </button>
                    <button class="btn-primary" onclick="window.toggleCamera()" id="btn-toggle-camera" style="width: 100%; margin-bottom: 10px;">
                        <i class="fas fa-video"></i> Ligar Câmera
                    </button>
                    <button class="btn-secondary" onclick="retryCamera()" style="width: 100%;">
                        <i class="fas fa-sync"></i> Resetar Hardware
                    </button>
                </div>
            </div>
        </section>

        <!-- SEÇÃO 2: DASHBOARD (Estatísticas)
             Exibe gráficos de fluxo e contagem de presença.
        -->
        <section id="sec-dashboard" class="hidden">
            <div class="dashboard-grid">
                <div class="card chart-card">
                    <div class="card-header">
                        <h3>Fluxo Horário (Frequência)</h3>
                        <div class="chart-controls">
                            <button id="btn-type-line" class="btn-secondary active" onclick="changeChartType('line')">Linha</button>
                            <button id="btn-type-bar" class="btn-secondary" onclick="changeChartType('bar')">Barra</button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="flowChart"></canvas>
                    </div>
                </div>

                <div class="card stats-card">
                    <div class="card-header" style="margin-bottom: 1rem;">
                        <h3 id="stat-period-title">Acessos Hoje</h3>
                    </div>
                    
                    <div class="total-acessos-number" id="total-acessos" style="margin-bottom: 1rem;">0</div>

                    <div class="period-selector" style="display: flex; gap: 5px; margin-bottom: 1.5rem;">
                        <button id="btn-period-today" class="btn-secondary btn-sm active" onclick="changePeriod('today')" style="flex: 1; padding: 5px;">Hoje</button>
                        <button id="btn-period-week" class="btn-secondary btn-sm" onclick="changePeriod('week')" style="flex: 1; padding: 5px;">Semana</button>
                        <button id="btn-period-month" class="btn-secondary btn-sm" onclick="changePeriod('month')" style="flex: 1; padding: 5px;">Mês</button>
                    </div>

                    <h4 style="font-size: 0.8rem; color: #666; margin-bottom: 0.5rem; text-align: left; border-top: 1px solid #eee; padding-top: 1rem;">Relatórios (Excel)</h4>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <button id="btn-export-dashboard" class="btn-secondary" onclick="exportExcel('today')" style="width: 100%; font-size: 0.85rem;"><i class="fas fa-file-download"></i> Planilha de Hoje</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- SEÇÃO 3: GESTÃO DE ALUNOS
             Tabela para visualização de cadastros e exclusão de usuários ruins/antigos.
        -->
        <section id="sec-students" class="hidden">
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 10px;">
                    <h3>Banco de Dados de Usuários</h3>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="text" id="search-student" onkeyup="filterStudents()" placeholder="Pesquisar nome ou turma..." class="form-control" style="padding: 0.6rem; border: 1px solid #ddd; border-radius: 8px; min-width: 250px;">
                        <button class="btn-secondary" onclick="exportStudentsCSV()"><i class="fas fa-file-csv"></i> Salvar CSV</button>
                        <button class="btn-primary" onclick="openRegistration()">+ Novo Usuário</button>
                    </div>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Matrícula/Turma</th>
                                <th>Última Presença</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="student-table-body">
                            <!-- Popula via AJAX (app.js) -->
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- SEÇÃO: GESTÃO DE TURMAS -->
        <section id="sec-classes" class="hidden">
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3>Gestão de Turmas</h3>
                    <button class="btn-primary" onclick="openClassModal()">+ Nova Turma</button>
                </div>
                <div id="classes-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
                    <!-- Cards de turmas injetados via AJAX (app.js) -->
                </div>
            </div>
        </section>

        <!-- SEÇÃO 4: HISTÓRICO GERAL
             Log completo de todas as entradas registradas no sistema.
        -->
        <section id="sec-history" class="hidden">
            <div class="card">
                <div style="text-align: center; margin-bottom: 1.5rem;">
                    <h3>Audit de Acessos Recentes</h3>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>#ID Log</th>
                                <th>Nome</th>
                                <th>Turma</th>
                                <th>Carimbo de Tempo (Data/Hora)</th>
                            </tr>
                        </thead>
                        <tbody id="history-table-body">
                            <!-- Popula via AJAX (app.js) -->
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <!-- MODAL DE CADASTRO (Pop-up)
         Aparece quando a IA detecta um rosto novo ou quando o botão Novo Registro é clicado.
    -->
    <div id="modal-register" class="modal-overlay">
        <div class="modal-content">
            <h2>Captura de Biometria</h2>
            <form id="registration-form" onsubmit="event.preventDefault(); saveRegistration();">
                <!-- Descriptor: Campo oculto que guarda o array de números da face -->
                <input type="hidden" id="reg-descriptor">

                <div class="form-group">
                    <label for="reg-name">Nome Completo</label>
                    <input type="text" id="reg-name" placeholder="Ex: Nome do Aluno" required>
                </div>

                <div class="form-group">
                    <label for="reg-matricula">Turma Responsável</label>
                    <select id="reg-matricula" required>
                        <option value="" disabled selected>Selecione a Turma</option>
                        <option value="Turma 1">Turma 1</option>
                        <option value="Turma 2">Turma 2</option>
                        <option value="Turma 3">Turma 3</option>
                        <option value="Turma 4">Turma 4</option>
                    </select>
                </div>

                <div id="face-capture-status" style="margin-bottom: 1.5rem; font-weight: bold; text-align: center; color: var(--error);">
                    [Biometria Indisponível]
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn-primary" style="flex: 2;">Confirmar Cadastro</button>
                    <button type="button" class="btn-secondary" style="flex: 1;" onclick="closeRegistration()">Sair</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL DE NOVA TURMA -->
    <div id="modal-class" class="modal-overlay">
        <div class="modal-content">
            <h2 id="class-modal-title">Nova Turma</h2>
            <form id="class-form" onsubmit="event.preventDefault(); saveClass();">
                <input type="hidden" id="class-id">
                <div class="form-group">
                    <label for="class-name">Nome da Turma</label>
                    <input type="text" id="class-name" placeholder="Ex: TDS26" required>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 1.5rem;">
                    <button type="submit" class="btn-primary" style="flex: 2;">Salvar</button>
                    <button type="button" class="btn-secondary" style="flex: 1;" onclick="closeClassModal()">Sair</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL DE GERENCIAMENTO DE ALUNOS NA TURMA -->
    <div id="modal-manage-class" class="modal-overlay">
        <div class="modal-content" style="max-width: 600px;">
            <h2 id="manage-class-title">Gerenciar Turma</h2>

            <div style="margin-bottom: 1rem;">
                <h4>Adicionar Aluno Existente</h4>
                <div style="display: flex; gap: 10px;">
                    <select id="select-add-student" style="flex: 1; padding: 0.75rem; border: 1px solid #ddd; border-radius: 8px;">
                        <option value="" disabled selected>Selecione um aluno...</option>
                        <!-- Options carregadas via JS -->
                    </select>
                    <button class="btn-primary" onclick="addStudentToClass()">Adicionar</button>
                </div>
            </div>

            <h4>Alunos nesta turma</h4>
            <div class="table-container" style="max-height: 250px; overflow-y: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="class-students-body">
                        <!-- Popula via JS -->
                    </tbody>
                </table>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem; border-top: 1px solid #eee; padding-top: 1rem;">
                <button type="button" class="btn-secondary" onclick="closeManageClassModal()">Fechar</button>
                <button type="button" class="btn-action btn-delete" onclick="deleteCurrentClass()">EXCLUIR TURMA INTEIRA</button>
            </div>
        </div>
    </div>

    <!-- MODAL DE EXPORTAÇÃO AVANÇADA -->
    <div id="modal-export-advanced" class="modal-overlay">
        <div class="modal-content" style="max-width: 450px;">
            <h2>Exportar Relatório</h2>
            <p style="margin-bottom: 1.5rem; color: #666; font-size: 0.9rem;">Escolha o período e a data de referência para gerar a planilha de acessos.</p>
            
            <form id="export-form" onsubmit="event.preventDefault(); runAdvancedExport();">
                <div class="form-group">
                    <label>Tipo de Relatório</label>
                    <div style="display: flex; gap: 10px; margin-top: 5px;">
                        <label style="flex: 1; border: 1px solid #ddd; padding: 10px; border-radius: 8px; cursor: pointer; text-align: center;">
                            <input type="radio" name="export-period" value="today" checked style="display: block; margin: 0 auto 5px;"> Dia
                        </label>
                        <label style="flex: 1; border: 1px solid #ddd; padding: 10px; border-radius: 8px; cursor: pointer; text-align: center;">
                            <input type="radio" name="export-period" value="week" style="display: block; margin: 0 auto 5px;"> Semana
                        </label>
                        <label style="flex: 1; border: 1px solid #ddd; padding: 10px; border-radius: 8px; cursor: pointer; text-align: center;">
                            <input type="radio" name="export-period" value="month" style="display: block; margin: 0 auto 5px;"> Mês
                        </label>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 1.5rem;">
                    <label for="export-date">Data de Referência</label>
                    <input type="date" id="export-date" required class="form-control" value="<?php echo date('Y-m-d'); ?>">
                    <small style="color: #999; display: block; margin-top: 5px;">O sistema usará esta data para localizar o dia, a semana ou o mês correspondente.</small>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 2rem;">
                    <button type="submit" class="btn-primary" style="flex: 2;">Gerar Planilha</button>
                    <button type="button" class="btn-secondary" style="flex: 1;" onclick="closeExportModal()">Sair</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Container flutuante para alertas rápidos (Toasts) -->
    <div id="toast-container"></div>

    <!-- CARREGAMENTO DOS SCRIPTS (Final do Body para performance) -->
    <script src="assets/js/facialRecognition.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/app.js?v=<?php echo time(); ?>"></script>
</body>

</html>