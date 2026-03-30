/**
 * Turmas: Gestão de turmas e atribuição de alunos
 */

let currentManageClass = null;

async function loadClasses() {
    try {
        const response = await fetch('api.php?action=list_classes');
        const classes = await response.json();
        const grid = document.getElementById('classes-grid');

        if (!grid) return;
        grid.innerHTML = '';

        if (!classes || classes.length === 0) {
            grid.innerHTML = '<div style="padding: 2rem; text-align: center; color: #666; grid-column: 1 / -1;">Nenhuma turma cadastrada. Crie uma para começar.</div>';
            updateClassSelects([]);
            return;
        }

        updateClassSelects(classes);

        classes.forEach(cls => {
            const card = document.createElement('div');
            card.className = 'card';
            card.style.cursor = 'pointer';
            card.style.transition = 'transform 0.2s, box-shadow 0.2s';

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

function openManageClassModal(className, classId, event) {
    if (event) event.stopPropagation();
    currentManageClass = { name: className, id: classId };

    document.getElementById('manage-class-title').innerText = `Gerenciar Turma: ${className}`;
    document.getElementById('modal-manage-class').classList.add('active');

    refreshStudentsInClassModal();
}

function closeManageClassModal() {
    document.getElementById('modal-manage-class').classList.remove('active');
    currentManageClass = null;
}

function refreshStudentsInClassModal() {
    if (!window.knownStudentsCache || !currentManageClass) return;

    const allStudents = window.knownStudentsCache;
    const enrolled = allStudents.filter(s => s.turma === currentManageClass.name);
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

    const select = document.getElementById('select-add-student');
    select.innerHTML = '<option value="" disabled selected>Selecione um aluno livre...</option>';
    notEnrolled.forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.id;
        opt.innerText = `${s.nome} (${s.turma || 'Sem Turma'})`;
        select.appendChild(opt);
    });
}

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
            const st = window.knownStudentsCache.find(s => s.id == studentId);
            if (st) st.turma = newClassName;
            refreshStudentsInClassModal();
            loadStudents();
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
            window.knownStudentsCache = null;
            await loadStudents();
            loadClasses();
        } else {
            showToast(data.message, "error");
        }
    } catch (err) {
        showToast("Erro ao apagar", "error");
    }
}
