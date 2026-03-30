<?php
/**
 * PÁGINA PRINCIPAL E SCANNER - SISTEMA DE BIBLIOTECA (VISÃO COMPUTACIONAL)
 * -------------------------------------------------------------------
 * Modelo Multi-Page (MPA). Esta página foca APENAS na câmera e reconhecimento.
 */
$currentPage = 'index';
// Configurações e Cache
require_once __DIR__ . '/../config/config.php';
header("Cache-Control: no-cache, must-revalidate");

// 1. Cabeçalho HTML, Meta Tags e CDNs
include_once __DIR__ . '/views/partials/head.php';
?>

<body>

    <!-- 2. Navegação Lateral (Sidebar) -->
    <?php include_once __DIR__ . '/views/partials/sidebar.php'; ?>

    <!-- 3. Área de Conteúdo Principal (Main) -->
    <main>
        <!-- Cabeçalho Dinâmico e Relógio -->
        <?php include_once __DIR__ . '/views/partials/header.php'; ?>

        <!-- Seções de Conteúdo (MPA - APENAS CÂMERA AQUI) -->
        <?php include_once __DIR__ . '/views/sections/vision.php'; ?>
    </main>

    <!-- 4. Janelas Modais Requeridas (Apenas as usadas no Scanner Global) -->
    <?php 
        include_once __DIR__ . '/views/modals/register.php';           // Acionado via botão no Scanner
        include_once __DIR__ . '/views/modals/export_advanced.php';    // Acionado globalmente via Sidebar
    ?>

    <!-- 5. Componentes Flutuantes (Toasts) -->
    <?php include_once __DIR__ . '/views/partials/toasts.php'; ?>

    <!-- 6. Scripts e Lógica (Arquitetura Modular em JS) -->
    <?php include_once __DIR__ . '/views/partials/scripts.php'; ?>

</body>
</html>