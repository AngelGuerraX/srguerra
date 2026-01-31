<?php
// modules/configuracion/logic.php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    validar_csrf();

    $action = $_POST['action'];

    if ($action == 'actualizar_empresa') {
        $nombre = trim($_POST['nombre_comercial']);
        $razon = trim($_POST['razon_social']);
        $rnc = trim($_POST['rnc']);
        $tel = trim($_POST['telefono_contacto']);
        $email = trim($_POST['email_contacto']);
        $dir = trim($_POST['direccion']);
        
        // 1. MANEJO DEL LOGO (IMAGEN)
        $nombre_logo = NULL; // Por defecto no cambiamos nada
        
        // Si subieron un archivo...
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $nombre_logo = 'logo_' . $_SESSION['empresa_id'] . '_' . time() . '.' . $ext;
            
            // Crear carpeta si no existe
            if (!is_dir('uploads/logos')) {
                mkdir('uploads/logos', 0777, true);
            }
            
            // Mover archivo
            move_uploaded_file($_FILES['logo']['tmp_name'], 'uploads/logos/' . $nombre_logo);
            
            // Actualizar LOGO en BD
            $stmt = $pdo->prepare("UPDATE empresas SET logo = ? WHERE id = ?");
            $stmt->execute([$nombre_logo, $_SESSION['empresa_id']]);
        }
        // ... (código anterior de subir logo) ...

        // 2. ACTUALIZAR DATOS EN LA BD
        $shopify_secret = trim($_POST['shopify_secret']); // Nuevo campo capturado

        $sql = "UPDATE empresas SET 
                nombre_comercial = ?, 
                razon_social = ?, 
                rnc = ?, 
                telefono_contacto = ?, 
                email_contacto = ?, 
                direccion = ?,
                shopify_secret = ?  /* <--- Agregamos este campo */
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        // Asegúrate de agregar $shopify_secret al array de ejecución
        $stmt->execute([$nombre, $razon, $rnc, $tel, $email, $dir, $shopify_secret, $_SESSION['empresa_id']]);

        header("Location: index.php?ruta=configuracion&msg=guardado");
        exit();

        // 2. ACTUALIZAR DATOS DE TEXTO
        $sql = "UPDATE empresas SET 
                nombre_comercial = ?, 
                razon_social = ?, 
                rnc = ?, 
                telefono_contacto = ?, 
                email_contacto = ?, 
                direccion = ? 
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $razon, $rnc, $tel, $email, $dir, $_SESSION['empresa_id']]);

        header("Location: index.php?ruta=configuracion&msg=guardado");
        exit();
    }
}
?>