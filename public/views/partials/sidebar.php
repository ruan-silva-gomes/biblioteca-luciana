<!-- MENU LATERAL (Sidebar)
     Contém a logo institucional e os links de navegação por seções. 
-->
<nav>
    <div class="sidebar-header">
        <h1>SISTEMA</h1>
        <div class="senai-logo"><?php echo APP_NAME_SHORT; ?></div>
    </div>
    <ul class="nav-links">
        <li class="<?php echo (isset($currentPage) && $currentPage === 'index') ? 'active' : ''; ?>" onclick="window.location.href='index.php'">
            <i class="fas fa-camera"></i> Reconhecimento
        </li>
        <li class="<?php echo (isset($currentPage) && $currentPage === 'dashboard') ? 'active' : ''; ?>" onclick="window.location.href='dashboard.php'">
            <i class="fas fa-chart-line"></i> Dashboard
        </li>
        <li class="<?php echo (isset($currentPage) && $currentPage === 'students') ? 'active' : ''; ?>" onclick="window.location.href='students.php'">
            <i class="fas fa-users"></i> Alunos
        </li>
        <li class="<?php echo (isset($currentPage) && $currentPage === 'classes') ? 'active' : ''; ?>" onclick="window.location.href='classes.php'">
            <i class="fas fa-layer-group"></i> Turmas
        </li>
        <li class="<?php echo (isset($currentPage) && $currentPage === 'history') ? 'active' : ''; ?>" onclick="window.location.href='history.php'">
            <i class="fas fa-history"></i> Histórico
        </li>
        <li onclick="openExportModal()">
            <i class="fas fa-file-excel"></i> Exportar
        </li>
    </ul>
</nav>
