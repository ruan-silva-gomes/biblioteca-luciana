/**
 * Módulo de Reconhecimento Facial (Cérebro do Sistema de Visão)
 * Responsável por carregar os modelos neurais, gerenciar o hardware da câmera 
 * e realizar a comparação biométrica em tempo real.
 */

console.log('SISTEMA_BIBLIOTECA: Módulo de reconhecimento facial ativo.');

// --- Variáveis de Controle Global (Cuidado ao modificar) ---
window.localStream = null;          // Armazena o fluxo de vídeo atual vindo da webcam
window.modelsLoaded = false;        // Indica se as redes neurais (TensorFlow.js) já estão prontas
window.lastFaceDescriptor = null;   // Cache da última biometria lida (Array de 128 posições float)
window.recognitionInterval = null;  // Identificador do Timer que roda o loop de reconhecimento
window.isHardwareChanging = false;  // Trava de segurança para evitar que o navegador trave ao ligar/desligar rápido

/**
 * Mapeia os elementos físicos do HTML que compõem a interface de visão.
 */
function getCamElements() {
    return {
        video: document.getElementById('video'),        // Onde a imagem da câmera aparece
        canvas: document.getElementById('overlay'),     // Onde desenhamos as marcações faciais
        status: document.getElementById('vision-status'), // Indicador visual de status (Online/Offline)
        placeholder: document.getElementById('camera-placeholder') // Overlay cinza de "Câmera Desligada"
    };
}

/**
 * Solicita autorização do usuário e liga o hardware da câmera.
 * Configura o sensor para uma resolução equilibrada (640x480).
 */
window.startVideo = async function () {
    // Evita race-conditions se o usuário clicar no botão freneticamente
    if (window.isHardwareChanging) return;
    window.isHardwareChanging = true;

    const el = getCamElements();

    // Se já estiver rodando, apenas ignora para não sobrecarregar
    if (window.localStream) {
        window.isHardwareChanging = false;
        return;
    }

    try {
        // Verifica suporte básico de hardware do navegador
        if (!navigator.mediaDevices?.getUserMedia) {
            throw new Error('Hardware de vídeo não acessível nesta plataforma.');
        }

        // Tenta capturar o Stream com configurações ideais para web
        const stream = await navigator.mediaDevices.getUserMedia({
            video: {
                width: { ideal: 640 },
                height: { ideal: 480 },
                facingMode: "user" // Prioriza câmera frontal em notebooks/celulares
            }
        });

        if (el.video) {
            el.video.srcObject = stream;
            window.localStream = stream;

            el.video.onloadedmetadata = () => {
                el.video.play();
                // Remove o placeholder e atualiza o botão para estado ativo
                if (el.placeholder) el.placeholder.classList.add('hidden');
                updateCameraButton(true);

                // Orquestra o carregamento das IAs se for a primeira vez
                if (window.modelsLoaded) {
                    setVisionStatus("Sistema Online", "var(--success)");
                    if (!window.recognitionInterval) startRecognitionLoop();
                } else {
                    setVisionStatus("Carregando IA...", "#ffc107");
                    loadModels();
                }
            };
        }
    } catch (err) {
        // Tratamento de erros amigável para o usuário comum
        if (err.name === 'NotReadableError') {
            showToast('Outro programa está usando sua câmera agora.', 'error');
        } else if (err.name === 'NotAllowedError') {
            showToast('Acesso à câmera bloqueado no navegador.', 'warning');
        } else {
            showToast('Falha técnica de hardware detectada.', 'error');
        }
        setVisionStatus("Hardware Bloqueado", "#dc3545");
    } finally {
        window.isHardwareChanging = false;
    }
}

/**
 * Lógica manual para alternar entre Ligar e Desligar a câmera.
 */
window.toggleCamera = async function () {
    if (window.localStream) {
        await window.stopVideo();
    } else {
        await window.startVideo();
    }
}

/**
 * Encerra o hardware da câmera e todos os loops de processamento.
 * Essencial para liberar o recurso quando o usuário troca de aba.
 */
window.stopVideo = async function () {
    if (window.isHardwareChanging) return;
    window.isHardwareChanging = true;

    const el = getCamElements();

    // 1. Mata o loop de busca facial
    if (window.recognitionInterval) {
        clearInterval(window.recognitionInterval);
        window.recognitionInterval = null;
    }

    // 2. Libera o driver de vídeo
    if (window.localStream) {
        window.localStream.getTracks().forEach(track => {
            track.stop();
            console.log(`Driver: Sensor ${track.label} liberado.`);
        });
        window.localStream = null;
    }

    if (el.video) {
        el.video.pause();
        el.video.srcObject = null;
    }

    // Aguarda o sistema operacional reagir para evitar "Device Busy" em religadas rápidas
    await new Promise(resolve => setTimeout(resolve, 300));

    updateCameraButton(false);

    // Limpa desenhos residuais do canvas de overlay
    if (el.canvas) {
        el.canvas.getContext('2d').clearRect(0, 0, el.canvas.width, el.canvas.height);
    }

    setVisionStatus("Scanner Offline", "#666");
    if (el.placeholder) el.placeholder.classList.remove('hidden');

    window.isHardwareChanging = false;
}

/**
 * Altera visualmente o botão de controle da câmera.
 * @param {boolean} active Se a câmera está ligada ou não.
 */
function updateCameraButton(active) {
    const btn = document.getElementById('btn-toggle-camera');
    if (btn) {
        if (active) {
            btn.innerHTML = '<i class="fas fa-video-slash"></i> Desligar Câmera';
            btn.classList.replace('btn-primary', 'btn-secondary');
        } else {
            btn.innerHTML = '<i class="fas fa-video"></i> Ligar Câmera';
            btn.classList.replace('btn-secondary', 'btn-primary');
        }
    }
}

