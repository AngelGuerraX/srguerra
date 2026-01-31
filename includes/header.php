<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SRGUERRA | Sistema Logístico</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="assets/css/futuristic.css" rel="stylesheet">
</head>

<body>

    <div class="d-flex">
        <?php include 'includes/sidebar.php'; ?>

        <div class="w-100 position-relative" style="height: 100vh; overflow-y: auto; overflow-x: hidden; position: relative; z-index: 1051;">

            <nav class="navbar navbar-expand-lg px-3 py-3" style="background: transparent;">
                <div class="container-fluid p-0">

                    <button class="btn btn-dark d-lg-none me-3 text-neon border-0 shadow-none" id="sidebarToggle">
                        <i class="fas fa-bars fs-4"></i>
                    </button>

                    <span class="h-label mb-0 text-neon d-none d-sm-inline">
                        <i class="fas fa-building"></i> ID: <?php echo isset($_SESSION['empresa_id']) ? $_SESSION['empresa_id'] : 'ERR'; ?>
                    </span>

                    <span class="fw-bold text-white d-lg-none">SRGUERRA</span>

                    <div class="d-flex align-items-center gap-3 ms-auto">
                        <div class="position-relative cursor-pointer">
                            <i class="fas fa-bell text-muted fs-5"></i>
                            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                        </div>

                        <div class="dropdown" style="position: relative; z-index: 1051;">
                            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-white" id="userMenu" data-bs-toggle="dropdown">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                    <?php echo strtoupper(substr($_SESSION['usuario_nombre'], 0, 1)); ?>
                                </div>
                                <span class="d-none d-sm-inline"><?php echo explode(' ', $_SESSION['usuario_nombre'])[0]; ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow-lg border-0">
                                <li><a class="dropdown-item" href="#">Perfil</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-danger" href="index.php?ruta=logout">Cerrar Sesión</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="sidebar-overlay" id="sidebarOverlay"></div>

            <div class="container-fluid px-3 px-md-4 pb-5">