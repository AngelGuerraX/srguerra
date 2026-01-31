<?php 
// modules/inventario/index.php
// INVENTARIO CON BÚSQUEDA Y FILTROS DE STOCK

$empresa_id = $_SESSION['empresa_id'];

// ---------------------------------------------------------
// 1. CAPTURAR FILTROS
// ---------------------------------------------------------
$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';
$filtro_stock = isset($_GET['stock']) ? $_GET['stock'] : 'todos'; // todos, bajo, agotado, disponible

// ---------------------------------------------------------
// 2. CONSTRUIR CONSULTA DINÁMICA
// ---------------------------------------------------------
$sql = "SELECT * FROM productos WHERE empresa_id = ?";
$params = [$empresa_id];

// A) Filtro de Búsqueda (Nombre o SKU)
if (!empty($busqueda)) {
    $sql .= " AND (nombre LIKE ? OR sku LIKE ?)";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
}

// B) Filtro de Estado de Stock
if ($filtro_stock == 'agotado') {
    $sql .= " AND stock_actual <= 0";
} elseif ($filtro_stock == 'bajo') {
    $sql .= " AND stock_actual > 0 AND stock_actual <= 10"; // Umbral de poco stock
} elseif ($filtro_stock == 'disponible') {
    $sql .= " AND stock_actual > 0";
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();

// ---------------------------------------------------------
// 3. ESTADÍSTICAS RÁPIDAS (KPIs)
// ---------------------------------------------------------
// Contamos rápido para mostrar badges en los botones
$total_prods = count($productos);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="h-label">GESTIÓN DE STOCK</span>
        <h2 class="fw-bold text-white">Inventario</h2>
    </div>
    <a href="index.php?ruta=inventario/nuevo" class="btn btn-primary rounded-pill px-4 btn-glow">
        <i class="fas fa-plus me-2"></i> Nuevo Producto
    </a>
</div>

<div class="card-glass p-3 mb-4">
    <form action="index.php" method="GET" class="row g-3 align-items-center">
        <input type="hidden" name="ruta" value="inventario">
        
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-dark border-secondary text-muted"><i class="fas fa-search"></i></span>
                <input type="text" name="q" value="<?php echo $busqueda; ?>" class="form-control bg-dark text-white border-secondary" placeholder="Buscar por Nombre o SKU...">
                <?php if(!empty($busqueda)): ?>
                    <a href="index.php?ruta=inventario" class="btn btn-outline-secondary" title="Limpiar"><i class="fas fa-times"></i></a>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-7">
            <div class="d-flex gap-2 justify-content-md-end overflow-auto">
                
                <button type="submit" name="stock" value="todos" 
                        class="btn btn-sm rounded-pill <?php echo $filtro_stock=='todos' ? 'btn-light fw-bold' : 'btn-outline-secondary text-white'; ?>">
                    Todos
                </button>

                <button type="submit" name="stock" value="disponible" 
                        class="btn btn-sm rounded-pill <?php echo $filtro_stock=='disponible' ? 'btn-success fw-bold' : 'btn-outline-success'; ?>">
                    <i class="fas fa-check-circle me-1"></i> Disponibles
                </button>

                <button type="submit" name="stock" value="bajo" 
                        class="btn btn-sm rounded-pill <?php echo $filtro_stock=='bajo' ? 'btn-warning text-dark fw-bold' : 'btn-outline-warning'; ?>">
                    <i class="fas fa-exclamation-triangle me-1"></i> Poco Stock
                </button>

                <button type="submit" name="stock" value="agotado" 
                        class="btn btn-sm rounded-pill <?php echo $filtro_stock=='agotado' ? 'btn-danger fw-bold' : 'btn-outline-danger'; ?>">
                    <i class="fas fa-times-circle me-1"></i> Agotados
                </button>
            </div>
        </div>
    </form>
</div>

<?php if(!empty($busqueda) || $filtro_stock != 'todos'): ?>
    <div class="mb-3 text-muted small">
        <i class="fas fa-filter me-1 text-primary"></i> 
        Mostrando <b><?php echo $total_prods; ?></b> productos encontrados.
    </div>
<?php endif; ?>

<div class="row g-4">
    <?php if($total_prods > 0): ?>
        <?php foreach ($productos as $prod): ?>
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card-glass h-100 p-0 overflow-hidden position-relative group-hover border-secondary">
                    
                    <a href="index.php?ruta=inventario/editar&id=<?php echo $prod['id']; ?>" class="d-block text-decoration-none">
                        <div style="height: 200px; background: #000; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative;">
                            
                            <?php if($prod['stock_actual'] <= 0): ?>
                                <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-75 d-flex align-items-center justify-content-center">
                                    <span class="badge bg-danger fs-5 rotate-n15">¡AGOTADO!</span>
                                </div>
                            <?php endif; ?>

                            <?php if ($prod['imagen']): ?>
                                <img src="uploads/productos/<?php echo $prod['imagen']; ?>" class="w-100 h-100" style="object-fit: cover; opacity: 0.8; transition: 0.3s;">
                            <?php else: ?>
                                <i class="fas fa-box-open fs-1 text-secondary opacity-25"></i>
                            <?php endif; ?>
                        </div>
                    </a>

                    <div class="p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <small class="text-neon fw-bold d-block mb-1"><?php echo $prod['sku']; ?></small>
                                <h6 class="text-white fw-bold mb-0 text-truncate" style="max-width: 180px;" title="<?php echo $prod['nombre']; ?>">
                                    <?php echo $prod['nombre']; ?>
                                </h6>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3 p-2 rounded bg-black bg-opacity-25 border border-secondary">
                            <span class="text-muted small">Precio</span>
                            <span class="text-white fw-bold">RD$ <?php echo number_format($prod['precio_venta'], 0); ?></span>
                        </div>

                        <div class="mt-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="text-muted">Stock Global</span>
                                <?php 
                                    $color_text = 'text-white';
                                    if($prod['stock_actual'] < 10) $color_text = 'text-warning';
                                    if($prod['stock_actual'] <= 0) $color_text = 'text-danger';
                                ?>
                                <span class="<?php echo $color_text; ?> fw-bold"><?php echo $prod['stock_actual']; ?> u.</span>
                            </div>
                            
                            <div class="progress bg-dark border border-secondary" style="height: 6px;">
                                <?php 
                                    $porcentaje = min(100, ($prod['stock_actual'] > 0 ? $prod['stock_actual'] : 0)); 
                                    $color_barra = 'bg-primary';
                                    if($prod['stock_actual'] < 10) $color_barra = 'bg-warning';
                                    if($prod['stock_actual'] <= 0) $color_barra = 'bg-danger';
                                ?>
                                <div class="progress-bar <?php echo $color_barra; ?>" style="width: <?php echo $porcentaje; ?>%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="position-absolute top-0 end-0 p-2">
                        <a href="index.php?ruta=inventario/editar&id=<?php echo $prod['id']; ?>" class="btn btn-sm btn-dark rounded-circle shadow border border-secondary text-white" title="Editar / Ver Movimientos">
                            <i class="fas fa-pen small"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12 text-center py-5">
            <div class="mb-3">
                <i class="fas fa-search fs-1 text-muted opacity-25"></i>
            </div>
            <h4 class="text-white">No encontramos productos</h4>
            <p class="text-muted">Intenta cambiar los filtros o la búsqueda.</p>
            <a href="index.php?ruta=inventario" class="btn btn-outline-light btn-sm mt-2">Limpiar Filtros</a>
        </div>
    <?php endif; ?>
</div>