/**
 * Carrega os modelos neurais SSD e LANDMARKS da biblioteca face-api.
 */
async function loadModels() {
    // Endereço oficial das redes neurais pré-treinadas
    const MODEL_URL = 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@0.22.2/weights';

    try {
        // Carrega simultaneamente as 3 redes necessárias para reconhecimento 1:N
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL), // Detecção rápida
            faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL), // Mapeamento de pontos (olhos, boca)
            faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL) // Extração de biometria
        ]);

        window.modelsLoaded = true;
        setVisionStatus("IA Pronta", "var(--success)");
        startRecognitionLoop(); // Inicia o loop assim que os modelos baixarem
    } catch (err) {
        setVisionStatus("Falha Crítica na IA", "#dc3545");
    }
}

/**
 * Loop de Reconhecimento: Processa frame a frame em busca de rostos conhecidos.
 * Rodando a 1Hz (1 vez por segundo) para máxima eficiência energética.
 */
function startRecognitionLoop() {
    window.recognitionInterval = setInterval(async () => {
        const el = getCamElements();
        if (!el.video || el.video.paused || !window.localStream) return;

        // Ajusta o tamanho da área de desenho para bater com o vídeo real
        const displaySize = { width: el.video.offsetWidth, height: el.video.offsetHeight };
        faceapi.matchDimensions(el.canvas, displaySize);

        try {
            // FASE 1: Detecção e Extração
            const detections = await faceapi.detectAllFaces(el.video, new faceapi.TinyFaceDetectorOptions({ inputSize: 160 }))
                .withFaceLandmarks()
                .withFaceDescriptors();

            // Limpa o canvas antes de desenhar novas molduras
            const ctx = el.canvas.getContext('2d', { willReadFrequently: true });
            ctx.clearRect(0, 0, el.canvas.width, el.canvas.height);

            if (detections.length > 0) {
                // Desenha a moldura de acompanhamento
                faceapi.draw.drawDetections(el.canvas, faceapi.resizeResults(detections, displaySize));

                // Seleciona apenas o rosto principal (centralizado)
                const mainFace = detections[0];
                const box = mainFace.detection.box;

                // FASE 2: Validar Proximidade (Segurança contra deteções de fundo)
                if (box.width < (displaySize.width * 0.15)) {
                    setVisionStatus("Aproxime-se do Totem", "#ffc107");
                    return;
                }

                // FASE 3: Comparação Biométrica 1 para N
                if (!window.knownStudentsCache) return;

                let bestMatch = null;
                let minDistance = 0.55; // Limiar de aceitação: menor = mais seguro

                window.knownStudentsCache.forEach(student => {
                    if (student.face_descriptor) {
                        const studentDesc = new Float32Array(JSON.parse(student.face_descriptor));
                        const distance = faceapi.euclideanDistance(mainFace.descriptor, studentDesc);

                        if (distance < minDistance) {
                            minDistance = distance;
                            bestMatch = student;
                        }
                    }
                });

                // Ação final: Aluno Reconhecido!
                if (bestMatch) {
                    setVisionStatus(`Identificado: ${bestMatch.nome}`, "var(--success)");
                    if (window.lastLoggedId !== bestMatch.id) {
                        await sendAccessLog(bestMatch); // Salva entrada no banco
                    }
                } else {
                    setVisionStatus("Visitante (Novo Cadastro?)", "#dc3545");

                    // Buffer: Se o rosto desconhecido ficar 3s parado, sugere cadastro
                    if (!window.regCounter) window.regCounter = 0;
                    window.regCounter++;
                    if (window.regCounter >= 3) {
                        window.lastFaceDescriptor = mainFace.descriptor;
                        openRegistration(); // Abre formulário automaticamente
                        window.regCounter = 0;
                    }
                }
            } else {
                setVisionStatus("Aguardando Rosto...", "var(--success)");
            }
        } catch (err) {
            console.warn('Processamento de frame pulado devido a carga.');
        }
    }, 1000);
}

/**
 * Atualiza o texto e a cor do monitor de biometria.
 */
function setVisionStatus(text, color) {
    const el = getCamElements();
    if (el.status) {
        el.status.innerText = text;
        el.status.style.color = "white";
        el.status.style.background = color || "var(--gray-light)";
    }
}

/**
 * Persiste a entrada no banco de dados.
 */
async function sendAccessLog(student) {
    window.lastLoggedId = student.id;
    try {
        const res = await fetch(`api.php?action=record_access&id=${student.id}`);
        const data = await res.json();

        if (data.success) {
            showToast(`Entrada registrada: ${student.nome}`);
            if (typeof loadStudents === 'function') loadStudents();

            setTimeout(() => { window.lastLoggedId = null; }, 30000);
        }
    } catch (e) {
        console.error('Falha ao logar acesso.');
    }
}

// Inicializa o estado visual como desativado
document.addEventListener('DOMContentLoaded', () => {
    console.log('facialRecognition.js: Inicializando controles...');

    // Vincula o botão de toggle (redundância de segurança ao onclick do HTML)
    const btnToggle = document.getElementById('btn-toggle-camera');
    if (btnToggle) {
        btnToggle.addEventListener('click', () => {
            if (typeof window.toggleCamera === 'function') window.toggleCamera();
        });
    }

    setVisionStatus("Scanner Desativado", "#666");
    const el = getCamElements();
    if (el.placeholder) el.placeholder.classList.remove('hidden');
});
