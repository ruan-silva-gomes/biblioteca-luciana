/**
 * Alunos: CRUD, Modais e Filtros
 */

function openRegistration(descriptor = null) {
    const desc = descriptor || window.lastFaceDescriptor;

    if (!desc) {
        showToast("Aguarde a câmera detectar um rosto para iniciar o cadastro.", "error");
        showSection('vision');
        return;
    }

    document.getElementById('reg-descriptor').value = JSON.stringify(Array.from(desc));
    document.getElementById('face-capture-status').innerText = "[Biometria Facial Capturada ✓]";
    document.getElementById('face-capture-status').style.color = "var(--success)";
    document.getElementById('modal-register').classList.add('active');
}

function closeRegistration() {
    document.getElementById('modal-register').classList.remove('active');
}

async function saveRegistration() {
    const name = document.getElementById('reg-name').value;
    const turma = document.getElementById('reg-matricula').value;
    const descriptor = document.getElementById('reg-descriptor').value;

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
            window.knownStudentsCache = null;
            loadStudents();
            closeRegistration();
            document.getElementById('registration-form').reset();
        } else {
            showToast(result.message, 'error');
        }
    } catch (err) {
        showToast('Falha na comunicação com o servidor.', 'error');
    }
}

async function loadStudents() {
    try {
        const response = await fetch('api.php?action=list_students');
        const students = await response.json();

        if (Array.isArray(students)) {
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
