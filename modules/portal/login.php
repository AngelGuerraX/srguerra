<?php
// modules/portal/login.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Soy Conductor - Acceso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #000; color: #fff; font-family: sans-serif; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .app-card { background: #111; border: 1px solid #333; border-radius: 20px; padding: 30px; width: 100%; max-width: 350px; }
        .form-control { background: #222; border: 1px solid #444; color: #fff; height: 50px; font-size: 18px; text-align: center; border-radius: 10px; }
        .form-control:focus { background: #333; color: #fff; border-color: #0d6efd; box-shadow: none; }
        .btn-app { height: 55px; font-size: 18px; font-weight: bold; border-radius: 12px; width: 100%; letter-spacing: 1px; }
        .logo-icon { font-size: 50px; color: #0d6efd; margin-bottom: 20px; }
    </style>
</head>
<body>

    <div class="app-card text-center">
        <div class="logo-icon"><i class="fas fa-shipping-fast"></i></div>
        <h3 class="fw-bold mb-1">Modo Conductor</h3>
        <p class="text-muted small mb-4">Ingresa tus credenciales de ruta</p>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger p-2 small border-0 bg-danger bg-opacity-25 text-danger mb-3">
                Datos incorrectos. Intenta de nuevo.
            </div>
        <?php endif; ?>

        <form action="index.php?ruta=portal/logic" method="POST">
            <input type="hidden" name="action" value="login_conductor">
            
            <div class="mb-3">
                <input type="text" name="codigo" class="form-control" placeholder="CÓDIGO (Ej: METRO-01)" required autocomplete="off" style="text-transform: uppercase;">
            </div>

            <div class="mb-4">
                <input type="password" name="pin" class="form-control" placeholder="PIN SECRETO" required pattern="[0-9]*" inputmode="numeric">
            </div>

            <button type="submit" class="btn btn-primary btn-app">
                INICIAR RUTA <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </form>

        <div class="mt-4 text-muted small">
            &copy; <?php echo date('Y'); ?> El Clavito Logistics
        </div>
    </div>

</body>
</html>