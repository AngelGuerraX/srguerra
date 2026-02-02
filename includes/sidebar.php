<div class="sidebar d-flex flex-column flex-shrink-0 p-3" id="mainSidebar" style="width: 260px; z-index: 1070;">

    <div class="d-flex justify-content-between align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <a href="index.php?ruta=dashboard" class="text-white text-decoration-none">
            <span class="fs-4 fw-bold text-neon" style="letter-spacing: 2px;">SRGUERRA</span>
        </a>
        <button class="btn btn-link text-white d-lg-none" id="sidebarClose">
            <i class="fas fa-times fs-4"></i>
        </button>
    </div>

    <hr class="border-secondary opacity-50">

    <?php 
    // Capturar ruta actual para marcar activo
    $ruta_actual = $_GET['ruta'] ?? 'dashboard';
    ?>

    <ul class="nav nav-pills flex-column mb-auto" style="position: relative; z-index: 1060;">
        
        <li class="nav-item">
            <a href="index.php?ruta=dashboard" class="nav-link <?php echo ($ruta_actual == 'dashboard') ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt me-2 text-center" style="width: 25px;"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="index.php?ruta=pedidos" class="nav-link <?php echo (strpos($ruta_actual, 'pedidos') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-box me-2 text-center" style="width: 25px;"></i> Pedidos
            </a>
        </li>        
        <li class="nav-item">
            <a href="index.php?ruta=pedidos/despacho" class="nav-link text-white">
                <i class="fas fa-dolly me-2"></i> Despacho Masivo
            </a>
        </li>        
        <li class="nav-item"> 
            <a href="index.php?ruta=inventario" class="nav-link <?php echo ($ruta_actual == 'inventario') ? 'active' : ''; ?>">
                <i class="fas fa-boxes me-2 text-center" style="width: 25px;"></i> Inventario
            </a>
        </li>
        <li class="nav-item">
            <a href="index.php?ruta=almacenes" class="nav-link <?php echo ($ruta_actual == 'almacenes') ? 'active' : ''; ?>">
                <i class="fas fa-warehouse me-2 text-center" style="width: 25px;"></i> Almacenes
            </a>
        </li>
        <li class="nav-item">        
            <a href="index.php?ruta=transportadoras" class="nav-link <?php echo ($ruta_actual == 'transportadoras') ? 'active' : ''; ?>">
                <i class="fas fa-shipping-fast me-2 text-center" style="width: 25px;"></i> Transportadoras
            </a>
        </li> 
        <li class="nav-item">
            <a href="index.php?ruta=logistica" class="nav-link <?php echo ($ruta_actual == 'logistica') ? 'active' : ''; ?>">
                <i class="fas fa-map-marked-alt me-2 text-center" style="width: 25px;"></i> Rutas
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($ruta_actual == 'clientes') ? 'active' : ''; ?>" href="index.php?ruta=clientes">
                <i class="fas fa-users me-2 text-center" style="width: 25px;"></i> Clientes
            </a>
        </li>

        <span class="px-3 h-label mb-2 mt-4 text-white-50 small fw-bold">NEGOCIO</span>

        <li class="nav-item">
            <a href="index.php?ruta=finanzas" class="nav-link <?php echo ($ruta_actual == 'finanzas') ? 'active' : ''; ?>">
                <i class="fas fa-chart-line me-2 text-center" style="width: 25px;"></i> Finanzas Global
            </a>
        </li>
        <li class="nav-item">
            <a href="index.php?ruta=finanzas/productos" class="nav-link <?php echo ($ruta_actual == 'finanzas/productos') ? 'active' : ''; ?>">
                <i class="fas fa-tags me-2 text-center" style="width: 25px;"></i> Rentabilidad Prod.
            </a>
        </li>
        <li class="nav-item">
            <a href="index.php?ruta=finanzas/gastos" class="nav-link <?php echo ($ruta_actual == 'finanzas/gastos') ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice-dollar me-2 text-center" style="width: 25px;"></i> Gastos
            </a>
        </li>
        <li class="nav-item">
            <a href="index.php?ruta=marketing" class="nav-link <?php echo ($ruta_actual == 'marketing') ? 'active' : ''; ?>">
                <i class="fab fa-facebook me-2 text-center" style="width: 25px;"></i> Marketing Ads
            </a>
        </li>
</li>

        <hr class="border-secondary opacity-50 my-3">

        <li class="nav-item">
            <a class="nav-link text-warning <?php echo ($ruta_actual == 'configuracion') ? 'active' : ''; ?>" href="index.php?ruta=configuracion">
                <i class="fas fa-cog me-2 text-center" style="width: 25px;"></i> Ajustes
            </a>
        </li>
        <?php if ($_SESSION['rol'] === 'SuperAdmin'): ?>
    <hr class="border-secondary opacity-50 my-3">
    <li class="nav-item">
        <a class="nav-link text-warning fw-bold <?php echo ($ruta_actual == 'saas') ? 'active' : ''; ?>" href="index.php?ruta=saas">
            <i class="fas fa-user-astronaut me-2 text-center" style="width: 25px;"></i> Gestión SaaS
        </a>
    </li>
<?php endif; ?>
    </ul>

    <hr class="border-secondary opacity-50">

    <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-muted text-decoration-none small">
            <i class="fas fa-code-branch me-2"></i>
            <strong>v1.0.6 SaaS</strong>
        </a>
    </div>
</div>