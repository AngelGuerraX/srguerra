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
    // Capturar ruta actual
    $ruta_actual = $_GET['ruta'] ?? 'dashboard';
    
    // DEFINIR ROLES (Lógica de Permisos)
    $rol = $_SESSION['rol'] ?? '';
    $es_admin    = in_array($rol, ['SuperAdmin', 'Admin']); 
    $es_vendedor = ($rol == 'Vendedor');
    $es_almacen  = ($rol == 'Almacen');
    $es_super    = ($rol == 'SuperAdmin');
    ?>

    <ul class="nav nav-pills flex-column mb-auto" style="position: relative; z-index: 1060;">
        
        <li class="nav-item">
            <a href="index.php?ruta=dashboard" class="nav-link <?php echo ($ruta_actual == 'dashboard') ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt me-2 text-center" style="width: 25px;"></i> Dashboard
            </a>
        </li>

        <?php if ($es_admin || $es_vendedor || $es_almacen): ?>
        <li class="nav-item">
            <a href="index.php?ruta=pedidos" class="nav-link <?php echo (strpos($ruta_actual, 'pedidos') !== false && strpos($ruta_actual, 'despacho') === false) ? 'active' : ''; ?>">
                <i class="fas fa-box me-2 text-center" style="width: 25px;"></i> Pedidos
            </a>
        </li>        
        <?php endif; ?>

        <?php if ($es_admin || $es_almacen): ?>
        <li class="nav-item">
            <a href="index.php?ruta=pedidos/despacho" class="nav-link text-white <?php echo (strpos($ruta_actual, 'despacho') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-dolly me-2 text-center" style="width: 25px;"></i> Despacho Masivo
            </a>
        </li>    
        <?php endif; ?>
            
        <?php if ($es_admin || $es_almacen): ?>
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
        <?php endif; ?>

        <?php if ($es_admin || $es_almacen): ?>
        <li class="nav-item">        
            <a href="index.php?ruta=transportadoras" class="nav-link <?php echo ($ruta_actual == 'transportadoras') ? 'active' : ''; ?>">
                <i class="fas fa-shipping-fast me-2 text-center" style="width: 25px;"></i> Transportadoras
            </a>
        </li> 
        <li class="nav-item">
            <a href="index.php?ruta=transportadoras/pagos" class="nav-link text-white">
                <i class="fas fa-money-check-alt me-2"></i> Liquidaciones
            </a>
        </li>
        <li class="nav-item">
            <a href="index.php?ruta=logistica" class="nav-link <?php echo ($ruta_actual == 'logistica') ? 'active' : ''; ?>">
                <i class="fas fa-map-marked-alt me-2 text-center" style="width: 25px;"></i> Rutas
            </a>
        </li>
        <?php endif; ?>

        <?php if ($es_admin || $es_vendedor): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo ($ruta_actual == 'clientes') ? 'active' : ''; ?>" href="index.php?ruta=clientes">
                <i class="fas fa-users me-2 text-center" style="width: 25px;"></i> Clientes
            </a>
        </li>
        <?php endif; ?>

        <?php if ($es_admin): ?>
        <li class="nav-item mb-2 mt-2">
            <a href="#finanzasSubmenu" data-bs-toggle="collapse" class="nav-link text-white align-middle px-0">
                <i class="fs-4 fas fa-hand-holding-usd text-warning text-center" style="width: 25px;"></i> <span class="ms-1">Finanzas</span>
            </a>
            <ul class="collapse nav flex-column ms-3 ps-2 border-start border-secondary" id="finanzasSubmenu" data-bs-parent="#menu">
                
                <li class="nav-item w-100">
                    <a href="index.php?ruta=finanzas" class="nav-link px-0 text-white-50 <?php echo ($ruta_actual == 'finanzas') ? 'text-white fw-bold' : ''; ?>"> 
                        <i class="fas fa-file-invoice me-2"></i> Reporte General
                    </a>
                </li>

                <li class="nav-item w-100">
                    <a href="index.php?ruta=finanzas/gastos" class="nav-link px-0 text-white-50 <?php echo ($ruta_actual == 'finanzas/gastos') ? 'text-white fw-bold' : ''; ?>">
                        <i class="fas fa-receipt me-2"></i> Registrar Gastos
                    </a>
                </li>

                <li class="nav-item w-100">
                    <a href="index.php?ruta=finanzas/rentabilidad" class="nav-link px-0 text-warning fw-bold <?php echo ($ruta_actual == 'finanzas/rentabilidad') ? 'text-white' : ''; ?>">
                        <i class="fas fa-chart-pie me-2"></i> Rentabilidad / CPA
                    </a>
                </li>

            </ul>
        </li> 
        <?php endif; ?>

        <hr class="border-secondary opacity-50 my-3">

        <?php if ($es_admin): ?>
        <li class="nav-item">
            <a class="nav-link text-warning <?php echo ($ruta_actual == 'configuracion') ? 'active' : ''; ?>" href="index.php?ruta=configuracion">
                <i class="fas fa-cog me-2 text-center" style="width: 25px;"></i> Ajustes
            </a>
        </li>
        <?php endif; ?>

        <?php if ($es_super): ?>
        <li class="nav-item">
            <a class="nav-link text-warning fw-bold border border-warning rounded mt-2 <?php echo ($ruta_actual == 'saas') ? 'active' : ''; ?>" href="index.php?ruta=saas">
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