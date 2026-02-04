<?php
// modules/pedidos/nuevo.php
$empresa_id = $_SESSION['empresa_id'];

// 1. OBTENER LISTAS PARA LOS DROPDOWNS
$almacenes = $pdo->query("SELECT * FROM almacenes WHERE empresa_id = $empresa_id AND activo = 1")->fetchAll();
$transportadoras = $pdo->query("SELECT * FROM transportadoras WHERE activo = 1 AND (empresa_id = $empresa_id OR es_publica = 1) ORDER BY es_publica DESC, nombre ASC")->fetchAll();
$clientes = $pdo->query("SELECT * FROM clientes WHERE empresa_id = $empresa_id ORDER BY id DESC LIMIT 50")->fetchAll();
$productos = $pdo->query("SELECT * FROM productos WHERE empresa_id = $empresa_id AND stock_actual > 0")->fetchAll();

// 2. OBTENER EL MAPA DE STOCK POR ALMACÉN
$stock_map = [];
$q_stock = $pdo->prepare("SELECT producto_id, almacen_id, cantidad FROM inventario_almacen");
$q_stock->execute();
while ($row = $q_stock->fetch(PDO::FETCH_ASSOC)) {
    $stock_map[$row['producto_id']][$row['almacen_id']] = $row['cantidad'];
}
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
    /* Ajuste oscuro para Select2 */
    .select2-container--bootstrap-5 .select2-selection {
        background-color: #212529 !important;
        border-color: #6c757d !important;
        color: #fff !important;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        color: #fff !important;
    }
    .select2-search__field {
        background-color: #212529 !important;
        color: #fff !important;
    }
    .select2-results__option {
        background-color: #212529;
        color: #fff;
    }
    .select2-results__option--highlighted {
        background-color: #0d6efd !important; 
    }
</style>

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
                    <select class="form-select bg-dark text-white border-secondary" id="select_cliente_existente" onchange="cargarCliente(this)">
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
                            <option value="">Seleccionar Provincia...</option>
                            <option value="Azua">Azua</option>
                            <option value="Baoruco">Baoruco</option>
                            <option value="Barahona">Barahona</option>
                            <option value="Dajabón">Dajabón</option>
                            <option value="Distrito Nacional">Distrito Nacional</option>
                            <option value="Duarte">Duarte</option>
                            <option value="Elías Piña">Elías Piña</option>
                            <option value="El Seibo">El Seibo</option>
                            <option value="Espaillat">Espaillat</option>
                            <option value="Hato Mayor">Hato Mayor</option>
                            <option value="Hermanas Mirabal">Hermanas Mirabal</option>
                            <option value="Independencia">Independencia</option>
                            <option value="La Altagracia">La Altagracia</option>
                            <option value="La Romana">La Romana</option>
                            <option value="La Vega">La Vega</option>
                            <option value="María Trinidad Sánchez">María Trinidad Sánchez</option>
                            <option value="Monseñor Nouel">Monseñor Nouel</option>
                            <option value="Monte Cristi">Monte Cristi</option>
                            <option value="Monte Plata">Monte Plata</option>
                            <option value="Pedernales">Pedernales</option>
                            <option value="Peravia">Peravia</option>
                            <option value="Puerto Plata">Puerto Plata</option>
                            <option value="Samaná">Samaná</option>
                            <option value="San Cristóbal">San Cristóbal</option>
                            <option value="San José de Ocoa">San José de Ocoa</option>
                            <option value="San Juan">San Juan</option>
                            <option value="San Pedro de Macorís">San Pedro de Macorís</option>
                            <option value="Sánchez Ramírez">Sánchez Ramírez</option>
                            <option value="Santiago">Santiago</option>
                            <option value="Santiago Rodríguez">Santiago Rodríguez</option>
                            <option value="Santo Domingo">Santo Domingo</option>
                            <option value="Valverde">Valverde</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="text-white small">Ciudad/Municipio *</label>
                        <input type="hidden" name="cliente_ciudad_final" id="ciudad_final">
                        <select id="select_ciudad" class="form-select bg-dark text-white border-secondary" onchange="fijarCiudad(this)" required>
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
                <span class="h-label text-neon mb-3">LOGÍSTICA (Opcional)</span>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-white small">Transportadora</label>
                        <select name="transportadora_id" class="form-select bg-dark text-white border-secondary">
                            <option value="">Seleccionar...</option>
                            <?php foreach($transportadoras as $t): ?>
                                <option value="<?php echo $t['id']; ?>">
                                    <?php 
                                        // Agregamos un prefijo visual
                                        echo ($t['es_publica'] == 1) ? '🌎 Publico - ' : '👤 '; 
                                        echo $t['nombre']; 
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="text-white small">Costo Envío (Real)</label>
                        <input type="number" name="costo_envio_real" id="costo_envio" class="form-control bg-black text-white border-secondary" value="0" readonly>
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
                    <select name="producto_id" id="producto_select" class="form-select bg-dark text-white border-secondary fs-5" required>
                        <option value="" data-precio="0">-- Buscar Producto --</option>
                        <option value="0" data-precio="0">[ + ] Producto Manual (Sin Stock)</option>

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
                    <label class="text-white small">Almacén de Origen</label>
                    <select name="almacen_id" id="almacen_select" class="form-select bg-dark text-white border-secondary" onchange="actualizarCostoEmpaque()">
                        <option value="" data-costo="0">-- Sin Asignar (No descuenta stock) --</option>
                        <?php foreach ($almacenes as $a): ?>
                            <option value="<?php echo $a['id']; ?>"
                                data-costo="<?php echo $a['costo_empaque']; ?>"
                                data-nombre="<?php echo $a['nombre']; ?>">
                                <?php echo $a['nombre']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="costo_empaque_real" id="costo_empaque" value="0">
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
                    <i class="fas fa-check-circle me-2"></i> CREAR PEDIDO
                </button>
            </div>
        </div>
    </div>
