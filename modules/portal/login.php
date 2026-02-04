<?php
// modules/portal/login.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Driver App | Acceso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Rajdhani:wght@400;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --neon-primary: #00ffd5; /* Cian brillante */
            --neon-secondary: #bc13fe; /* Violeta */
            --bg-dark: #09090b;
            --glass-bg: rgba(20, 20, 25, 0.85);
        }

        body {
            background-color: var(--bg-dark);
            /* Patrón de fondo tecnológico */
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(188, 19, 254, 0.1) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, rgba(0, 255, 213, 0.1) 0%, transparent 20%),
                linear-gradient(0deg, rgba(255,255,255,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 100% 100%, 100% 100%, 40px 40px, 40px 40px;
            color: #fff;
            font-family: 'Rajdhani', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .app-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 25px;
            padding: 2.5rem 2rem;
            width: 90%;
            max-width: 380px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            position: relative;
            animation: slideUp 0.6s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Icono Animado */
        .icon-container {
            width: 80px;
            height: 80px;
            background: rgba(0, 255, 213, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem auto;
            border: 2px solid var(--neon-primary);
            box-shadow: 0 0 20px rgba(0, 255, 213, 0.3);
            position: relative;
        }

        .logo-icon {
            font-size: 35px;
            color: var(--neon-primary);
            filter: drop-shadow(0 0 5px var(--neon-primary));
        }

        h3 {
            font-family: 'Orbitron', sans-serif;
            letter-spacing: 1px;
            background: linear-gradient(90deg, #fff, var(--neon-primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Inputs Estilizados */
        .form-control {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #fff;
            height: 55px;
            font-size: 1.1rem;
            text-align: center;
            border-radius: 12px;
            font-family: 'Orbitron', sans-serif; /* Fuente tecnológica para códigos */
            letter-spacing: 2px;
            transition: all 0.3s ease;
        }

        .form-control::placeholder {
            font-family: 'Rajdhani', sans-serif;
            color: rgba(255,255,255,0.3);
            letter-spacing: normal;
            font-size: 1rem;
        }

        .form-control:focus {
            background: rgba(0, 255, 213, 0.05);
            border-color: var(--neon-primary);
            color: #fff;
            box-shadow: 0 0 15px rgba(0, 255, 213, 0.2);
            outline: none;
        }

        /* Botón Neon */
        .btn-app {
            height: 60px;
            font-family: 'Orbitron', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            border-radius: 12px;
            width: 100%;
            letter-spacing: 2px;
            background: linear-gradient(45deg, var(--neon-secondary), #9d00ff);
            border: none;
            color: white;
            box-shadow: 0 5px 20px rgba(188, 19, 254, 0.4);
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
            overflow: hidden;
        }

        .btn-app:active {
            transform: scale(0.98);
            box-shadow: 0 2px 10px rgba(188, 19, 254, 0.4);
        }

        .btn-app::after {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }
        
        .btn-app:hover::after {
            left: 100%;
        }

        /* Alerta */
        .alert-error {
            background: rgba(255, 50, 50, 0.1);
            border: 1px solid rgba(255, 50, 50, 0.3);
            color: #ff5555;
            border-radius: 10px;
            font-size: 0.9rem;
        }

        .back-link {
            text-decoration: none;
            color: rgba(255,255,255,0.3);
            font-size: 0.8rem;
            transition: color 0.3s;
        }
        .back-link:hover { color: var(--neon-primary); }

    </style>
</head>
<body>

    <div class="app-card text-center">
        
        <div class="icon-container">
            <i class="fas fa-shipping-fast logo-icon"></i>
        </div>

        <h3 class="mb-1">DRIVER PORTAL</h3>
        <p class="text-white-50 small mb-4" style="letter-spacing: 1px;">SISTEMA DE RUTA INTELIGENTE</p>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-error p-2 mb-4 d-flex align-items-center justify-content-center gap-2">
                <i class="fas fa-exclamation-circle"></i>
                <span>Acceso denegado. Verifica tu PIN.</span>
            </div>
        <?php endif; ?>

        <form action="index.php?ruta=portal/logic" method="POST">
            <input type="hidden" name="action" value="login_conductor">
            
            <div class="mb-3">
                <input type="text" name="codigo" class="form-control" placeholder="ID UNIDAD" required autocomplete="off" style="text-transform: uppercase;">
            </div>

            <div class="mb-4">
                <input type="password" name="pin" class="form-control" placeholder="PIN SECRETO" required pattern="[0-9]*" inputmode="numeric">
            </div>

            <button type="submit" class="btn btn-app">
                INICIAR MOTOR <i class="fas fa-chevron-right ms-2" style="font-size: 0.8em;"></i>
            </button>
        </form>

        <div class="mt-4 d-flex justify-content-between align-items-center px-2">
            <a href="index.php?ruta=login" class="back-link"><i class="fas fa-arrow-left me-1"></i> Admin</a>
            <small class="text-white-50" style="font-size: 0.7rem;">v2.5 SECURE</small>
        </div>
    </div>

</body>
</html>