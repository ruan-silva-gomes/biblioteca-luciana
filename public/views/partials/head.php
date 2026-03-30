<?php require_once __DIR__ . '/../../../config/config.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Controle de Acesso</title>

    <!-- Fontes e Estilos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Design System e Módulos CSS (SOLID) -->
    <link rel="stylesheet" href="assets/css/modules/variables.css">
    <link rel="stylesheet" href="assets/css/modules/base.css">
    <link rel="stylesheet" href="assets/css/modules/navigation.css">
    <link rel="stylesheet" href="assets/css/modules/vision.css">
    <link rel="stylesheet" href="assets/css/modules/dashboard.css">
    <link rel="stylesheet" href="assets/css/modules/tables.css">
    <link rel="stylesheet" href="assets/css/modules/modals.css">
    <link rel="stylesheet" href="assets/css/modules/components.css">
    <link rel="stylesheet" href="assets/css/modules/responsive.css">

    <!-- Dependências Externas (CDNs) -->
    <!-- Chart.js: Usado para renderizar os gráficos de fluxo no Dashboard -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Face-api.js: Motor de IA que roda detecção facial diretamente no navegador do usuário -->
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <!-- html2pdf.js: Usado para gerar o relatório PDF a partir do HTML/Canvases do Dashboard -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
