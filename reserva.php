<?php
include 'database.php';
include 'functions.php';
session_start();
redirectIfNotLogged();

// Detectar si venimos de una confirmación de reserva para marcar al conductor como pendiente
$pendingConductorId = isset($_SESSION['pending_conductor_id']) ? intval($_SESSION['pending_conductor_id']) : null;
$pendingReservaId = isset($_SESSION['pending_reserva_id']) ? intval($_SESSION['pending_reserva_id']) : null;
$pendingReservaAccepted = false;
$openWhatsApp = isset($_SESSION['open_whatsapp']) ? $_SESSION['open_whatsapp'] : null;
// Limpiar sesión de flags para que solo se muestre una vez
if ($pendingConductorId) {
    unset($_SESSION['pending_conductor_id']);
}
if ($openWhatsApp) {
    unset($_SESSION['open_whatsapp']);
}

syncConductorEstado($conn);

if ($pendingReservaId) {
    $stmtStatus = mysqli_prepare($conn, "SELECT status FROM reservas WHERE id = ? LIMIT 1");
    if ($stmtStatus) {
        mysqli_stmt_bind_param($stmtStatus, 'i', $pendingReservaId);
        mysqli_stmt_execute($stmtStatus);
        mysqli_stmt_bind_result($stmtStatus, $statusReserva);
        if (mysqli_stmt_fetch($stmtStatus)) {
            if ($statusReserva === 'Confirmada') {
                $pendingReservaAccepted = true;
                unset($_SESSION['pending_reserva_id']);
            }
        }
        mysqli_stmt_close($stmtStatus);
    }
}

// Obtener todos los datos de conductores registrados
$query = "SELECT * FROM conductor ORDER BY nombre, apellido";
$conductores = mysqli_query($conn, $query);

// Contadores de estado para mostrar tarjetas similares a listar.php
$activeCount = 0;
$pathCount = 0;
$busyCount = 0;
$countResult = mysqli_query($conn, "SELECT LOWER(estado) AS estado FROM conductor");
while ($countRow = mysqli_fetch_assoc($countResult)) {
    $estado = trim($countRow['estado']);
    if (strpos($estado, 'activo') !== false) {
        $activeCount++;
    } elseif (strpos($estado, 'camino') !== false) {
        $pathCount++;
    } elseif (strpos($estado, 'ocup') !== false) {
        $busyCount++;
    } else {
        $busyCount++;
    }
}

