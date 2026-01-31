<?php
// modules/configuracion/index.php
// AJUSTES DE LA EMPRESA Y MARCA

$empresa_id = $_SESSION['empresa_id'];

// Obtener datos actuales
$stmt = $pdo->prepare("SELECT * FROM empresas WHERE id = ?");
$stmt->execute([$empresa_id]);
$empresa = $stmt->fetch();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="h-label">ADMINISTRACIÓN</span>
        <h2 class="fw-bold text-white">Configuración de Empresa</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card-glass p-4">
            <form action="index.php?ruta=configuracion/logic" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="actualizar_empresa">
                <input type="hidden" name="csrf_token" value="<?php echo generar_csrf_token(); ?>">

                <h5 class="text-white fw-bold mb-3"><i class="fas fa-id-card me-2"></i> Identidad Corporativa</h5>
                
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="small text-muted mb-1">Nombre Comercial (Marca)</label>
                        <input type="text" name="nombre_comercial" value="<?php echo $empresa['nombre_comercial']; ?>" class="form-control bg-dark text-white border-secondary" required>
                        <div class="form-text text-muted small">Este nombre aparecerá en el Dashboard y Menú.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted mb-1">Razón Social (Legal)</label>
                        <input type="text" name="razon_social" value="<?php echo $empresa['razon_social']; ?>" class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted mb-1">RNC / Identificación Fiscal</label>
                        <input type="text" name="rnc" value="<?php echo $empresa['rnc']; ?>" class="form-control bg-dark text-white border-secondary">
                    </div>
                </div>

                <hr class="border-secondary opacity-25">

                <h5 class="text-white fw-bold mb-3 mt-4"><i class="fas fa-map-marker-alt me-2"></i> Datos de Contacto (Etiquetas)</h5>
                
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="small text-muted mb-1">Teléfono Principal</label>
                        <input type="text" name="telefono_contacto" value="<?php echo $empresa['telefono_contacto']; ?>" class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted mb-1">Email de Contacto</label>
                        <input type="email" name="email_contacto" value="<?php echo $empresa['email_contacto']; ?>" class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-12">
                        <label class="small text-muted mb-1">Dirección Física (Remitente)</label>
                        <input type="text" name="direccion" value="<?php echo $empresa['direccion']; ?>" class="form-control bg-dark text-white border-secondary">
                        <div class="form-text text-muted small">Esta dirección saldrá en las etiquetas de envío como remitente.</div>
                    </div>
                </div>

                <hr class="border-secondary opacity-25">

                <h5 class="text-white fw-bold mb-3 mt-4"><i class="fas fa-image me-2"></i> Logotipo</h5>
                
                <div class="row g-3 align-items-center">
                    <div class="col-md-8">
                        <label class="small text-muted mb-1">Subir Logo (PNG/JPG)</label>
                        <input type="file" name="logo" class="form-control bg-dark text-white border-secondary" accept="image/*">
                    </div>
                    <div class="col-md-4 text-center">
                         <?php if(!empty($empresa['logo'])): ?>
                            <div class="p-2 bg-white rounded">
                                <img src="uploads/logos/<?php echo $empresa['logo']; ?>" alt="Logo Actual" style="max-height: 50px;">
                            </div>
                            <small class="text-muted d-block mt-1">Logo Actual</small>
                        <?php else: ?>
                            <div class="p-2 bg-dark border border-secondary rounded text-muted">
                                Sin Logo
                            </div>
                        <?php endif; ?><hr class="border-secondary opacity-25">

                <h5 class="text-white fw-bold mb-3 mt-4"><i class="fab fa-shopify me-2"></i> Integración Shopify</h5>

                <div class="alert alert-dark border border-secondary p-3 mb-3">
                    <div class="d-flex">
                        <div class="me-3"><i class="fas fa-link text-success fs-3"></i></div>
                        <div>
                            <h6 class="text-white fw-bold mb-1">Tu URL de Webhook</h6>
                            <p class="text-muted small mb-2">Copia esta URL y pégala en Shopify > Configuración > Notificaciones > Webhooks.</p>
                            
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control bg-black text-warning border-secondary" 
                                       value="https://tudominio.com/webhook_shopify.php?uid=<?php echo $empresa_id; ?>" readonly id="webhookUrl">
                                <button class="btn btn-outline-secondary" type="button" onclick="copiarUrl()">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="small text-muted mb-1">Clave de Firma (Signing Secret)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-muted"><i class="fas fa-key"></i></span>
                        <input type="password" name="shopify_secret" id="secretInput"
                               value="<?php echo isset($empresa['shopify_secret']) ? $empresa['shopify_secret'] : ''; ?>" 
                               class="form-control bg-dark text-white border-secondary" placeholder="shpss_...">
                        <button class="btn btn-outline-secondary" type="button" onclick="toggleSecret()">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="form-text text-muted small">
                        Pega aquí la clave que te da Shopify al crear el webhook.
                    </div>
                </div>

                <script>
                function toggleSecret() {
                    var x = document.getElementById("secretInput");
                    x.type = (x.type === "password") ? "text" : "password";
                }
                function copiarUrl() {
                    var copyText = document.getElementById("webhookUrl");
                    copyText.select();
                    document.execCommand("copy");
                    alert("URL copiada al portapapeles: " + copyText.value);
                }
                </script>
                    </div>
                </div>

                <div class="mt-5 text-end">
                    <button type="submit" class="btn btn-primary fw-bold px-5 py-2 shadow">
                        <i class="fas fa-save me-2"></i> Guardar Cambios
                    </button>
                </div>

            </form>
        </div>
    </div>

    <div class="col-md-4">
        <h6 class="text-muted text-uppercase mb-3">Vista Previa Etiqueta</h6>
        
        <div class="bg-white p-3 text-dark rounded shadow-sm" style="font-family: 'Courier New', monospace;">
            <div class="border-bottom border-dark pb-2 mb-2 text-center">
                <?php if(!empty($empresa['logo'])): ?>
                    <img src="uploads/logos/<?php echo $empresa['logo']; ?>" style="max-height: 40px;" class="mb-2">
                    <br>
                <?php endif; ?>
                <strong style="font-size: 18px;"><?php echo strtoupper($empresa['nombre_comercial'] ?: 'TU EMPRESA'); ?></strong>
            </div>
            <div style="font-size: 12px;">
                <strong>REMITENTE:</strong><br>
                <?php echo $empresa['direccion'] ?: 'Calle Principal #123'; ?><br>
                Tel: <?php echo $empresa['telefono_contacto'] ?: '809-555-5555'; ?>
            </div>
            <div class="my-3 border border-dark p-2 text-center">
                <strong style="font-size: 14px;">DESTINATARIO:</strong><br>
                Juan Pérez<br>
                Santo Domingo
            </div>
            <div class="bg-black text-white text-center py-1">
                COD: RD$ 2,500
            </div>
        </div>
        
        <div class="alert alert-dark border-secondary mt-4 small">
            <i class="fas fa-info-circle text-info me-2"></i>
            Mantén estos datos actualizados para que tus etiquetas de envío (Metro Pac, Vimenpaq) salgan profesionales.
        </div>
    </div>
</div>