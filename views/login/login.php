<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SRGUERRA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-card { width: 100%; max-width: 400px; padding: 2rem; background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
        .brand-logo { text-align: center; margin-bottom: 1.5rem; font-weight: bold; color: #0d6efd; font-size: 1.5rem; }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="brand-logo">SRGUERRA</div>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger text-center p-2 small">
                <?php
                if ($_GET['error'] == 'credenciales') echo "Usuario o contraseña incorrectos.";
                if ($_GET['error'] == 'seguridad') echo "Sesión expirada por seguridad.";
                if ($_GET['error'] == 'inactivo') echo "Usuario desactivado.";
                ?>
            </div>
        <?php endif; ?>

        <form action="index.php?ruta=validar-login" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo function_exists('generar_csrf_token') ? generar_csrf_token() : ''; ?>">

            <div class="mb-3">
                <label for="email" class="form-label">Correo Electrónico</label>
                <input type="email" class="form-control" id="email" name="email" required autofocus placeholder="admin@srguerra.com">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" required placeholder="******">
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Ingresar al Sistema</button>
            </div>
        </form>

        <div class="text-center mt-3">
            <small class="text-muted">Sistema de Gestión Logística v1.0</small>
        </div>
    </div>

</body>
</html>