// Si venimos de una confirmación, registrar estados actuales para depuración
if (isset($_GET['from']) && $_GET['from'] === 'confirm') {
    $logRes = mysqli_query($conn, "SELECT id, nombre, apellido, telefono, estado FROM conductor ORDER BY nombre, apellido");
    if ($logRes) {
        $lines = array();
        while ($c = mysqli_fetch_assoc($logRes)) {
            $lines[] = date('Y-m-d H:i:s') . " - Conductor id={$c['id']} nombre={$c['nombre']} apellido={$c['apellido']} telefono={$c['telefono']} estado={$c['estado']}";
        }
        if (!empty($lines)) {
            @file_put_contents(__DIR__ . '/reserva_debug.log', implode("\n", $lines) . "\n", FILE_APPEND);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conductores Registrados - QuillaMovil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="estilos.css">
    <style>
        body.listar-bg {
            background: radial-gradient(circle at top, #1b1b1b 0%, #050505 55%, #000 100%);
            color: #f8f9fa;
        }

        .driver-card {
            min-height: auto;
            max-width: 72%;
            border-radius: 18px;
            padding: 0.2rem;
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
            border: 3px solid transparent;
            border-image: linear-gradient(45deg, #3d3c3c 0%, #3d3c3c 45%, #ff0000 45%, #ff0000 55%, #3d3c3c 55%, #3d3c3c 100%) 1;
            background: transparent !important;
            box-shadow: none;
            width: 72%;
            margin: 0 auto;
            position: relative;
        }

        .driver-card .card-body {
            padding: 0.65rem;
            gap: 0.4rem;
        }

        .driver-card .driver-card-icon-left {
            position: absolute;
            top: 1.1rem;
            left: 1rem;
            font-size: 1.3rem;
            color: #ff8c00;
            opacity: 0.9;
        }

        .driver-card .card-logo {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            width: 40px;
            height: auto;
            opacity: 0.95;
        }

        .btn-red {
            background-color: #c00114;
            border-color: #da0016;
            color: #fff;
            padding: 0.75rem 1.5rem;
            min-width: 180px;
            width: auto;
            font-size: 1rem;
        }

        @media (max-width: 767.98px) {
            .btn-red {
                width: 100%;
                max-width: 100%;
            }
        }

        @media (max-width: 575.98px) {
            .driver-card .card-logo {
                width: 32px;
                top: 0.5rem;
                right: 0.5rem;
            }

            .btn-red {
                padding: 0.75rem;
            }
        }

        .driver-card .card-title {
            color: #fff;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }

        .btn-outline-light {
            border-color: #f8f9fa;
            color: #f8f9fa;
        }

        .btn-outline-light:hover,
        .btn-outline-light:focus {
            background: #f8f9fa;
            color: #111;
            border-color: #f8f9fa;
        }

        .btn-outline-light i {
            color: #ff4d4f;
        }

        .btn-red {
            background-color: #c00114;
            border-color: #da0016;
            color: #fff;
            padding: 0.75rem 1.5rem;
            min-width: 250px;
            font-size: 1rem;
        }

        .btn-red:hover,
        .btn-red:focus {
            background-color: #c82333;
            border-color: #bd2130;
            color: #fff;
        }

        .vehicle-name {
            color: #ff8c00;
        }

        @keyframes parpadeo {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.3;
            }
        }

        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 0.5rem;
            animation: parpadeo 1s infinite;
        }

        .status-libre {
            background-color: #00cc00;
            box-shadow: 0 0 8px 0 #00cc00;
        }

        #reservaNotification {
            position: fixed;
            top: -130px;
            left: 50%;
            transform: translateX(-50%);
            width: min(720px, calc(100% - 2rem));
            z-index: 1100;
            background: rgba(173, 216, 230, 0.92);
            color: #022c55;
            border-radius: 0 0 18px 18px;
            border: 1px solid rgba(0, 99, 163, 0.95);
            box-shadow: 0 18px 40px rgba(0, 44, 85, 0.24);
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            opacity: 0;
            transition: top 0.35s ease, opacity 0.35s ease, transform 0.35s ease;
        }

        #reservaNotification.show {
            top: 1rem;
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        #reservaNotification.hide {
            top: -130px;
            opacity: 0;
            transform: translateX(-50%) translateY(-10px);
        }

        #reservaNotificationText {
            font-size: 1rem;
            font-weight: 700;
            color: #022c55;
        }

        #reservaNotification .btn-close {
            background: rgba(2, 44, 85, 0.12);
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: grid;
            place-items: center;
            color: #022c55;
            border: 1px solid rgba(2, 44, 85, 0.2);
            opacity: 0.9;
        }

        #reservaNotification .btn-close:hover {
            opacity: 1;
            background: rgba(2, 44, 85, 0.18);
        }

        .status-ocupado {
            background-color: #ff0000;
            box-shadow: 0 0 8px 0 #ff0000;
        }

        .estado-text-libre {
            color: #00cc00;
            font-weight: 700;
        }

        .estado-text-ocupado {
            color: #ff4d4f;
            font-weight: 700;
        }

        .driver-card.ocupado {
            border-color: #ff4d4f !important;
            box-shadow: 0 0 16px rgba(255, 77, 79, 0.35);
        }

        .driver-card.ocupado .card-body {
            background: rgba(255, 77, 79, 0.06);
        }

        .estado-badge-ocupado {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            background: #ff4d4f;
            color: #fff;
            font-size: 0.75rem;
            font-weight: 700;
            margin-left: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .whatsapp-link {
            color: #25d366;
            text-decoration: none;
            font-weight: 600;
        }

        .whatsapp-link:hover,
        .whatsapp-link:focus {
            color: #a7ffb8;
            text-decoration: underline;
        }

        .card-header {
            background: linear-gradient(135deg, #000000 0%, #2b2b2b 100%);
            border-bottom: 1px solid rgba(0, 0, 0, 0.12);
        }

        .modal-content {
            background: #111111;
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .modal-dialog.modal-sm {
            max-width: 620px;
        }

        .modal-body .row {
            display: flex;
            flex-wrap: nowrap;
        }

        .modal-body .col-md-5 {
            flex: 0 0 40%;
            min-width: 0;
        }

        .modal-body .col-md-7 {
            flex: 0 0 60%;
            min-width: 0;
        }

        .modal-body img#modalFoto {
            width: 100%;
            max-width: 180px !important;
            max-height: 220px !important;
        }

        .modal-header,
        .modal-footer {
            border-color: rgba(255, 255, 255, 0.12);
        }

        .modal-header .modal-title,
        .modal-body p,
        .modal-body strong,
        .modal-body span,
        .modal-footer .btn {
            color: #fff;
        }

        .modal-footer .btn-outline-light {
            background-color: transparent;
            border-color: #fff;
            color: #fff;
        }

        .modal-footer .btn-outline-light:hover,
        .modal-footer .btn-outline-light:focus {
            background-color: rgba(255, 255, 255, 0.08);
            border-color: #fff;
            color: #fff;
        }

        .modal-body p {
            margin-bottom: 0.8rem;
            color: #fff;
        }

        .modal-body strong {
            color: #fff;
        }

        .modal-body span {
            color: #fff;
        }

        .container.py-5 {
            max-width: 1180px;
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }

        .status-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: center;
            margin-bottom: 1rem;
        }
        .status-card {
            flex: 0 1 auto;
            width: 100%;
            max-width: 190px;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.6rem 0.7rem;
            border-radius: 0.85rem;
            color: #fff;
            box-shadow: 0 6px 14px rgba(0,0,0,0.12);
            min-width: 140px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            backdrop-filter: blur(5px);
        }
        .status-cards .col {
            max-width: 200px;
        }
        .status-card-icon {
            width: 36px;
            height: 36px;
            min-width: 36px;
            min-height: 36px;
            display: grid;
            place-items: center;
            background: rgba(255,255,255,0.18);
            border-radius: 50%;
        }
        .status-card-icon svg {
            width: 20px;
            height: 20px;
            fill: #fff;
        }
        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            animation: blink 1.4s ease-in-out infinite;
            box-shadow: 0 0 0 rgba(255,255,255,0.5);
        }
        .status-card-number {
            font-size: 1.4rem;
            font-weight: 700;
            color: rgba(255,255,255,0.95);
            margin-top: 0.15rem;
            line-height: 1;
        }
        .status-card-body {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .status-card-text {
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }
        .status-green { background: #1f6b2b; }
        .status-yellow { background: #1b6eff; }
        .status-red { background: #8e1f28; }
        .status-green .status-dot { background: #77d582; }
        .status-yellow .status-dot { background: #78a7ff; }
        .status-red .status-dot { background: #ff8a8a; }

        .estado-badge-pendiente {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            background: #1b6eff;
            color: #fff;
            font-size: 0.75rem;
            font-weight: 700;
            margin-left: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.3; transform: scale(0.85); }
        }

        /* Menú Desplegable Derecha-Izquierda */
        .dropdown-menu-container {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            z-index: 1000;
        }

        .dropdown-toggle-btn {
            background: transparent;
            border: none;
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.5rem;
            transition: all 0.3s ease;
            box-shadow: none;
        }

        .dropdown-toggle-btn:hover {
            transform: scale(1.1);
            box-shadow: none;
            background: transparent;
        }

        .dropdown-menu-list {
            position: absolute;
            top: 70px;
            right: 0;
            background: rgba(20, 20, 20, 0.95);
            border: 1px solid rgba(192, 1, 20, 0.3);
            border-radius: 12px;
            min-width: 240px;
            max-width: 280px;
            list-style: none;
            padding: 0.5rem 0;
            margin: 0;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
            opacity: 0;
            visibility: hidden;
            transform: translateX(20px) translateY(-10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .dropdown-menu-list.active {
            opacity: 1;
            visibility: visible;
            transform: translateX(0) translateY(0);
        }

        .dropdown-menu-list li {
            margin: 0;
            padding: 0;
        }

        .dropdown-menu-list a,
        .dropdown-menu-list button {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: #f8f9fa;
            text-decoration: none;
            border: none;
            background: transparent;
            width: 100%;
            text-align: left;
            cursor: pointer;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .dropdown-menu-list a:hover,
        .dropdown-menu-list button:hover {
            background: rgba(192, 1, 20, 0.15);
            padding-left: 1.2rem;
            color: #c00114;
        }

        .dropdown-menu-list i {
            width: 20px;
            text-align: center;
            color: #c00114;
        }

        .dropdown-menu-list a:hover i {
            color: #c82333;
        }

        .dropdown-divider {
            border-top: 1px solid rgba(192, 1, 20, 0.2);
            margin: 0.5rem 0;
        }

        /* Overlay para cerrar menú */
        .dropdown-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .dropdown-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        @media (max-width: 575.98px) {
            .dropdown-menu-container {
                top: 1rem;
                right: 1rem;
            }

            .dropdown-toggle-btn {
                width: 45px;
                height: 45px;
                font-size: 1.25rem;
            }

            .dropdown-menu-list {
                min-width: 200px;
                max-width: 240px;
            }
        }
    </style>
</head>

<body class="listar-bg">

    <!-- Overlay para menú desplegable -->
    <div class="dropdown-overlay" id="dropdownOverlay"></div>

    <!-- Menú Desplegable Derecha-Izquierda -->
    <div class="dropdown-menu-container">
        <button class="dropdown-toggle-btn" id="dropdownToggle" title="Menú">
            <i class="bi bi-three-dots-vertical"></i>
        </button>
        <ul class="dropdown-menu-list" id="dropdownMenu">
            <li>
                <a href="index.php" class="dropdown-link">
                    <i class="bi bi-house-fill"></i>
                    <span>Inicio</span>
                </a>
            </li>
            <li>
                <a href="dashboard.php" class="dropdown-link">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
           
            <li>
                <a href="login-conductor.php" class="dropdown-link">
                    <i class="bi bi-person-badge"></i>
                    <span>Ingreso Conductor</span>
                </a>
            </li>
            
            
        
        </ul>
    </div>

    <div id="reservaNotification" class="hide" role="alert">
        <div id="reservaNotificationText">Conductor aceptó tu solicitud.</div>
        <button type="button" class="btn-close btn-close-white" aria-label="Cerrar"></button>
    </div>

    <div class="text-center mb-4" style="padding-top: 1.5rem;">
        <h1 class="display-6 text-white">CONDUCTORES REGISTRADOS PARA RESERVAR</h1>
    </div>

    <div class="status-cards row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3 justify-content-center mb-4">
        <div class="col d-flex justify-content-center">
            <div class="status-card status-green">
                <div class="status-card-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                </div>
                <span class="status-dot"></span>
                <div class="status-card-body">
                    <div class="status-card-number"><?php echo $activeCount; ?></div>
                    <div class="status-card-text">Activos</div>
                </div>
            </div>
        </div>
    
        <div class="col d-flex justify-content-center">
            <div class="status-card status-red">
                <div class="status-card-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                </div>
                <span class="status-dot"></span>
                <div class="status-card-body">
                    <div class="status-card-number"><?php echo $busyCount; ?></div>
                    <div class="status-card-text">Ocupado</div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($conductores && mysqli_num_rows($conductores) > 0): ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4 justify-content-center">
            <?php while ($conductor = mysqli_fetch_assoc($conductores)): ?>
                <?php
                    $estado = trim($conductor['estado']);
                    $isOcupado = strtolower($estado) === 'ocupado';
                    // Detectar si este conductor es el que seleccionó el cliente
                    $isPending = $pendingConductorId && (intval($conductor['id']) === $pendingConductorId);
                    $indicadorClass = $isOcupado ? 'status-ocupado' : ($isPending ? 'status-yellow' : 'status-libre');
                    $textoClass = $isOcupado ? 'estado-text-ocupado' : ($isPending ? 'estado-text-ocupado' : 'estado-text-libre');
                ?>
                <div class="col d-flex justify-content-center">
                    <div class="card bg-dark border-secondary h-100 shadow-sm driver-card <?php echo $isOcupado ? 'ocupado' : ''; ?>">
                        <a href="index.php" class="card-logo-link">
                            <img src="logo.png" alt="Logo QuillaMovil" class="card-logo">
                        </a>
                        
                        <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                            <?php if (!empty($conductor['foto'])): ?>
                                <img src="<?php echo htmlspecialchars($conductor['foto']); ?>" alt="Foto conductor" style="width:64px; height:64px; object-fit:cover; border-radius:50%; margin-bottom:0.5rem;">
                            <?php else: ?>
                                <i class="bi bi-person-fill text-white" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                            <?php endif; ?>
                            <h5 class="card-title text-white mb-1">
                                <?php echo htmlspecialchars($conductor['nombre'] . ' ' . $conductor['apellido']); ?>
                            </h5>
                            <p class="vehicle-name mb-2"><?php echo htmlspecialchars($conductor['vehiculo']); ?></p>
                            <p class="mb-2" style="font-size: 0.9rem; color: #999;">
                                <span class="status-indicator <?php echo $indicadorClass; ?>"></span>
                                Estado: <span class="<?php echo $textoClass; ?>"><?php echo $isPending ? 'Solicitud enviada' : htmlspecialchars($conductor['estado']); ?></span>
                                <?php if ($isOcupado): ?>
                                    <span class="estado-badge-ocupado">Confirmado</span>
                                <?php elseif ($isPending): ?>
                                    <span class="estado-badge-pendiente">Solicitud enviada</span>
                                <?php endif; ?>
                            </p>
                           
                            <p class="mb-3">
                                <a class="whatsapp-link" href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $conductor['telefono']); ?>?text=<?php echo urlencode('Hola, Me comunico desde "QUILLAMOVIL" para coordinar la reserva de uno de sus vehículos. ¿Podría indicarme disponibilidad y los detalles del servicio? Quedo atento a su respuesta.'); ?>" target="_blank" rel="noopener noreferrer">
                                    <?php echo htmlspecialchars($conductor['telefono']); ?>
                                </a>
                            </p>
                            <button type="button" class="btn btn-red btn-sm mt-2" data-bs-toggle="modal"
                                data-bs-target="#detalleConductorModal"
                                data-dni="<?php echo htmlspecialchars($conductor['dni']); ?>"
                                data-nombre="<?php echo htmlspecialchars($conductor['nombre']); ?>"
                                data-apellidos="<?php echo htmlspecialchars($conductor['apellido']); ?>"
                                data-telefono="<?php echo htmlspecialchars($conductor['telefono']); ?>"
                                data-vehiculo="<?php echo htmlspecialchars($conductor['vehiculo']); ?>"
                                data-placa="<?php echo htmlspecialchars($conductor['tipo_lic']); ?>"
                                data-numserie="<?php echo htmlspecialchars($conductor['numero_censo']); ?>"
                                data-color="<?php echo htmlspecialchars($conductor['color']); ?>"
                                data-foto="<?php echo htmlspecialchars($conductor['foto']); ?>"
                                data-estado="<?php echo htmlspecialchars($conductor['estado']); ?>">
                                <i class="bi bi-eye me-1"></i> Ver
                            </button>
                            <?php if ($isOcupado): ?>
                                <a class="btn btn-outline-light btn-sm mt-2 disabled" href="#" tabindex="-1" aria-disabled="true">Continuar</a>
                            <?php else: ?>
                                <a href="pasos-reserva.php?conductor_id=<?php echo urlencode($conductor['id']); ?>&conductor_nombre=<?php echo urlencode($conductor['nombre']); ?>&conductor_apellido=<?php echo urlencode($conductor['apellido']); ?>&conductor_telefono=<?php echo urlencode($conductor['telefono']); ?>" class="btn btn-outline-light btn-sm mt-2">Continuar</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-secondary text-center" role="alert">
            No hay conductores registrados todavía.
        </div>
    <?php endif; ?>


    <!-- Modal de detalles de conductor -->
    <div class="modal fade" id="detalleConductorModal" tabindex="-1" aria-labelledby="detalleConductorModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 700px;">
            <div class="modal-content bg-dark text-white border-secondary">
                <div class="modal-header border-bottom-secondary">
                    <h5 class="modal-title" id="detalleConductorModalLabel">Detalles del Conductor</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-5 d-flex justify-content-center align-items-center">
                            <img id="modalFoto" src="" alt="Foto conductor" style="max-width:180px; max-height:220px; object-fit:cover; border-radius:8px; display:none;">
                        </div>
                        <div class="col-md-7">
                            <p><strong>DNI:</strong> <span id="modalDni">-</span></p>
                            <p><strong>Nombre:</strong> <span id="modalNombre">-</span></p>
                            <p><strong>Apellidos:</strong> <span id="modalApellidos">-</span></p>
                            <p><strong>Teléfono:</strong> <span id="modalTelefono">-</span></p>
                            <p><strong>Vehículo:</strong> <span id="modalVehiculo">-</span></p>
                            <p><strong>Placa:</strong> <span id="modalPlaca">-</span></p>
                            <p><strong>Número de serie:</strong> <span id="modalNumSerie">-</span></p>
                            <p><strong>Color:</strong> <span id="modalColor">-</span></p>
                            <p id="modalEstadoContainer"><strong>Estado:</strong> <span id="modalEstado">-</span></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-secondary">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var detalleModal = document.getElementById('detalleConductorModal');

            detalleModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                if (!button) return;

                document.getElementById('modalDni').textContent = button.getAttribute('data-dni') || '-';
                document.getElementById('modalNombre').textContent = button.getAttribute('data-nombre') || '-';
                document.getElementById('modalApellidos').textContent = button.getAttribute('data-apellidos') || '-';
                document.getElementById('modalTelefono').textContent = button.getAttribute('data-telefono') || '-';
                document.getElementById('modalVehiculo').textContent = button.getAttribute('data-vehiculo') || '-';
                document.getElementById('modalPlaca').textContent = button.getAttribute('data-placa') || '-';
                document.getElementById('modalNumSerie').textContent = button.getAttribute('data-numserie') || '-';
                document.getElementById('modalColor').textContent = button.getAttribute('data-color') || '-';
                var estadoVal = button.getAttribute('data-estado') || '-';
                var container = document.getElementById('modalEstadoContainer');
                document.getElementById('modalEstado').textContent = estadoVal;
                if (container) {
                    container.className = ((estadoVal === 'Activo' || estadoVal === 'Libre') ? 'estado-libre' : (estadoVal === 'Ocupado' ? 'estado-ocupado' : ''));
                }
                // Foto en modal
                var fotoUrl = button.getAttribute('data-foto') || '';
                var modalFoto = document.getElementById('modalFoto');
                if (modalFoto) {
                    if (fotoUrl) {
                        modalFoto.src = fotoUrl;
                        modalFoto.style.display = '';
                    } else {
                        modalFoto.src = '';
                        modalFoto.style.display = 'none';
                    }
                }
            });
        });

        // Funcionalidad del Menú Desplegable
        document.addEventListener('DOMContentLoaded', function () {
            const dropdownToggle = document.getElementById('dropdownToggle');
            const dropdownMenu = document.getElementById('dropdownMenu');
            const dropdownOverlay = document.getElementById('dropdownOverlay');

            // Alternar menú
            dropdownToggle.addEventListener('click', function (e) {
                e.stopPropagation();
                dropdownMenu.classList.toggle('active');
                dropdownOverlay.classList.toggle('active');
            });

            // Cerrar menú al hacer clic en un enlace
            const dropdownLinks = dropdownMenu.querySelectorAll('a');
            dropdownLinks.forEach(link => {
                link.addEventListener('click', function () {
                    dropdownMenu.classList.remove('active');
                    dropdownOverlay.classList.remove('active');
                });
            });

            // Cerrar menú al hacer clic en el overlay
            dropdownOverlay.addEventListener('click', function () {
                dropdownMenu.classList.remove('active');
                dropdownOverlay.classList.remove('active');
            });

            // Cerrar menú si se hace clic en cualquier otro lugar
            document.addEventListener('click', function (e) {
                if (!e.target.closest('.dropdown-menu-container')) {
                    dropdownMenu.classList.remove('active');
                    dropdownOverlay.classList.remove('active');
                }
            });
        });

        <?php if (!empty($openWhatsApp)): ?>
        // Abrir WhatsApp en nueva pestaña para respaldo (solo una vez)
        document.addEventListener('DOMContentLoaded', function () {
            try {
                window.open(<?php echo json_encode($openWhatsApp); ?>, '_blank');
            } catch (e) {
                console.error('No se pudo abrir WhatsApp automáticamente');
            }
        });
        <?php endif; ?>

        <?php if ($pendingReservaId): ?>
        document.addEventListener('DOMContentLoaded', function () {
            var notification = document.getElementById('reservaNotification');
            var notificationText = document.getElementById('reservaNotificationText');
            var closeButton = notification ? notification.querySelector('.btn-close') : null;

            var autoHideTimer;

            function showAcceptedNotification() {
                if (!notification || !notificationText) return;
                notificationText.textContent = 'Conductor aceptó tu solicitud.';
                notification.classList.remove('hide');
                notification.classList.add('show');
                if (autoHideTimer) {
                    clearTimeout(autoHideTimer);
                }
                autoHideTimer = setTimeout(hideNotification, 6000);
            }

            function hideNotification() {
                if (!notification) return;
                notification.classList.remove('show');
                notification.classList.add('hide');
            }

            if (closeButton) {
                closeButton.addEventListener('click', function () {
                    hideNotification();
                    if (autoHideTimer) {
                        clearTimeout(autoHideTimer);
                    }
                });
            }

            function checkReservaStatus() {
                fetch('check-reserva-status.php')
                    .then(function (response) { return response.json(); })
                    .then(function (data) {
                        if (data && data.success && data.status === 'Confirmada') {
                            showAcceptedNotification();
                        }
                    })
                    .catch(function (error) {
                        console.error('Error al consultar el estado de la reserva:', error);
                    });
            }

            checkReservaStatus();
            setInterval(checkReservaStatus, 8000);
        });
        <?php endif; ?>
    </script>
</body>

</html>