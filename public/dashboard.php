<?php
$currentPage = 'dashboard';
require_once __DIR__ . '/../config/config.php';
header("Cache-Control: no-cache, must-revalidate");

include_once __DIR__ . '/views/partials/head.php';
?>

<body>

    <!-- Navegação Lateral (Sidebar) -->
    <?php include_once __DIR__ . '/views/partials/sidebar.php'; ?>

    <!-- Área de Conteúdo Principal (Main) -->
    <main>
        <!-- Cabeçalho Dinâmico e Relógio -->
        <?php include_once __DIR__ . '/views/partials/header.php'; ?>

        <!-- Seção: Dashboard -->
        <?php include_once __DIR__ . '/views/sections/dashboard.php'; ?>
    </main>

    <!-- Janelas Modais Requeridas -->
    <?php include_once __DIR__ . '/views/modals/export_advanced.php'; ?>

    <!-- Componentes Flutuantes (Toasts) -->
    <?php include_once __DIR__ . '/views/partials/toasts.php'; ?>

    <!-- Scripts -->
    <?php include_once __DIR__ . '/views/partials/scripts.php'; ?>

</body>
</html>
