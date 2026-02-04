<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Neural | SRGUERRA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Rajdhani:wght@300;500;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --neon-blue: #00f3ff;
            --neon-purple: #bc13fe;
            --dark-bg: #050505;
        }

        body {
            background-color: var(--dark-bg);
            /* Fondo de rejilla futurista animado */
            background-image: 
                linear-gradient(rgba(0, 243, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 243, 255, 0.03) 1px, transparent 1px);
            background-size: 30px 30px;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Rajdhani', sans-serif;
            overflow: hidden;
            position: relative;
        }

        /* Efecto de luz de fondo */
        body::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: var(--neon-purple);
            filter: blur(150px);
            border-radius: 50%;
            top: 10%;
            left: 20%;
            opacity: 0.4;
            animation: float 6s infinite alternate;
        }
        body::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: var(--neon-blue);
            filter: blur(150px);
            border-radius: 50%;
            bottom: 10%;
            right: 20%;
            opacity: 0.4;
            animation: float 6s infinite alternate-reverse;
        }

        @keyframes float {
            0% { transform: translate(0, 0); }
            100% { transform: translate(20px, 40px); }
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            padding: 2.5rem;
            background: rgba(20, 20, 20, 0.6);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            box-shadow: 0 0 40px rgba(0, 243, 255, 0.1);
            position: relative;
            z-index: 10;
        }

        .brand-logo {
            text-align: center;
            margin-bottom: 2rem;
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            font-size: 2rem;
            color: #fff;
            text-shadow: 0 0 10px var(--neon-blue);
            letter-spacing: 2px;
        }

        /* Inputs Futuristas */
        .form-control {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid #333;
            color: #fff;
            border-radius: 8px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(0, 243, 255, 0.05);
            border-color: var(--neon-blue);
            box-shadow: 0 0 15px rgba(0, 243, 255, 0.3);
            color: #fff;
        }

        .form-label {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        /* Botón Principal */
        .btn-neon {
            background: transparent;
            color: var(--neon-blue);
            border: 1px solid var(--neon-blue);
            padding: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
            box-shadow: 0 0 10px rgba(0, 243, 255, 0.2);
        }

        .btn-neon:hover {
            background: var(--neon-blue);
            color: #000;
            box-shadow: 0 0 25px var(--neon-blue);
        }

        /* Botón Transportista */
        .btn-transporter {
            margin-top: 15px;
            background: transparent;
            color: var(--neon-purple);
            border: 1px solid var(--neon-purple);
            padding: 10px;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
            text-decoration: none;
            display: block;
            text-align: center;
            border-radius: 8px;
        }

        .btn-transporter:hover {
            background: var(--neon-purple);
            color: #fff;
            box-shadow: 0 0 20px var(--neon-purple);
        }

        /* Separador */
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.5rem 0;
            color: rgba(255,255,255,0.3);
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .divider span {
            padding: 0 10px;
            font-size: 0.8rem;
        }

        .alert-custom {
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid #dc3545;
            color: #ff6b6b;
            text-shadow: 0 0 5px rgba(220, 53, 69, 0.5);
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="brand-logo">
            <i class="fas fa-cube me-2"></i>SRGUERRA
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-custom text-center p-2 mb-4 small rounded">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php
                if ($_GET['error'] == 'credenciales') echo "ACCESO DENEGADO: Credenciales inválidas.";
                if ($_GET['error'] == 'seguridad') echo "TIEMPO EXCEDIDO: Sesión cerrada.";
                if ($_GET['error'] == 'inactivo') echo "USUARIO DESACTIVADO.";
                ?>
            </div>
        <?php endif; ?>

        <form action="index.php?ruta=validar-login" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo function_exists('generar_csrf_token') ? generar_csrf_token() : ''; ?>">

            <div class="mb-4">
                <label for="email" class="form-label"><i class="fas fa-envelope me-2"></i>IDENTIFICADOR</label>
                <input type="email" class="form-control" id="email" name="email" required autofocus placeholder="usuario@srguerra.com">
            </div>
            
            <div class="mb-4">
                <label for="password" class="form-label"><i class="fas fa-lock me-2"></i>CÓDIGO DE ACCESO</label>
                <input type="password" class="form-control" id="password" name="password" required placeholder="••••••••">
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-neon">
                    INICIAR SISTEMA <i class="fas fa-arrow-right ms-2"></i>
                </button>
            </div>
        </form>

        <div class="divider">
            <span>O ACCEDE COMO</span>
        </div>

        <a href="index.php?ruta=portal/login" class="btn-transporter">
            <i class="fas fa-truck-fast me-2"></i> Portal Transportista
        </a>

        <div class="text-center mt-4">
            <small class="text-white-50" style="font-size: 0.7rem; letter-spacing: 1px;">
                SYSTEM VERSION 2.0 // SECURE CONNECTION
            </small>
        </div>
    </div>

</body>
</html>