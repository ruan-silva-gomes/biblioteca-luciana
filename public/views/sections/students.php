<!-- SEÇÃO 3: GESTÃO DE ALUNOS
     Tabela para visualização de cadastros e exclusão de usuários ruins/antigos.
-->
<section id="sec-students">
    <div class="card">
        <div
            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 10px;">
            <h3>Banco de Dados de Usuários</h3>
            <div style="display: flex; gap: 10px; align-items: center;">
                <input type="text" id="search-student" onkeyup="filterStudents()"
                    placeholder="Pesquisar nome ou turma..." class="form-control"
                    style="padding: 0.6rem; border: 1px solid #ddd; border-radius: 8px; min-width: 250px;">
                <button class="btn-secondary" onclick="exportStudentsCSV()"><i class="fas fa-file-csv"></i>
                    Salvar CSV</button>
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
