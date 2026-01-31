<?php
// modules/pedidos/nuevo.php
$empresa_id = $_SESSION['empresa_id'];

// 1. OBTENER LISTAS PARA LOS DROPDOWNS
$almacenes = $pdo->query("SELECT * FROM almacenes WHERE empresa_id = $empresa_id AND activo = 1")->fetchAll();
$transportadoras = $pdo->query("SELECT * FROM transportadoras WHERE activo = 1")->fetchAll();
$clientes = $pdo->query("SELECT * FROM clientes WHERE empresa_id = $empresa_id ORDER BY id DESC LIMIT 50")->fetchAll();
$productos = $pdo->query("SELECT * FROM productos WHERE empresa_id = $empresa_id AND stock_actual > 0")->fetchAll();

// 2. OBTENER EL MAPA DE STOCK POR ALMACÉN (La magia para JS)
// Esto crea una lista de "quién tiene qué" para usarla en el script de abajo
$stock_map = [];
$q_stock = $pdo->prepare("SELECT producto_id, almacen_id, cantidad FROM inventario_almacen");
$q_stock->execute();
while ($row = $q_stock->fetch(PDO::FETCH_ASSOC)) {
    $stock_map[$row['producto_id']][$row['almacen_id']] = $row['cantidad'];
}
?>

<div class="row mb-4">
    <div class="col-12">
        <a href="index.php?ruta=pedidos" class="btn btn-outline-light btn-sm mb-3"><i class="fas fa-arrow-left"></i> Volver</a>
        <h2 class="fw-bold text-white">Crear Nuevo Pedido</h2>
    </div>
</div>

