<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once 'database.php';
require_once 'functions.php';

// Si no está logueado como conductor, redirigir
if (!isset($_SESSION['conductor'])) {
    header('Location: login-conductor.php');
    exit;
}

$conductor = $_SESSION['conductor'];
$conductorId = intval($conductor['id']);
$alertMessage = '';
$alertType = 'success';

// Cambiar estado del conductor (Activo / Ocupado)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_estado'])) {
    $nuevo = $_POST['nuevo_estado'] === 'Activo' ? 'Activo' : 'Ocupado';
    $stmt = mysqli_prepare($conn, "UPDATE conductor SET estado = ? WHERE id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'si', $nuevo, $conductorId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $_SESSION['conductor']['estado'] = $nuevo;
        $conductor['estado'] = $nuevo;
    }
}

// Acciones de reserva: aceptar o cancelar solicitudes pendientes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserva_action'], $_POST['reserva_id'])) {
    $reservaId = intval($_POST['reserva_id']);
    $action = $_POST['reserva_action'] === 'aceptar' ? 'Confirmada' : ($_POST['reserva_action'] === 'cancelar' ? 'Cancelada' : '');

    if ($action) {
        $stmtReserva = mysqli_prepare($conn, "UPDATE reservas SET status = ? WHERE id = ? AND conductor_nombre = ? AND conductor_apellido = ? AND status = 'Pendiente' LIMIT 1");
        if ($stmtReserva) {
            mysqli_stmt_bind_param($stmtReserva, 'siss', $action, $reservaId, $conductor['nombre'], $conductor['apellido']);
            mysqli_stmt_execute($stmtReserva);
            $updated = mysqli_stmt_affected_rows($stmtReserva) > 0;
            mysqli_stmt_close($stmtReserva);

            if ($updated) {
                if ($action === 'Confirmada') {
                    $stmtConductor = mysqli_prepare($conn, "UPDATE conductor SET estado = 'Ocupado' WHERE nombre = ? AND apellido = ? LIMIT 1");
                    if ($stmtConductor) {
                        mysqli_stmt_bind_param($stmtConductor, 'ss', $conductor['nombre'], $conductor['apellido']);
                        mysqli_stmt_execute($stmtConductor);
                        mysqli_stmt_close($stmtConductor);
                        $_SESSION['conductor']['estado'] = 'Ocupado';
                        $conductor['estado'] = 'Ocupado';
                    }
                }
                header('Location: dashboard-conductor.php?msg=' . urlencode($action));
                exit;
            }
        }
        $alertMessage = 'No se pudo actualizar la reserva. Intenta nuevamente.';
        $alertType = 'danger';
    }
}



// Obtener reservas asignadas
$nombre = mysqli_real_escape_string($conn, $conductor['nombre']);
$apellido = mysqli_real_escape_string($conn, $conductor['apellido']);
$sql = "SELECT * FROM reservas WHERE conductor_nombre = '$nombre' AND conductor_apellido = '$apellido' ORDER BY fecha_reserva DESC, hora_reserva DESC";
$reservas = mysqli_query($conn, $sql);

// Contar reservas por estado
$estadosPendientes = 0;
$estadosConfirmadas = 0;
$estadosCanceladas = 0;