</form>

<script>
    // 1. IMPORTAR EL MAPA DE STOCK DE PHP A JS
    const stockMap = <?php echo json_encode($stock_map); ?>;

    // === INICIALIZACIÓN DE SELECT2 (BUSCADOR) ===
    $(document).ready(function() {
        $('#producto_select').select2({
            theme: "bootstrap-5",
            width: '100%',
            placeholder: '-- Buscar Producto --'
        });

        // Vincular el evento change de Select2 con nuestra función
        $('#producto_select').on('change', function() {
            seleccionarProducto(this);
        });
    });

    // === BASE DE DATOS GEOGRÁFICA RD (COMPLETA) ===
    const datosRD = {
        "Azua": ["Azua de Compostela", "Estebanía", "Guayabal", "Las Charcas", "Las Yayas de Viajama", "Padre Las Casas", "Peralta", "Pueblo Viejo", "Sabana Yegua", "Tábara Arriba"],
        "Baoruco": ["Neiba", "Galván", "Los Ríos", "Tamayo", "Villa Jaragua"],
        "Barahona": ["Barahona", "Cabral", "El Peñón", "Enriquillo", "Fundación", "Jaquimeyes", "La Ciénaga", "Las Salinas", "Paraíso", "Polo", "Vicente Noble"],
        "Dajabón": ["Dajabón", "El Pino", "Loma de Cabrera", "Partid,o", "Restauración"],
        "Distrito Nacional": ["Distrito Nacional"],
        "Duarte": ["San Francisco de Macorís", "Arenoso", "Castillo", "Eugenio María de Hostos", "Las Guáranas", "Pimentel", "Villa Riva"],
        "El Seibo": ["El Seibo", "Miches"],
        "Elías Piña": ["Comendador", "Bánica", "El Llano", "Hondo Valle", "Juan Santiago", "Pedro Santana"],
        "Espaillat": ["Moca", "Cayetano Germosén", "Gaspar Hernández", "Jamao al Norte"],
        "Hato Mayor": ["Hato Mayor del Rey", "El Valle", "Sabana de la Mar"],
        "Hermanas Mirabal": ["Salcedo", "Tenares", "Villa Tapia"],
        "Independencia": ["Jimaní", "Cristóbal", "Duvergé", "La Descubierta", "Mella", "Postrer Río"],
        "La Altagracia": ["Higüey", "San Rafael del Yuma"],
        "La Romana": ["La Romana", "Guaymate", "Villa Hermosa"],
        "La Vega": ["La Vega", "Constanza", "Jarabacoa", "Jima Abajo"],
        "María Trinidad Sánchez": ["Nagua", "Cabrera", "El Factor", "Río San Juan"],
        "Monseñor Nouel": ["Bonao", "Maimón", "Piedra Blanca"],
        "Monte Cristi": ["Monte Cristi", "Castañuelas", "Guayubín", "Las Matas de Santa Cruz", "Pepillo Salcedo", "Villa Vásquez"],
        "Monte Plata": ["Monte Plata", "Bayaguana", "Peralvillo", "Sabana Grande de Boyá", "Yamasá"],
        "Pedernales": ["Pedernales", "Oviedo"],
        "Peravia": ["Baní", "Nizao"],
        "Puerto Plata": ["Puerto Plata", "Altamira", "Guananico", "Imbert", "Los Hidalgos", "Luperón", "Sosúa", "Villa Isabela", "Villa Montellano"],
        "Samaná": ["Samaná", "Las Terrenas", "Sánchez"],
        "San Cristóbal": ["San Cristóbal", "Bajos de Haina", "Cambita Garabitos", "Los Cacaos", "Sabana Grande de Palenque", "San Gregorio de Nigua", "Villa Altagracia", "Yaguate"],
        "San José de Ocoa": ["San José de Ocoa", "Rancho Arriba", "Sabana Larga"],
        "San Juan": ["San Juan de la Maguana", "Bohechío", "El Cercado", "Juan de Herrera", "Las Matas de Farfán", "Vallejuelo"],
        "San Pedro de Macorís": ["San Pedro de Macorís", "Consuelo", "Guayacanes", "Quisqueya", "Ramón Santana", "San José de los Llanos"],
        "Sánchez Ramírez": ["Cotuí", "Cevicos", "Fantino", "La Mata"],
        "Santiago": ["Santiago", "Bisonó", "Jánico", "Licey al Medio", "Puñal", "Sabana Iglesia", "San José de las Matas", "Tamboril", "Villa González"],
        "Santiago Rodríguez": ["Sabaneta", "Los Almácigos", "Monción"],
        "Santo Domingo": ["Santo Domingo Este", "Santo Domingo Oeste", "Santo Domingo Norte", "Boca Chica", "San Antonio de Guerra", "Los Alcarrizos", "Pedro Brand"],
        "Valverde": ["Mao", "Esperanza", "Laguna Salada"]
    };

    function actualizarCiudades() {
        const selectProvincia = document.getElementById('cli_prov');
        const selectCiudad = document.getElementById('select_ciudad');
        const provinciaSeleccionada = selectProvincia.value;

        selectCiudad.innerHTML = "<option value=''>Selecciona Ciudad...</option>";

        if (provinciaSeleccionada && datosRD[provinciaSeleccionada]) {
            const ciudades = datosRD[provinciaSeleccionada];
            ciudades.sort();
            ciudades.forEach(ciudad => {
                let opt = document.createElement('option');
                opt.value = ciudad;
                opt.innerHTML = ciudad;
                selectCiudad.appendChild(opt);
            });
        }
        document.getElementById('ciudad_final').value = "";
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
            
            const prov = opt.getAttribute('data-prov');
            const ciudad = opt.getAttribute('data-ciudad');

            const selectProv = document.getElementById('cli_prov');
            selectProv.value = prov;
            
            if (selectProv.value === prov) {
                actualizarCiudades();
                const selectCiudad = document.getElementById('select_ciudad');
                selectCiudad.value = ciudad;
                fijarCiudad(selectCiudad);
            }
        }
    }

    // --- LÓGICA DE PRODUCTOS Y COSTOS ---

    function seleccionarProducto(select) {
        const opcion = select.options[select.selectedIndex];
        const precio = opcion.getAttribute('data-precio') || 0;
        const esManual = select.value == '0';
        const prodId = select.value;

        const inputManual = document.getElementById('input_nombre_manual');
        if (esManual) {
            inputManual.classList.remove('d-none');
            inputManual.required = true;
        } else {
            inputManual.classList.add('d-none');
            inputManual.required = false;
            if (opcion.getAttribute('data-nombre')) inputManual.value = opcion.getAttribute('data-nombre');
        }

        document.getElementById('precio_unit').value = precio;
        calcularTotal();
        actualizarStockVisual(prodId);
    }

    function actualizarStockVisual(prodId) {
        const selectAlmacen = document.getElementById('almacen_select');
        const opciones = selectAlmacen.options;

        // Resetear visualmente si no hay producto
        if (prodId == '0' || prodId == '') {
            for (let i = 0; i < opciones.length; i++) {
                if (opciones[i].value == "") continue; // Saltar el "Sin Asignar"
                let nombreOriginal = opciones[i].getAttribute('data-nombre');
                opciones[i].text = nombreOriginal;
                opciones[i].disabled = false;
                opciones[i].classList.remove('text-muted');
            }
            return;
        }

        const stockProducto = stockMap[prodId] || {}; 

        for (let i = 0; i < opciones.length; i++) {
            if (opciones[i].value == "") continue; // Saltar "Sin Asignar"

            let almId = opciones[i].value;
            let nombreOriginal = opciones[i].getAttribute('data-nombre');
            let cantidad = stockProducto[almId] || 0;

            if (cantidad > 0) {
                opciones[i].text = `✅ ${nombreOriginal} (Disp: ${cantidad})`;
                opciones[i].disabled = false; 
                opciones[i].classList.remove('text-muted');
            } else {
                opciones[i].text = `❌ ${nombreOriginal} (Agotado)`;
                // Opcional: no deshabilitar para permitir backorders, pero marcamos visualmente
                opciones[i].classList.add('text-muted');
            }
        }
    }

    function calcularTotal() {
        const cant = parseFloat(document.getElementById('cantidad').value) || 0;
        const precio = parseFloat(document.getElementById('precio_unit').value) || 0;
        document.getElementById('total_venta').value = (cant * precio).toFixed(0);
    }

    function calcularCostoEnvio() {
        const select = document.getElementById('transporte');
        // Manejar si está vacío (Sin Asignar)
        if(select.value === "") {
             document.getElementById('costo_envio').value = 0;
             return;
        }
        const costo = select.options[select.selectedIndex].getAttribute('data-costo');
        document.getElementById('costo_envio').value = costo;
    }

    function actualizarCostoEmpaque() {
        const select = document.getElementById('almacen_select');
        // Manejar si está vacío (Sin Asignar)
        if(select.value === "") {
             document.getElementById('costo_empaque').value = 0;
             return;
        }
        const costo = select.options[select.selectedIndex].getAttribute('data-costo');
        document.getElementById('costo_empaque').value = costo;
    }

    document.addEventListener('DOMContentLoaded', function() {
        calcularCostoEnvio();
        actualizarCostoEmpaque();
    });
</script>