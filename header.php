<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Servicio y Gestion QuillaMovil</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lobster&family=Poppins:wght@300;400;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* =============== VARIABLES Y TEMAS =============== */
        :root {
            --primary-dark: #1a1a1a;
            --primary-red-light: #f30505;
            --text-light: #ececec;
       
            
        }

        /* Imágenes responsivas */
        img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        /* =============== NAVBAR STYLES =============== */
        .navbar {
            background-color: var(--primary-dark) !important;
            padding: 1rem 0;
            border-bottom: 3px solid var(--primary-red);
        }

        .navbar-brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            
           
        }

        .navbar-brand:hover {
        }

        .navbar-brand img {
            height: 90px;
            width: auto;
            border-radius: 8px;
        }

       

        .brand-name {
            display: inline-flex;
            font-family: 'Lobster', cursive;
            font-size: 2.2rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            letter-spacing: 1px;
        }

        .brand-name .quilla {
            color: #ffffff;
            font-weight: 700;
        }

        .brand-name .movil {
            color: var(--primary-red);
            font-weight: 700;
            margin-left: 2px;
        }

        /* =============== NAVBAR NAVIGATION =============== */
        .navbar-nav .nav-link {
            color: var(--text-light) !important;
            font-weight: 500;
            font-size: 1rem;
            margin: 0 8px;
            position: relative;
        }

        .navbar-nav .nav-link:before {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 3px;
            background-color: var(--primary-red);
        }

        .navbar-nav .nav-link:hover {
            color: var(--text-light) !important;
        }

        .navbar-nav .nav-link:hover:before {
            width: 0;
        }

        /* =============== DROPDOWN STYLES =============== */
        .bordered-dropdown {
            border: 2px solid var(--text-light) !important;
            border-radius: 8px;
            padding: 10px 18px !important;
            margin: 0 8px;
            background: transparent;
            transition: border-color 0.3s ease;
        }

        .bordered-dropdown:hover,
        .bordered-dropdown:focus {
            border-color: var(--primary-red-light) !important;
            background-color: transparent !important;
        }

        .dropdown-menu-dark {
            background-color: #1a1a1a !important;
            border: 1px solid var(--border-color);
            border-radius: 8px;
        }

        .dropdown-menu-dark .dropdown-item {
            color: var(--text-light);
            font-weight: 500;
            border-left: 3px solid transparent;
            padding-left: 18px;
        }

        .dropdown-menu-dark .dropdown-item:hover {
            background-color: transparent;
            color: var(--text-light);
            border-left-color: transparent;
        }

        .dropdown-divider {
            border-color: var(--border-color);
        }

        /* =============== BUTTONS =============== */
        .btn {
            font-weight: 600;
            border-radius: 8px;
            font-size: 1rem;
        }

        .btn-outline-light {
            border: 2px solid var(--text-light);
            color: var(--text-light);
            background-color: transparent;
        }

        .btn-outline-light:hover,
        .btn-outline-light:focus {
            background-color: transparent;
            color: var(--text-light);
            border-color: var(--text-light);
        }

        .btn-login-red-hover:hover,
        .btn-login-red-hover:focus {
            border-color: #f30505 !important;
            color: var(--text-light);
        }

        .btn-primary-custom {
            background-color: #f30000 !important;
            border: none;
            color: var(--text-light);
            font-weight: 700;
        }

        .btn-primary-custom:hover,
        .btn-primary-custom:focus {
            background-color: #d60000 !important;
            color: var(--text-light);
        }

        .btn-danger {
            background-color: #e53935 !important;
            border: none;
            font-weight: 600;
        }

        .btn-danger:hover {
            background-color: #c62828 !important;
        }

        .btn-sm {
            padding: 10px 16px;
            font-size: 0.95rem;
        }

        /* =============== OFFCANVAS (MOBILE MENU) =============== */
        .offcanvas-end {
            background-color: #111 !important;
        }

        .offcanvas-header {
            background-color: var(--primary-dark) !important;
            border-bottom: 2px solid var(--primary-red);
        }

        .offcanvas-title {
            color: var(--text-light);
            font-weight: 700;
            font-size: 1.3rem;
        }

        .btn-close-white {
            background-color: var(--text-light) !important;
        }

        .offcanvas-body {
            background-color: #111 !important;
        }

        @media (max-width: 767px) {
            .offcanvas.offcanvas-end {
                --bs-offcanvas-width: 50vw;
                width: 50vw !important;
                max-width: 50vw !important;
            }
        }

        /* =============== TABLAS - ESTILOS PARA LISTAR =============== */
        body.listar-bg {
            background-color: #000 !important;
            color: #fff !important;
        }

        body.listar-bg .table {
            background-color: #181818 !important;
            color: #fff !important;
            border-radius: 8px;
            overflow: hidden;
        }

        body.listar-bg .table thead th {
            background-color: #222 !important;
            color: var(--accent-yellow) !important;
            font-weight: 700;
            border: none;
        }

        body.listar-bg .table-bordered td,
        body.listar-bg .table-bordered th {
            border-color: var(--border-color) !important;
        }

        body.listar-bg .table-striped tbody tr:nth-of-type(odd) {
            background-color: #232323 !important;
        }

        body.listar-bg .table tbody tr:hover {
            background-color: rgba(214, 51, 51, 0.1) !important;
        }

        body.listar-bg .btn {
            color: #fff;
        }

        body.listar-bg .btn-warning {
            background-color: #ff9800;
            border-color: #ff9800;
            color: #000;
            font-weight: 600;
        }

        body.listar-bg .btn-warning:hover {
            background-color: #f57c00;
        }

        body.listar-bg .btn-danger {
            background-color: #e53935;
            border-color: #e53935;
            color: #fff;
            font-weight: 600;
        }

        body.listar-bg .btn-danger:hover {
            background-color: #c62828;
        }

        /* =============== RESPONSIVE DESIGN =============== */
        @media (max-width: 992px) {
            .navbar-brand img {
                height: 70px;
            }

            .brand-name {
                font-size: 1.8rem;
            }

            .navbar-nav .nav-link {
                margin: 5px 0;
                padding: 10px 0;
            }

            .bordered-dropdown {
                margin: 5px 0 !important;
            }
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 0.75rem 0;
            }

            .navbar-brand {
                gap: 8px;
            }

            .navbar-brand img {
                height: 60px;
            }

            .brand-name {
                font-size: 1.5rem;
            }

            .btn-sm {
                padding: 8px 12px;
                font-size: 0.9rem;
            }

            .ms-3 {
                margin-left: 0 !important;
            }
        }

        @media (max-width: 576px) {
            .navbar-brand {
                gap: 6px;
            }

            .navbar-brand img {
                height: 50px;
            }

            .brand-name {
                font-size: 1.3rem;
            }

            .offcanvas-body {
                padding: 12px;
            }

            .btn-outline-light,
            .btn-primary-custom,
            .btn-danger {
                width: 100% !important;
                margin-bottom: 10px !important;
            }

            .dropdown w-100 {
                margin-bottom: 10px;
            }

            /* Tabla responsiva */
            table {
                font-size: 0.9rem !important;
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
            }

            th {
                text-align: left;
                padding: 10px 5px;
            }

            td {
                padding: 10px 5px;
            }
        }

        /* =============== SERVICIOS/ANIMACIONES =============== */
        .service-buttons {
            width: auto;
            max-width: 320px;
        }

        .service-btn {
            font-weight: 700;
            font-size: 1.2rem;
            padding: 10px 16px;
            width: 250px;
            height: 80px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            text-decoration: none;
            color: #ffffff;
            border: 3px solid #fff;
        }

        .service-btn:hover {
            border-color: #fff;
        }

        /* =============== UTILITIES =============== */
        .gap-2 {
            gap: 0.5rem !important;
        }

        .gap-lg-2 {
            gap: 0.5rem !important;
        }

        @media (min-width: 1200px) {
            .gap-lg-2 {
                gap: 0.5rem !important;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar/Encabezado -->
    <?php if (in_array(basename($_SERVER['PHP_SELF']), ['listar.php', 'listar_motocarga.php', 'listar_camion.php', 'listar_conductor.php'])) : ?>
    <!-- NAVBAR PARA PÁGINAS DE LISTAR -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid px-4 justify-content-end">
            <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenuConductores" aria-controls="mobileMenuConductores" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse d-none d-lg-flex justify-content-end" id="navbarButtonsConductores">
                <ul class="navbar-nav mb-2 mb-lg-0 align-items-center gap-2">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            Inicio
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle bordered-dropdown" href="#" id="navbarDropdownConductores" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Solicitar hoy
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarDropdownConductores">
                            <li><a class="dropdown-item" href="listar.php"> Torito</a></li>
                            <li><a class="dropdown-item" href="listar_motocarga.php"> Motocarga</a></li>
                            <li><a class="dropdown-item" href="listar_camion.php"> Camión</a></li>
                      
                        </ul>
                    </li>
                </ul>
            </div>
            <div class="offcanvas offcanvas-end d-lg-none" tabindex="-1" id="mobileMenuConductores" aria-labelledby="mobileMenuConductoresLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="mobileMenuConductoresLabel">Menú</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body d-flex flex-column">
                    <a href="index.php" class="btn btn-outline-light mb-2">
                        Inicio
                    </a>
                    <a href="index.php#como-funciona" class="btn btn-outline-light mb-2">
                        ¿Cómo funciona?
                    </a>
                    <div class="dropdown w-100 mb-2">
                        <button class="btn btn-outline-light w-100 dropdown-toggle" type="button" id="dropdownSolicitarMobile" data-bs-toggle="dropdown" aria-expanded="false">
                            Solicitar hoy
                        </button>
                        <ul class="dropdown-menu dropdown-menu-dark w-100" aria-labelledby="dropdownSolicitarMobile">
                            <li><a class="dropdown-item" href="listar.php">Torito</a></li>
                            <li><a class="dropdown-item" href="listar_motocarga.php">Motocarga</a></li>
                            <li><a class="dropdown-item" href="listar_camion.php">Camión</a></li>
                            
                        </ul>
                    </div>
                    <a href="conductor.php" class="btn btn-outline-light mb-2">
                        Conductor Reserva
                    </a>
                    <a href="logout.php" class="btn btn-danger">
                        <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <?php elseif (in_array(basename($_SERVER['PHP_SELF']), ['conductor-torito.php', 'conductores-motocarga.php', 'conductores-camion.php'])) : ?>
    <!-- NAVBAR PARA PAGES DE SOLICITUD DE CONDUCTORES -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid px-4 justify-content-end">
            <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenuConductores" aria-controls="mobileMenuConductores" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse d-none d-lg-flex justify-content-end" id="navbarButtonsConductores">
                <ul class="navbar-nav mb-2 mb-lg-0 align-items-center gap-2">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="bi bi-house"></i> Inicio
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle bordered-dropdown" href="#" id="navbarDropdownConductores" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-plus-circle"></i> Solicitar hoy
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarDropdownConductores">
                            <li><a class="dropdown-item" href="listar.php"> Torito</a></li>
                            <li><a class="dropdown-item" href="listar_motocarga.php"> Motocarga</a></li>
                            <li><a class="dropdown-item" href="listar_camion.php"> Camión</a></li>
                           
                        </ul>
                    </li>
                </ul>
            </div>
            <div class="offcanvas offcanvas-end d-lg-none" tabindex="-1" id="mobileMenuConductores" aria-labelledby="mobileMenuConductoresLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="mobileMenuConductoresLabel">Menú</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body d-flex flex-column">
                    <a href="index.php" class="btn btn-outline-light mb-2">
                        Inicio
                    </a>
                    <a href="index.php#como-funciona" class="btn btn-outline-light mb-2" data-bs-dismiss="offcanvas">
                        ¿Cómo funciona?
                    </a>
                    <div class="dropdown w-100 mb-2">
                        <button class="btn btn-outline-light w-100 dropdown-toggle" type="button" id="dropdownSolicitarMobile" data-bs-toggle="dropdown" aria-expanded="false">
                            Solicitar hoy
                        </button>
                        <ul class="dropdown-menu dropdown-menu-dark w-100" aria-labelledby="dropdownSolicitarMobile">
                            <li><a class="dropdown-item" href="listar.php">Torito</a></li>
                            <li><a class="dropdown-item" href="listar_motocarga.php">Motocarga</a></li>
                            <li><a class="dropdown-item" href="listar_camion.php">Camión</a></li>
                           
                        </ul>
                    </div>
                    <a href="conductor.php" class="btn btn-outline-light mb-2">
                        Conductor Reserva
                    </a>
                    <a href="logout.php" class="btn btn-danger">
                        Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <?php else: ?>
    <!-- NAVBAR PRINCIPAL CON LOGO -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid px-4">
            <!-- Logo -->
            <a class="navbar-brand" href="index.php">
                <img src="logo.png" alt="QuillaMovil Logo">
                <span class="brand-name"><span class="quilla">Quilla</span><span class="movil">Movil</span></span>
            </a>
            <!-- Botón toggle para dispositivos móviles -->
            <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- Menú de navegación para desktop -->
            <div class="collapse navbar-collapse d-none d-lg-flex" id="navbarButtons">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 w-100 justify-content-end align-items-lg-center gap-2">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#como-funciona">
                            ¿Cómo funciona?
                        </a>
                    </li>
                    <!-- Dropdown de Solicitar -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle bordered-dropdown" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Solicitar Ahora
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarDropdown">
                            <?php if (!isset($_SESSION['user'])): ?>
                                <li><a class="dropdown-item" href="login.php?redirect=listar.php">Conductor-Torito</a></li>
                                <li><a class="dropdown-item" href="login.php?redirect=listar_motocarga.php">Conductor-Motocarga</a></li>
                                <li><a class="dropdown-item" href="login.php?redirect=listar_camion.php">Conductor-Camión</a></li>
                             
                            <?php else: ?>
                                <li><a class="dropdown-item" href="listar.php">Conductor-Torito</a></li>
                                <li><a class="dropdown-item" href="listar_motocarga.php">Conductor-Motocarga</a></li>
                                <li><a class="dropdown-item" href="listar_camion.php">Conductor-Camión</a></li>
                                
                                
                            <?php endif; ?>
                        </ul>
                    </li>
                    <!-- Botón Conductor -->
                    <li class="nav-item">
                        <?php if (!isset($_SESSION['user'])): ?>
                            <a href="login.php" class="nav-link bordered-dropdown">
                                <i class="bi bi-person-badge"></i> Conductor Reserva
                            </a>
                        <?php else: ?>
                            <a href="conductor.php" class="nav-link bordered-dropdown">
                                <i class="bi bi-person-badge"></i> Conductor Reserva
                            </a>
                        <?php endif; ?>
                    </li>
                </ul>
                <!-- Botones de Sesión (desktop) -->
                <div class="ms-3 gap-2 d-flex">
                    <?php if (!isset($_SESSION['user'])): ?>
                        <a href="login.php" class="btn btn-outline-light btn-sm btn-login-red-hover d-flex align-items-center justify-content-center" style="min-width: 180px; padding-top: 10px; padding-bottom: 10px;">
                            Iniciar Sesión
                        </a>
                    <?php else: ?>
                        <a href="logout.php" class="btn btn-primary-custom btn-sm d-flex align-items-center justify-content-center" style="min-width: 180px; padding-top: 10px; padding-bottom: 10px;">
                            Cerrar Sesión
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Offcanvas para menú móvil -->
             
            <div class="offcanvas offcanvas-end d-lg-none" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="mobileMenuLabel">Menú</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body d-flex flex-column">
                    <a href="index.php" class="btn btn-outline-light mb-2">
                        Inicio
                    </a>
                    <a href="index.php#como-funciona" class="btn btn-outline-light mb-2" data-bs-dismiss="offcanvas">
                        ¿Cómo funciona?
                    </a>
                    <?php if (!isset($_SESSION['user'])): ?>
                        <div class="dropdown w-100 mb-2">
                            <button class="btn btn-outline-light w-100 dropdown-toggle" type="button" id="dropdownSolicitarMobile" data-bs-toggle="dropdown" aria-expanded="false">
                                Solicitar hoy
                            </button>
                            <ul class="dropdown-menu dropdown-menu-dark w-100" aria-labelledby="dropdownSolicitarMobile">
                                <li><a class="dropdown-item" href="login.php?redirect=listar.php">Torito</a></li>
                                <li><a class="dropdown-item" href="login.php?redirect=listar_motocarga.php">Motocarga</a></li>
                                <li><a class="dropdown-item" href="login.php?redirect=listar_camion.php">Camión</a></li>
                                
                            </ul>
                        </div>
                        <a href="login.php?redirect=conductor.php" class="btn btn-outline-light mb-2 w-100">
                            Conductor Reserva
                        </a>
                        <a href="login.php" class="btn btn-outline-light mb-2 w-100">
                            Iniciar Sesión
                        </a>
                    <?php else: ?>
                        <div class="dropdown w-100 mb-2">
                            <button class="btn btn-outline-light w-100 dropdown-toggle" type="button" id="dropdownSolicitarMobileAuth" data-bs-toggle="dropdown" aria-expanded="false">
                                Solicitar hoy
                            </button>
                            <ul class="dropdown-menu dropdown-menu-dark w-100" aria-labelledby="dropdownSolicitarMobileAuth">
                                <li><a class="dropdown-item" href="listar.php">Torito</a></li>
                                <li><a class="dropdown-item" href="listar_motocarga.php">Motocarga</a></li>
                                <li><a class="dropdown-item" href="listar_camion.php">Camión</a></li>
                                
                            </ul>
                        </div>
                        <a href="conductor.php" class="btn btn-outline-light mb-2 w-100">
                            Conductor Reserva
                        </a>
                        <a href="logout.php" class="btn btn-danger w-100">
                            Cerrar Sesión
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    
    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Cierra el offcanvas padre y navega al ancla 'como-funciona' después de cerrarlo
        (function(){
            document.addEventListener('DOMContentLoaded', function(){
                document.querySelectorAll('.offcanvas a[href*="#como-funciona"]').forEach(function(anchor){
                    anchor.addEventListener('click', function(e){
                        var href = anchor.getAttribute('href');
                        var off = anchor.closest('.offcanvas');
                        if (!off) return; // no estamos dentro de un offcanvas
                        e.preventDefault();
                        var oc = bootstrap.Offcanvas.getInstance(off) || new bootstrap.Offcanvas(off);
                        // Cuando el offcanvas se haya cerrado, navegamos
                        var handler = function(){
                            off.removeEventListener('hidden.bs.offcanvas', handler);
                            // Navegar a la ruta/anchor (puede ser a otra página)
                            window.location.href = href;
                        };
                        off.addEventListener('hidden.bs.offcanvas', handler);
                        oc.hide();
                    });
                });
            });
        })();
    </script>
</body>
</html>