if ($reservas) {
    $reservasArray = [];
    while ($row = mysqli_fetch_assoc($reservas)) {
        $reservasArray[] = $row;
        if ($row['status'] === 'Pendiente') $estadosPendientes++;
        elseif ($row['status'] === 'Confirmada') $estadosConfirmadas++;
        elseif ($row['status'] === 'Cancelada') $estadosCanceladas++;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel Conductor - QuillaMovil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="estilos.css">
    <style>
        body {
            background: #000 !important;
            color: #f8f9fa;
            min-height: 100vh;
        }

        .navbar {
            background: #000 !important;
            box-shadow: 0 2px 15px rgba(200, 1, 20, 0.2);
            border-bottom: 2px solid #c82333;
        }

        .navbar > .container-fluid {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.3rem;
            color: #c00114 !important;
            letter-spacing: 0.5px;
        }

        .navbar-brand img {
            max-height: 68px;
            width: auto;
        }

        .navbar-brand span {
            display: inline-block;
            white-space: nowrap;
        }

        @media (max-width: 768px) {
            .navbar-brand img {
                max-height: 60px;
            }

            .navbar-brand span {
                font-size: 1.15rem;
            }
        }

        @media (max-width: 576px) {
            .navbar-brand img {
                max-height: 56px;
            }

            .navbar-brand span {
                font-size: 1rem;
            }

            .offcanvas.offcanvas-start {
                width: 50% !important;
                max-width: 50% !important;
            }
        }

        .header-section {
            background: #1f1f23 !important;
            border-left: 4px solid #ff0019;
            padding: 2rem;
            margin-bottom: 2rem;
            border-radius: 8px;
            position: relative;
            z-index: 2;
        }

        .conductor-title {
            color: #ffffff;
            font-weight: 700;
            margin: 0;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .conductor-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: #ff0404;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.6rem;
            color: #ffffff;
            box-shadow: none;
            overflow: hidden;
        }

        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .status-activo {
            background: rgba(76, 175, 80, 0.2);
            color: #4caf50;
            border: 1px solid #4caf50;
        }

        .status-ocupado {
            background: rgba(200, 1, 20, 0.2);
            color: #c82333;
            border: 1px solid #c82333;
        }

        .card {
            background: #1f1f23 !important;
            border: 1px solid #2c2c33 !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            border-radius: 18px;
            transition: all 0.3s ease;
            color: #fff;
            padding: 20px;
        }

        .card:not(.stat-card):hover {
            border-color: #3a3a4d !important;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.42);
            transform: none !important;
        }

        .stat-card:hover {
            transform: none !important;
        }

        .reserve-card:hover {
            transform: none !important;
        }

        .card-header {
            background: transparent !important;
            border-bottom: 1px solid #2c2c33 !important;
            border-radius: 7px 7px 0 0 !important;
            color: #fff;
        }

        .stat-card {
            text-align: center;
            padding: 1.1rem 0.85rem;
            min-height: 100px;
            max-width: 240px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border-radius: 16px;
            border-bottom-width: 8px;
            border-bottom-style: solid;
            border-bottom-color: transparent;
        }

        .border-bottom-red {
            border-bottom-color: #ff011a !important;
        }

        .border-bottom-green {
            border-bottom-color: #04ff3e !important;
        }

        .border-bottom-yellow {
            border-bottom-color: #ffc107 !important;
        }

        .border-bottom-gray {
            border-bottom-color: #6c757d !important;
        }

        /* Permitir que las tarjetas de estadísticas ocupen el ancho de la columna
           cuando estén dentro de .stats-row (4 en una fila) */
        .stats-row {
            display: flex;
            flex-wrap: nowrap;
            gap: 1rem;
            justify-content: center;
            align-items: center;
            overflow-x: auto;
            padding-bottom: 0.25rem;
        }

        .stats-row .stat-item {
            flex: 0 0 auto;
            width: 240px;
            min-width: 240px;
        }

        .stats-row .stat-card {
            max-width: none;
            margin: 0;
        }

        .header-section .d-flex {
            flex-wrap: wrap;
            gap: 1rem;
        }

        .header-section .d-flex > div {
            min-width: 0;
        }

        .header-section .btn,
        .header-section .btn-outline-light {
            min-width: 150px;
        }

        .reserve-card {
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 18px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease, background-color 0.25s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        }

        .reserve-card:hover {
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.22);
            border-color: rgba(255, 77, 79, 0.4);
            background: rgba(255, 255, 255, 0.03);
        }

        .reserve-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .reserve-date {
            color: #bbb;
            font-size: 0.85rem;
            text-align: right;
            min-width: 140px;
        }

        .reserve-card .btn {
            width: auto;
            flex: 1 1 48%;
            min-width: 120px;
        }

        .reserve-card .btn + .btn {
            margin-left: 0;
        }

        @media (max-width: 992px) {
            .stats-row {
                justify-content: flex-start;
            }

            .stats-row .stat-item {
                width: calc(50% - 0.75rem);
                min-width: 180px;
            }
        }

        @media (max-width: 768px) {
            .header-section {
                padding: 1.25rem;
            }

            .header-section .d-flex {
                flex-direction: column;
                align-items: stretch;
            }

            .header-section .btn,
            .header-section .btn-outline-light {
                width: 100%;
            }

            .stats-row .stat-item {
                width: 100%;
                min-width: 0;
            }

            .reserve-header {
                justify-content: space-between;
            }

            .reserve-date {
                text-align: left;
                width: 100%;
                min-width: 0;
            }

            .reserve-card .btn {
                width: 100%;
                flex: 1 1 100%;
            }
        }

        @media (max-width: 576px) {
            .conductor-avatar {
                width: 90px;
                height: 90px;
            }

            .conductor-title {
                font-size: 1.4rem;
            }

            .reserve-card {
                padding: 1rem;
            }

            .reserve-title {
                font-size: 1rem;
            }

            .reserve-meta p,
            .reserve-date,
            .reserve-status {
                font-size: 0.95rem;
            }
        }

        .stat-number {
            font-size: 2.2rem;
            font-weight: 800;
            color: #fff;
            line-height: 1;
            margin: 0.3rem 0 0;
        }

        .stat-icon {
            font-size: 1.6rem;
            color: #fff;
            margin-bottom: 0.5rem;
        }

        .border-bottom-red .stat-number,
        .border-bottom-red .stat-icon {
            color: #ff0019 !important;
        }

        .border-bottom-green .stat-number,
        .border-bottom-green .stat-icon {
            color: #00fd3b !important;
        }

        .border-bottom-yellow .stat-number,
        .border-bottom-yellow .stat-icon {
            color: #ffc106 !important;
        }

        .border-bottom-gray .stat-number,
        .border-bottom-gray .stat-icon {
            color: #6c757d !important;
        }

        .stat-label {
            color: #fff;
            font-size: 1rem;
            margin: 0.25rem 0 0;
        }

        .btn-primary {
            background: linear-gradient(135deg, #c00114 0%, #c82333 100%);
            border: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #c82333 0%, #b71c1c 100%);
            box-shadow: 0 4px 12px rgba(200, 1, 20, 0.3);
            transform: translateY(-2px);
        }

        .btn-outline-light {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            border: 1px solid #ddd;
            color: #ddd;
            font-weight: 600;
            text-align: center;
        }

        .btn-outline-light:hover {
            background: rgba(192, 1, 20, 0.1);
            border-color: #c82333;
            color: #c82333;
        }

        .reserve-card {
            background: transparent;
            border: 3px solid transparent;
            border-image: linear-gradient(45deg, #3d3c3c 0%, #3d3c3c 45%, #ff0000 45%, #ff0000 55%, #3d3c3c 55%, #3d3c3c 100%) 1;
            border-radius: 18px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease, background-color 0.25s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        }

        .reserve-card:hover {
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.22);
            border-color: rgba(255, 77, 79, 0.4);
            background: rgba(255, 255, 255, 0.03);
        }

        .reserve-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }

        .reserve-title {
            color: #ff8c00;
            font-weight: 700;
            margin: 0;
            font-size: 1.1rem;
        }

        .reserve-date {
            color: #bbb;
            font-size: 0.85rem;
        }

        .reserve-route {
            color: #ddd;
            font-size: 0.95rem;
            margin: 0.75rem 0;
        }

        .reserve-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-pendiente {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
        }

        .status-confirmada {
            background: rgba(76, 175, 80, 0.2);
            color: #4caf50;
        }

        .status-cancelada {
            background: rgba(244, 67, 54, 0.2);
            color: #f44336;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #bbb;
        }

        .empty-state i {
            font-size: 3rem;
            color: rgba(192, 1, 20, 0.3);
            margin-bottom: 1rem;
        }

        .footer-section {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(192, 1, 20, 0.2);
            text-align: center;
            color: #888;
            font-size: 0.9rem;
        }

        /* Estilos para notificación flotante */
        .floating-notification {
            position: fixed;
            top: 0;
            left: 50%;
            transform: translateX(-50%) translateY(-150%);
            width: 90%;
            max-width: 600px;
            background: rgba(173, 216, 230, 0.95);
            border: 2px solid #0066cc;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 102, 204, 0.3);
            padding: 1.5rem;
            z-index: 9999;
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            margin-top: 1rem;
        }

        .floating-notification.show {
            transform: translateX(-50%) translateY(0);
        }

        .notification-content {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .notification-icon {
            font-size: 2.5rem;
            color: #0066cc;
            flex-shrink: 0;
            animation: pulse 2s infinite;
        }

        .notification-title {
            color: #0066cc;
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .notification-message {
            color: #0066cc;
            margin-bottom: 0;
            font-size: 0.95rem;
        }

        .btn-close-notification {
            background: none;
            border: none;
            color: #0066cc;
            font-size: 1.5rem;
            cursor: pointer;
            flex-shrink: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s ease;
        }

        .btn-close-notification:hover {
            transform: scale(1.2);
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }

        @media (max-width: 768px) {
            .floating-notification {
                width: 95%;
                padding: 1rem;
            }

            .notification-icon {
                font-size: 2rem;
            }

            .notification-title {
                font-size: 1rem;
            }

            .notification-message {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid px-4 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <button class="navbar-toggler me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNav" aria-controls="offcanvasNav" aria-label="Abrir menú">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <a class="navbar-brand d-flex align-items-center" href="dashboard-conductor.php">
                    <img src="logo.png" alt="QuillaMovil" style="height:100px; width:auto; margin-right:0.35rem;">
                    <span style="color:#fff; font-size:2.5rem; font-weight:700;">QuillaMovil</span>
                </a>
            </div>
            <div class="d-none d-lg-flex align-items-center gap-2">
                <span class="text-white fw-semibold"><?php echo htmlspecialchars($conductor['nombre']); ?></span>
                <a class="btn btn-outline-light btn-sm" href="editar-conductor.php?id=<?php echo urlencode($conductorId); ?>">Editar perfil</a>
                <a class="btn btn-danger btn-sm" href="logout-conductor.php">Cerrar sesión</a>
            </div>
        </div>
    </nav>

    <div class="offcanvas offcanvas-start text-bg-dark" tabindex="-1" id="offcanvasNav" aria-labelledby="offcanvasNavLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasNavLabel">Menú</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
        </div>
        <div class="offcanvas-body">
            <div class="mb-4">
                <p class="mb-1 text-white-50">Conductor</p>
                <h5 class="text-white mb-0"><?php echo htmlspecialchars($conductor['nombre']); ?></h5>
            </div>
            <div class="list-group">
                <a class="list-group-item list-group-item-action bg-secondary text-white mb-2" href="editar-conductor.php?id=<?php echo urlencode($conductorId); ?>">Editar perfil</a>
                <a class="list-group-item list-group-item-action bg-danger text-white" href="logout-conductor.php">Cerrar sesión</a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid px-4 py-4">
        <!-- Header Section -->
        <div class="header-section">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="conductor-avatar">
                        <?php
                        $fotoConductor = '';
                        $stmtFoto = mysqli_prepare($conn, "SELECT foto FROM conductor WHERE id = ? LIMIT 1");
                        if ($stmtFoto) {
                            mysqli_stmt_bind_param($stmtFoto, 'i', $conductorId);
                            mysqli_stmt_execute($stmtFoto);
                            $resFoto = mysqli_stmt_get_result($stmtFoto);
                            if ($resFoto && mysqli_num_rows($resFoto) > 0) {
                                $rowFoto = mysqli_fetch_assoc($resFoto);
                                $fotoConductor = $rowFoto['foto'];
                            }
                            mysqli_stmt_close($stmtFoto);
                        }

                        if (!empty($fotoConductor) && is_file(__DIR__ . '/' . $fotoConductor)): ?>
                            <img src="<?php echo htmlspecialchars($fotoConductor); ?>" alt="Foto conductor" style="width:100%;height:100%;object-fit:cover;display:block;">
                        <?php else: ?>
                            <i class="bi bi-person-fill"></i>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h1 class="conductor-title">
                            <?php echo htmlspecialchars($conductor['nombre'] . ' ' . $conductor['apellido']); ?>
                        </h1>
                        <div>
                            <span class="status-badge <?php echo $conductor['estado'] === 'Activo' ? 'status-activo' : 'status-ocupado'; ?>">
                                <i class="bi <?php echo $conductor['estado'] === 'Activo' ? 'bi-check-circle-fill' : 'bi-clock-fill'; ?>"></i>
                                <?php echo htmlspecialchars($conductor['estado']); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <form method="post">
                        <input type="hidden" name="toggle_estado" value="1">
                        <input type="hidden" name="nuevo_estado" value="<?php echo $conductor['estado'] === 'Activo' ? 'Ocupado' : 'Activo'; ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-arrow-repeat"></i> Cambiar Estado
                        </button>
                    </form>
                    
                </div>
            </div>
        </div>

        <!-- Stats Section -->
        <?php if (!empty($alertMessage)): ?>
            <div class="alert alert-<?php echo htmlspecialchars($alertType); ?> mb-4" role="alert">
                <?php echo htmlspecialchars($alertMessage); ?>
            </div>
        <?php endif; ?>

        <script>
            // Variables globales para tracking
            let previousPendingCount = <?php echo $estadosPendientes; ?>;
            let isFirstLoad = true;

            // Función para reproducir sonido de notificación
            function playNotificationSound() {
                try {
                    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                    const now = audioContext.currentTime;
                    
                    // Crear osciladores para generar tonos
                    const oscillator1 = audioContext.createOscillator();
                    const oscillator2 = audioContext.createOscillator();
                    const gainNode1 = audioContext.createGain();
                    const gainNode2 = audioContext.createGain();
                    
                    // Primer tono (más alto)
                    oscillator1.frequency.value = 800;
                    oscillator1.connect(gainNode1);
                    gainNode1.connect(audioContext.destination);
                    gainNode1.gain.setValueAtTime(0.3, now);
                    gainNode1.gain.exponentialRampToValueAtTime(0.01, now + 0.1);
                    oscillator1.start(now);
                    oscillator1.stop(now + 0.1);
                    
                    // Segundo tono (más bajo, después de una pausa)
                    oscillator2.frequency.value = 600;
                    oscillator2.connect(gainNode2);
                    gainNode2.connect(audioContext.destination);
                    gainNode2.gain.setValueAtTime(0, now + 0.15);
                    gainNode2.gain.setValueAtTime(0.3, now + 0.15);
                    gainNode2.gain.exponentialRampToValueAtTime(0.01, now + 0.25);
                    oscillator2.start(now + 0.15);
                    oscillator2.stop(now + 0.25);
                    
                } catch (err) {
                    console.log('Audio API no disponible:', err);
                }
            }

            // Función para actualizar notificación
            function updateNotificationAlert(count) {
                let alertElement = document.getElementById('pendingNotification');
                
                if (count > 0) {
                    if (!alertElement) {
                        // Crear el modal flotante si no existe
                        const alertHTML = `
                            <div class="floating-notification" id="pendingNotification">
                                <div class="notification-content">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="notification-icon">
                                            <i class="bi bi-bell-fill"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="notification-title">
                                                ¡Tienes <span id="pendingCount">${count}</span> reserva${count > 1 ? 's' : ''} pendiente${count > 1 ? 's' : ''}!
                                            </h5>
                                            <p class="notification-message">
                                                ${count === 1 ? 'Un cliente espera tu respuesta. Revisa los detalles y decide si aceptar o cancelar.' : `Hay ${count} clientes que esperan tu respuesta. ¡Revisa las solicitudes!`}
                                            </p>
                                        </div>
                                        <button type="button" class="btn-close-notification" onclick="this.closest('#pendingNotification').remove()">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                        document.body.insertAdjacentHTML('afterbegin', alertHTML);
                        
                        // Trigger animación
                        setTimeout(() => {
                            const elem = document.getElementById('pendingNotification');
                            if (elem) elem.classList.add('show');
                        }, 10);
                        
                        // Auto-desaparecer después de 8 segundos
                        setTimeout(() => {
                            const elem = document.getElementById('pendingNotification');
                            if (elem) {
                                elem.classList.remove('show');
                                setTimeout(() => elem.remove(), 300);
                            }
                        }, 8000);
                    } else {
                        // Actualizar el contador
                        const pendingCount = document.getElementById('pendingCount');
                        if (pendingCount) {
                            pendingCount.textContent = count;
                        }
                    }
                }
            }

            // Función para verificar nuevas reservas
            function checkForNewReservas() {
                fetch('check-new-reservas.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const currentCount = data.pendientes;
                            
                            // Si no es la primera carga y hay más reservas, reproducir sonido
                            if (!isFirstLoad && currentCount > previousPendingCount) {
                                playNotificationSound();
                                // Mostrar notificación flotante
                                updateNotificationAlert(currentCount);
                                
                                // Opcional: Mostrar notificación del navegador
                                if ('Notification' in window && Notification.permission === 'granted') {
                                    new Notification('Nueva Reserva', {
                                        body: `Tienes ${currentCount} reserva${currentCount > 1 ? 's' : ''} pendiente${currentCount > 1 ? 's' : ''}`,
                                        icon: 'logo.png'
                                    });
                                }
                            }
                            
                            // Actualizar el contador
                            previousPendingCount = currentCount;
                        }
                    })
                    .catch(err => console.log('Error al verificar reservas:', err));
            }

            document.addEventListener('DOMContentLoaded', function() {
                // Reproducir sonido solo la primera carga si hay reservas pendientes
                if (previousPendingCount > 0) {
                    playNotificationSound();
                }
                isFirstLoad = false;
                
                // Solicitar permiso para notificaciones del navegador
                if ('Notification' in window && Notification.permission === 'default') {
                    Notification.requestPermission();
                }
                
                // Verificar nuevas reservas cada 10 segundos
                setInterval(checkForNewReservas, 10000);
            });
        </script>
        <div class="mb-4 stats-row">
            <div class="stat-item">
                <div class="card stat-card border-bottom-yellow">
                    <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
                    <h1 class="stat-number"><?php echo $estadosPendientes; ?></h1>
                    <div class="stat-label">Pendientes</div>
                </div>
            </div>
            <div class="stat-item">
                <div class="card stat-card border-bottom-green">
                    <div class="stat-icon"><i class="bi bi-check-lg"></i></div>
                    <h1 class="stat-number"><?php echo $estadosConfirmadas; ?></h1>
                    <div class="stat-label">Confirmadas</div>
                </div>
            </div>
            <div class="stat-item">
                <div class="card stat-card border-bottom-red">
                    <div class="stat-icon"><i class="bi bi-x-lg"></i></div>
                    <h1 class="stat-number"><?php echo $estadosCanceladas; ?></h1>
                    <div class="stat-label">Canceladas</div>
                </div>
            </div>
            <div class="stat-item">
                <div class="card stat-card border-bottom-gray">
                    <div class="stat-icon"><i class="bi bi-calendar"></i></div>
                    <h1 class="stat-number"><?php echo isset($reservasArray) ? count($reservasArray) : 0; ?></h1>
                    <div class="stat-label">Total</div>
                </div>
            </div>
        </div>

        <!-- Reservas Section -->
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">
                    <i class="bi bi-calendar-check"></i> Mis Reservas Asignadas
                </h5>
            </div>
            <div class="card-body">
                <?php if (isset($reservasArray) && count($reservasArray) > 0): ?>
                    <div class="row">
                        <?php foreach ($reservasArray as $r): ?>
                            <div class="col-lg-6 col-md-12">
                                <div class="reserve-card">
                                    <div class="reserve-header">
                                        <div>
                                            <h6 class="reserve-title">
                                                <i class="bi bi-person-check"></i> Cliente: <?php echo htmlspecialchars($r['nombre_completo']); ?>
                                            </h6>
                                            <p class="mb-1 text-white-50">
                                                <strong>Origen:</strong> <?php echo htmlspecialchars($r['origen']); ?>
                                                <span class="mx-2">•</span>
                                                <strong>Destino:</strong> <?php echo htmlspecialchars($r['destino']); ?>
                                            </p>
                                        </div>
                                        <span class="reserve-date">
                                            <i class="bi bi-calendar-event"></i> <?php echo date('d/m/Y', strtotime($r['fecha_reserva'])); ?>
                                            <br>
                                            <i class="bi bi-clock"></i> <?php echo str_replace(['am', 'pm'], ['a.m.', 'p.m.'], strtolower(date('g:i a', strtotime($r['hora_reserva'])))); ?>
                                        </span>
                                    </div>
                                    <div class="reserve-meta mb-2">
                                        <p class="mb-1"><strong>Solicitud:</strong> <?php echo htmlspecialchars($r['status'] ?? 'Desconocido'); ?></p>
                                        
                                        <p class="mb-0"><strong>Teléfono conductor:</strong> <?php echo htmlspecialchars($r['conductor_telefono'] ?: 'No disponible'); ?></p>
                                    </div>
                                    <?php if ($r['status'] === 'Pendiente'): ?>
                                        <div class="mt-3 d-flex gap-2 flex-wrap">
                                            <form method="post" class="mb-2">
                                                <input type="hidden" name="reserva_id" value="<?php echo (int)$r['id']; ?>">
                                                <input type="hidden" name="reserva_action" value="aceptar">
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="bi bi-check-lg"></i> Aceptar
                                                </button>
                                            </form>
                                            <form method="post" class="mb-2">
                                                <input type="hidden" name="reserva_id" value="<?php echo (int)$r['id']; ?>">
                                                <input type="hidden" name="reserva_action" value="cancelar">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="bi bi-x-lg"></i> Cancelar
                                                </button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <h5>No tienes reservas asignadas</h5>
                        <p>Cuando se te asigne una reserva, aparecerá aquí.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer Section -->
        <div class="footer-section">
            <p><i class="bi bi-info-circle"></i> Sistema de Gestión QuillaMovil © 2024</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