<form action="index.php?ruta=guardar-pedido" method="POST" id="formPedido">
    <input type="hidden" name="action" value="crear_pedido">
    <input type="hidden" name="csrf_token" value="<?php echo generar_csrf_token(); ?>">

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card-glass p-4 mb-4">
                <span class="h-label text-neon mb-3">CLIENTE</span>

                <div class="mb-3">
                    <label class="text-white small">Seleccionar Cliente Existente (Opcional)</label>
                    <select class="form-select bg-dark text-white border-secondary" onchange="cargarCliente(this)">
                        <option value="">-- Nuevo Cliente --</option>
                        <?php foreach ($clientes as $c): ?>
                            <option value="<?php echo $c['id']; ?>"
                                data-nombre="<?php echo $c['nombre']; ?>"
                                data-tel="<?php echo $c['telefono']; ?>"
                                data-prov="<?php echo $c['provincia']; ?>"
                                data-ciudad="<?php echo $c['ciudad']; ?>"
                                data-dir="<?php echo $c['direccion']; ?>">
                                <?php echo $c['nombre']; ?> (<?php echo $c['telefono']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="text-white small">Nombre Completo *</label>
                        <input type="text" name="cliente_nombre" id="cli_nombre" class="form-control bg-dark text-white border-secondary" required>
                    </div>
                    <div class="col-md-6">
                        <label class="text-white small">Teléfono *</label>
                        <input type="text" name="cliente_telefono" id="cli_tel" class="form-control bg-dark text-white border-secondary" required placeholder="809-000-0000">
                    </div>
                    <div class="col-md-6">
                        <label class="text-white small">Provincia *</label>
                        <select name="cliente_provincia" id="cli_prov" class="form-select bg-dark text-white border-secondary" required onchange="actualizarCiudades()">
                            <option value="">Seleccionar...</option>
                            <option value="Santo Domingo">Santo Domingo</option>
                            <option value="Distrito Nacional">Distrito Nacional</option>
                            <option value="Santiago">Santiago</option>
                            <option value="La Altagracia">La Altagracia</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="text-white small">Ciudad/Municipio *</label>
                        <input type="hidden" name="cliente_ciudad_final" id="ciudad_final">
                        <select id="select_ciudad" class="form-select bg-dark text-white border-secondary" onchange="fijarCiudad(this)">
                            <option value="">Selecciona Provincia primero</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="text-white small">Dirección Exacta *</label>
                        <input type="text" name="cliente_direccion" id="cli_dir" class="form-control bg-dark text-white border-secondary" required placeholder="Calle, Número, Referencia...">
                    </div>
                </div>
            </div>

            <div class="card-glass p-4">
                <span class="h-label text-neon mb-3">LOGÍSTICA</span>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-white small">Transportadora</label>
                        <select name="transportadora_id" id="transporte" class="form-select bg-dark text-white border-secondary" required onchange="calcularCostoEnvio()">
                            <?php foreach ($transportadoras as $t): ?>
                                <option value="<?php echo $t['id']; ?>" data-costo="<?php echo $t['costo_envio_fijo']; ?>">
                                    <?php echo $t['nombre']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="text-white small">Costo Envío (Real)</label>
                        <input type="number" name="costo_envio_real" id="costo_envio" class="form-control bg-black text-white border-secondary" readonly>
                    </div>
                    <div class="col-12">
                        <label class="text-white small">Notas Internas</label>
                        <textarea name="notas" class="form-control bg-dark text-white border-secondary" rows="2" placeholder="Ojo con el timbre..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card-glass p-4 mb-4 border border-primary border-opacity-25">
                <span class="h-label text-primary mb-3">PRODUCTO A DESPACHAR</span>

                <div class="mb-3">
                    <label class="text-white small">Producto *</label>
                    <select name="producto_id" id="producto_select" class="form-select bg-dark text-white border-secondary fs-5" required onchange="seleccionarProducto(this)">
                        <option value="" data-precio="0">-- Seleccionar --</option>
                        <option value="0" data-precio="0" class="text-warning">[ + ] Producto Manual (Sin Stock)</option>

                        <?php foreach ($productos as $p): ?>
                            <option value="<?php echo $p['id']; ?>"
                                data-precio="<?php echo $p['precio_venta']; ?>"
                                data-nombre="<?php echo $p['nombre']; ?>">
                                <?php echo $p['sku']; ?> - <?php echo $p['nombre']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="nombre_producto_texto" id="input_nombre_manual" class="form-control mt-2 bg-dark text-white d-none" placeholder="Escribe el nombre del producto...">
                </div>

                <div class="mb-3">
                    <label class="text-white small">Almacén de Origen *</label>
                    <select name="almacen_id" id="almacen_select" class="form-select bg-dark text-white border-secondary" required onchange="actualizarCostoEmpaque()">
                        <?php foreach ($almacenes as $a): ?>
                            <option value="<?php echo $a['id']; ?>"
                                data-costo="<?php echo $a['costo_empaque']; ?>"
                                data-nombre="<?php echo $a['nombre']; ?>">
                                <?php echo $a['nombre']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="costo_empaque_real" id="costo_empaque">
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="text-white small">Cantidad</label>
                        <input type="number" name="cantidad" id="cantidad" class="form-control bg-dark text-white border-secondary text-center fw-bold" value="1" min="1" onchange="calcularTotal()">
                    </div>
                    <div class="col-6">
                        <label class="text-white small">Precio Unitario</label>
                        <input type="number" name="precio_unitario" id="precio_unit" class="form-control bg-dark text-white border-secondary text-end" value="0" onchange="calcularTotal()">
                    </div>
                </div>

                <div class="p-3 bg-black bg-opacity-50 rounded border border-secondary text-center">
                    <small class="text-muted d-block mb-1">TOTAL A COBRAR (C.O.D)</small>
                    <input type="number" name="total_venta" id="total_venta" class="form-control bg-transparent text-white border-0 text-center display-4 fw-bold p-0" value="0" readonly>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-3 mt-3 fw-bold rounded-pill shadow-lg">
                    <i class="fas fa-check-circle me-2"></i> CONFIRMAR PEDIDO
                </button>
            </div>
        </div>
    </div>
</form>

<script>
    // 1. IMPORTAR EL MAPA DE STOCK DE PHP A JS
    const stockMap = <?php echo json_encode($stock_map); ?>;

    // Función Principal: Cuando eliges producto, actualizamos los almacenes
    function seleccionarProducto(select) {
        const opcion = select.options[select.selectedIndex];
        const precio = opcion.getAttribute('data-precio') || 0;
        const esManual = select.value == '0';
        const prodId = select.value;

        // A. Mostrar/Ocultar input manual
        const inputManual = document.getElementById('input_nombre_manual');
        if (esManual) {
            inputManual.classList.remove('d-none');
            inputManual.required = true;
        } else {
            inputManual.classList.add('d-none');
            inputManual.required = false;
            if (opcion.getAttribute('data-nombre')) inputManual.value = opcion.getAttribute('data-nombre');
        }

        // B. Poner Precio
        document.getElementById('precio_unit').value = precio;
        calcularTotal();

        // C. ACTUALIZAR DROPDOWN DE ALMACENES CON STOCK
        actualizarStockVisual(prodId);
    }

    function actualizarStockVisual(prodId) {
        const selectAlmacen = document.getElementById('almacen_select');
        const opciones = selectAlmacen.options;

        // Si es producto manual, limpiamos los paréntesis y salimos
        if (prodId == '0' || prodId == '') {
            for (let i = 0; i < opciones.length; i++) {
                let nombreOriginal = opciones[i].getAttribute('data-nombre');
                opciones[i].text = nombreOriginal;
                opciones[i].disabled = false;
            }
            return;
        }

        // Si es un producto real, buscamos su stock en el Mapa JSON
        const stockProducto = stockMap[prodId] || {}; // Objeto {id_almacen: cantidad}

        for (let i = 0; i < opciones.length; i++) {
            let almId = opciones[i].value;
            let nombreOriginal = opciones[i].getAttribute('data-nombre');

            // Obtener cantidad (si no existe en el mapa, es 0)
            let cantidad = stockProducto[almId] || 0;

            // Cambiar el texto
            if (cantidad > 0) {
                opciones[i].text = `✅ ${nombreOriginal} (Disp: ${cantidad})`;
                opciones[i].disabled = false; // Habilitar
                opciones[i].classList.remove('text-muted');
            } else {
                opciones[i].text = `❌ ${nombreOriginal} (Agotado)`;
                // Opcional: Deshabilitar para que no lo elijan por error
                // opciones[i].disabled = true; 
                opciones[i].classList.add('text-muted');
            }
        }
    }

    // ... (Resto de funciones JS: cargarCliente, calcularTotal, etc. siguen igual) ...

    function calcularTotal() {
        const cant = parseFloat(document.getElementById('cantidad').value) || 0;
        const precio = parseFloat(document.getElementById('precio_unit').value) || 0;
        document.getElementById('total_venta').value = (cant * precio).toFixed(0);
    }

    function calcularCostoEnvio() {
        const select = document.getElementById('transporte');
        const costo = select.options[select.selectedIndex].getAttribute('data-costo');
        document.getElementById('costo_envio').value = costo;
    }

    function actualizarCostoEmpaque() {
        const select = document.getElementById('almacen_select');
        const costo = select.options[select.selectedIndex].getAttribute('data-costo');
        document.getElementById('costo_empaque').value = costo;
    }

    // Inicializar al cargar
    document.addEventListener('DOMContentLoaded', function() {
        calcularCostoEnvio();
        actualizarCostoEmpaque();
    });

    // Lógica básica de ciudades (Simplificada para el ejemplo)
    function actualizarCiudades() {
        const provincia = document.getElementById('cli_prov').value;
        const selectCiudad = document.getElementById('select_ciudad');
        selectCiudad.innerHTML = ""; // Limpiar

        let ciudades = [];
        if (provincia === 'Santo Domingo') ciudades = ['Santo Domingo Este', 'Santo Domingo Norte', 'Santo Domingo Oeste'];
        else if (provincia === 'Distrito Nacional') ciudades = ['Distrito Nacional'];
        else ciudades = [provincia + ' (Centro)'];

        ciudades.forEach(c => {
            let opt = document.createElement('option');
            opt.value = c;
            opt.innerHTML = c;
            selectCiudad.appendChild(opt);
        });
        fijarCiudad(selectCiudad);
    }

    function fijarCiudad(select) {
        document.getElementById('ciudad_final').value = select.value;
    }

    function cargarCliente(select) {
        const opt = select.options[select.selectedIndex];
        if (select.value !== "") {
            document.getElementById('cli_nombre').value = opt.getAttribute('data-nombre');
            document.getElementById('cli_tel').value = opt.getAttribute('data-tel');
            document.getElementById('cli_dir').value = opt.getAttribute('data-dir');
            // Provincia y ciudad requieren lógica extra para autoseleccionarse, 
            // por simplicidad lo dejamos manual o lo llenamos si coincide.
        }
    }
</script>