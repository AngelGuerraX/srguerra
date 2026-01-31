<div class="sidebar d-flex flex-column flex-shrink-0 p-3" id="mainSidebar" style="width: 260px; z-index: 1070;">

    <div class="d-flex justify-content-between align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <a href="dashboard" class="text-white text-decoration-none">
            <span class="fs-4 fw-bold text-neon" style="letter-spacing: 2px;">SRGUERRA</span>
        </a>
        <button class="btn btn-link text-white d-lg-none" id="sidebarClose">
            <i class="fas fa-times fs-4"></i>
        </button>
    </div>

    <hr class="border-secondary opacity-50">

    <ul class="nav nav-pills flex-column mb-auto" style="position: relative; z-index: 1060;">
        <li class="nav-item">
            <a href="dashboard" class="nav-link <?php echo ($partes[0] == 'dashboard') ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt me-3" style="width: 20px;"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="pedidos" class="nav-link <?php echo ($partes[0] == 'pedidos') ? 'active' : ''; ?>">
                <i class="fas fa-box me-3" style="width: 20px;"></i> Pedidos
            </a>
        </li>
        <li> 
            <a href="inventario" class="nav-link <?php echo ($partes[0] == 'inventario') ? 'active' : ''; ?>">
                <i class="fas fa-boxes me-3" style="width: 20px;"></i> Inventario
            </a>
        </li>
        <li>        
            <a href="transportadoras" class="nav-link <?php echo ($partes[0] == 'transportadoras') ? 'active' : ''; ?>">
                <i class="fas fa-shipping-fast me-3" style="width: 20px;"></i> Transportadoras
            </a>
        </li> 
        <li>
            <a class="nav-link <?php echo ($partes[0] == 'almacenes') ? 'active' : ''; ?>" href="index.php?ruta=almacenes">
                <i class="fas fa-warehouse me-1"></i> Almacenes
            </a>
        </li>
        <li>
            <a href="logistica" class="nav-link <?php echo ($partes[0] == 'logistica') ? 'active' : ''; ?>">
                <i class="fas fa-truck me-3" style="width: 20px;"></i> Rutas
            </a>
        </li>
        <li>
            <a href="finanzas" class="nav-link <?php echo ($partes[0] == 'finanzas') ? 'active' : ''; ?>">
                <i class="fas fa-wallet me-3" style="width: 20px;"></i> Finanzas
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="index.php?ruta=clientes"><i class="fas fa-users me-1"></i> Clientes</a>
        </li>
        <li class="nav-item ms-lg-3">
    <div class="vr h-100 bg-secondary mx-2 d-none d-lg-block"></div>
    <hr class="d-lg-none text-white opacity-25">
</li>

<li class="nav-item">
    <a class="nav-link text-warning" href="index.php?ruta=configuracion">
        <i class="fas fa-cog me-1"></i> Ajustes
    </a>
</li>
    </ul>

    <hr class="border-secondary opacity-50">

    <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-muted text-decoration-none small">
            <i class="fas fa-code-branch me-2"></i>
            <strong>v1.0.5 SaaS</strong>
        </a>
    </div>
</div>