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
            <button class="btn-primary" onclick="window.toggleCamera()" id="btn-toggle-camera"
                style="width: 100%; margin-bottom: 10px;">
                <i class="fas fa-video"></i> Ligar Câmera
            </button>
            <button class="btn-secondary" onclick="retryCamera()" style="width: 100%;">
                <i class="fas fa-sync"></i> Resetar Hardware
            </button>
        </div>
    </div>
</section>
