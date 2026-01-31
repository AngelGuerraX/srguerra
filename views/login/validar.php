<?php
// views/login/validar.php
// Este archivo recibe los datos del formulario de login y verifica la BD.

// 1. Seguridad CSRF (Si existe la configuración)
if (function_exists('validar_csrf_token')) {
    if (!isset($_POST['csrf_token']) || !validar_csrf($_POST['csrf_token'])) {
        header("Location: index.php?ruta=login&error=seguridad");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // 2. Buscar al Usuario en la BD
    // ($pdo ya viene cargado desde el index.php)
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // 3. Validaciones Básicas
    if (!$user) {
        header("Location: index.php?ruta=login&error=credenciales"); // Usuario no existe
        exit();
    }

    if ($user['activo'] == 0) {
        header("Location: index.php?ruta=login&error=inactivo"); // Usuario baneado
        exit();
    }

    // 4. Verificar Contraseña
    if (password_verify($password, $user['password_hash'])) {
        
        // --- INICIAR VARIABLES DE SESIÓN ---
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['empresa_id'] = $user['empresa_id'];
        $_SESSION['email']      = $user['email'];
        $_SESSION['rol']        = $user['rol'];
        
        // Definimos ambas variables de nombre para compatibilidad
        $_SESSION['nombre']         = $user['nombre_completo'];
        $_SESSION['usuario_nombre'] = $user['nombre_completo']; 

        // Registrar último acceso
        $pdo->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?")->execute([$user['id']]);

        // ¡ÉXITO! Vamos al Dashboard
        header("Location: index.php?ruta=dashboard");
        exit();

    } else {
        header("Location: index.php?ruta=login&error=credenciales"); // Password mal
        exit();
    }

} else {
    // Si intentan abrir este archivo directo sin enviar formulario
    header("Location: index.php?ruta=login");
    exit();
}
?>