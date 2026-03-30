<!-- SEÇÃO: GESTÃO DE TURMAS -->
<section id="sec-classes">
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3>Gestão de Turmas</h3>
            <button class="btn-primary" onclick="openClassModal()">+ Nova Turma</button>
        </div>
        <div id="classes-grid"
            style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
            <!-- Cards de turmas injetados via AJAX (app.js) -->
        </div>
    </div>
</section>
